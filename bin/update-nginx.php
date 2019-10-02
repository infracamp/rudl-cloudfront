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

phore_log()->setLogLevel(LogLevel::WARNING);
function warnMsgDelayed($message) {
    if (time() % 60 !== 1)
        return;
    phore_log()->warning("Delayed message: " . $message);
}

$targetConfig = [

    "principal_hostname" => CONF_PRINCIPAL_SERVICE,
    "principal_service_ip" => filter_var(gethostbyname(CONF_PRINCIPAL_SERVICE), FILTER_VALIDATE_IP),
    "conf_nginx_error_log" => CONF_NGINX_ERROR_LOG,
    "conf_nginx_access_log" => CONF_NGINX_ACCESS_LOG,
    "vhosts" => []
];

if ($targetConfig["principal_service_ip"] === false) {
    warnMsgDelayed("Cannot resolve CONF_PRINCIPAL_SERVICE: '" . CONF_PRINCIPAL_SERVICE . "' - Skipping SSL registration.");
}


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

$cloudFrontSecret = phore_file(CONF_CF_SECRET)->get_contents();
if (strlen($cloudFrontSecret) < 32)
    throw new \Exception("Cloudfront secret must be at least 32 characters.");
$principalToken = substr($cloudFrontSecret,0, 8);

$cloudConfig = phore_http_request(CONF_PRINCIPAL_GET_CONFIG_URL . "?ptoken={$principalToken}")->send()->getBodyJson();
$vhosts = phore_pluck("vhosts", $cloudConfig);





$secretBox = new PhoreSecretBoxSync($cloudFrontSecret);
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
            warnMsgDelayed("Skipping duplicate location: $location");
            continue;
        }

        $proxy_pass = phore_pluck("proxy_pass", $curLocation);

        $proxy_pass_ip = convertUrlToHostAddr($proxy_pass);

        if ($proxy_pass_ip === null) {
            warnMsgDelayed("Cannot resolve upstream proxy_pass '$proxy_pass'. Skipping vhost.");
            continue;
        }

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
            phore_log()->warning("Downloading new cert for $ssl_pem_file (Serial: $ssl_pem_serial)...");
            $ret = phore_http_request(CONF_PRINCIPAL_GET_CERT_URL . "?ptoken={$principalToken}", ["certId" => $ssl_pem_file])->send()->getBody();
            $storeFilename->set_contents($secretBox->decrypt($ret));
        }
        $vhostConfig["ssl_pem_local_file"] = $storeFilename->getUri();
    }
    $targetConfig["vhosts"][] = $vhostConfig;

}

phore_file(CONF_CLOUDFRONT_RUN_CONFIG)->set_contents(phore_json_pretty_print(phore_json_encode($targetConfig)));



$ct = new PhoreCloudTool(__DIR__ . "/../etc/nginx", "/etc/nginx", phore_log());

$ct->setEnvironment($targetConfig);

$ct->parseRecursive();

if ($ct->isFileModified()) {
    phore_log()->warning("nginx config changed - reloading server");
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
    phore_log()->warning("Nginx not running - restarting");
    try {
        phore_exec("service nginx restart");
    } catch (\Exception $e) {
        phore_log()->emergency("Cant restart nginx: " . $e->getMessage());
        passthru("nginx -t");
        sleep(10);
    }

}

