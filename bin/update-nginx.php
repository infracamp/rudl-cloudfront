<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 03.06.19
 * Time: 14:02
 */

namespace App;

use Phore\CloudTool\PhoreCloudTool;
use Phore\Core\Helper\PhoreSecretBoxSync;

require __DIR__ . "/../vendor/autoload.php";


$targetConfig = [
    "manager_hostname" => CONF_MANAGER_HOSTNAME,
    "vhosts" => []
];

$cloudConfig = phore_http_request(CONF_MANAGER_GET_CONFIG_URL)->send()->getBodyJson();
print_r ($cloudConfig);
$vhosts = phore_pluck("vhosts", $cloudConfig);
print_r ($vhosts);

$secretBox = new PhoreSecretBoxSync(phore_file(CONF_MANAGER_CERT_SECRET)->get_contents());
$certStore = phore_dir(CONF_SSL_CERT_STORE)->assertDirectory(true);

foreach ($vhosts as $index => $vhost) {

    $vhostConfig = [
        "domains" => phore_pluck("domains", $vhost),
        "locations" => []
    ];

    $usedLocations = [];

    foreach (phore_pluck("locations", $vhost) as $curLocation) {
        $location = phore_pluck("location", $curLocation);

        if (in_array($location, $usedLocations)) {
            phore_out("Skipping duplicate location: $location");
            continue;
        }

        $proxy_pass = phore_pluck("proxy_pass", $curLocation);
        try {
            phore_http_request($proxy_pass)->send(false);
        } catch (\Exception $e) {
            continue;
        }
        $vhostConfig["locations"][] = [
            "location" => $location,
            "proxy_pass" => $proxy_pass
        ];
    }

    $ssl_pem_file = phore_pluck("ssl_pem_file", $vhost, null);

    if ($ssl_pem_file !== null) {
        $ssl_pem_serial = phore_pluck("ssl_pem_serial", $vhost);
        $storeFilename = $certStore->withFileName($ssl_pem_serial . $ssl_pem_file);
        if (! $storeFilename->exists()) {
            phore_out("Downloading new cert for $ssl_pem_file (Serial: $ssl_pem_serial)...");
            $ret = phore_http_request(CONF_MANAGER_GET_CERT_URL, ["certId" => $ssl_pem_file])->send()->getBody();
            $storeFilename->set_contents($secretBox->decrypt($ret));
        }
        $vhostConfig["ssl_pem_local_file"] = $storeFilename->getUri();
    }
    $targetConfig["vhosts"][] = $vhostConfig;

}

phore_file(CONF_CLOUDFRONT_RUN_CONFIG)->set_contents(phore_json_pretty_print(phore_json_encode($targetConfig)));

$ct = new PhoreCloudTool(__DIR__ . "/../etc/nginx", "/etc/nginx");
$ct->setEnvironment($targetConfig);

$ct->parseRecursive();

if ($ct->isFileModified()) {
    phore_out("nginx config changed - reloading server");
    phore_exec("service nginx reload");
}

try {
    phore_http_request("http://localhost")->send(false);
} catch (\Exception $ex) {
    phore_out("Nginx not running - restarting");
    phore_exec("service nginx restart");
}

