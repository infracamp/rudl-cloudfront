<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 26.07.18
 * Time: 15:43
 */

namespace App;

use Phore\Core\Helper\PhoreSecretBoxSync;
use Phore\MicroApp\App;
use Phore\MicroApp\Handler\JsonExceptionHandler;
use Phore\MicroApp\Handler\JsonResponseHandler;

require __DIR__ . "/../vendor/autoload.php";


$app = new App();
$app->setResponseHandler(new JsonResponseHandler());
$app->setOnExceptionHandler(new JsonExceptionHandler());
$app->acl->addRule(aclRule("*")->ALLOW());


$app->router->onGet("/v1/cloudfront/config", function () {
    return phore_file(__DIR__ . "/cloudfront.json")->get_json();
});

$app->router->onGet("/v1/cloudfront/cert/:cert", function (string $cert) {
    $enc = new PhoreSecretBoxSync(phore_file(CONF_CF_SECRET)->get_contents());
    echo $enc->encrypt(phore_file(__DIR__ . "/democert.pem")->get_contents());
    return true;
});


$app->serve();