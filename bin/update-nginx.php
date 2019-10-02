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
use Phore\HttpClient\Ex\PhoreHttpRequestException;
use Psr\Log\LogLevel;

require __DIR__ . "/../vendor/autoload.php";


$targetConfig = [

    "principal_hostname" => CONF_PRINCIPAL_SERVICE,
    "principal_service_ip" => filter_var(gethostbyname(CONF_PRINCIPAL_SERVICE), FILTER_VALIDATE_IP),
    "conf_nginx_error_log" => CONF_NGINX_ERROR_LOG,
    "conf_nginx_access_log" => CONF_NGINX_ACCESS_LOG,
    "vhosts" => []
];


function convertUrlToHostAddr(string $input) : ?string
{
    $chost = parse_url($input, PHP_URL_HOST);
    $cpath = parse_url($input, PHP_URL_PATH);
    if ($cpath == false)
        $cpath = "";
    $cport = parse_url($input, PHP_URL_PORT);
    if ($cport == false)
        $cport = "80";

    if ($chost === false)
        return null;
    $addr = gethostbyname($chost);
    if ( ! filter_var($addr, FILTER_VALIDATE_IP))
        return null;
    return "http://{$addr}:{$cport}{$cpath}";
}


$cloudConfig = phore_http_request(CONF_PRINCIPAL_GET_CONFIG_URL)->send()->getBodyJson();
$vhosts = phore_pluck("vhosts", $cloudConfig);


$secretBox = new PhoreSecretBoxSync(phore_file(CONF_CF_SECRET)->get_contents());
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

        $proxy_pass_ip = convertUrlToHostAddr($proxy_pass);

        $vhostConfig["locations"][] = [
            "location" => $location,
            "proxy_pass" => $proxy_pass_ip
        ];
    }

    $ssl_pem_file = phore_pluck("ssl_cert_id", $vhost, null);

    $vhostConfig["ssl_pem_local_file"] = "";

    if ($ssl_pem_file !== null) {
        $ssl_pem_serial = phore_pluck("ssl_cert_serial", $vhost);
        $storeFilename = $certStore->withFileName($ssl_pem_serial . $ssl_pem_file, "pem");
        if (! $storeFilename->exists()) {
            phore_out("Downloading new cert for $ssl_pem_file (Serial: $ssl_pem_serial)...");
            $ret = phore_http_request(CONF_PRINCIPAL_GET_CERT_URL, ["certId" => $ssl_pem_file])->send()->getBody();
            $storeFilename->set_contents($secretBox->decrypt($ret));
        }
        $vhostConfig["ssl_pem_local_file"] = $storeFilename->getUri();
    }
    $targetConfig["vhosts"][] = $vhostConfig;

}

phore_file(CONF_CLOUDFRONT_RUN_CONFIG)->set_contents(phore_json_pretty_print(phore_json_encode($targetConfig)));


phore_log()->setLogLevel(LogLevel::ERROR);
$ct = new PhoreCloudTool(__DIR__ . "/../etc/nginx", "/etc/nginx", phore_log());

$ct->setEnvironment($targetConfig);

$ct->parseRecursive();

if ($ct->isFileModified()) {
    phore_log()->notice("nginx config changed - reloading server");
    try {
        phore_exec("service nginx reload");
    } catch (\Exception $e) {
        phore_log()->error("reload failed: " . $e->getMessage());
        passthru("nginx -t");
    }
}

try {
    phore_http_request("http://localhost/rudl-cf-selftest")->send(false);
} catch (\Exception $ex) {
    phore_log()->notice("Nginx not running - restarting");
    try {
        phore_exec("service nginx restart");
    } catch (\Exception $e) {
        phore_log()->emergency("Cant restart nginx: " . $e->getMessage());
        passthru("nginx -t");
        sleep(10);
    }

}

