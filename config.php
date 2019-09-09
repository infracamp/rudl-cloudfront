<?php
/**
 * This file is copied to config.php by kick
 *
 * Placeholders (\%CONF_ENVNAME\%) are replaced by the values found in environment
 */

define ("DEV_MODE", (bool)"1");


if (DEV_MODE === true) {
    define ("CONF_PRINCIPAL_HOSTNAME", "localhost:4000");
} else {

    define ("CONF_PRINCIPAL_HOSTNAME", "rudl-principal");
}

define ("CONF_SSL_CERT_STORE", "/mnt/ssl");
define ("CONF_CLOUDFRONT_RUN_CONFIG", "/mnt/cloudfront-run.json");


define("CONF_NGINX_ERROR_LOG", "syslog:server=35.246.251.215:5000,facility=local7,tag=rudlcloundfront,severity=info");
define("CONF_NGINX_ACCESS_LOG", "syslog:server=35.246.251.215:5000,facility=local7,tag=rudlcloundfront,severity=info");

define ("CONF_PRINCIPAL_GET_CONFIG_URL", "http://" . CONF_PRINCIPAL_HOSTNAME . "/v1/cloudfront/config");
define ("CONF_PRINCIPAL_GET_CERT_URL", "http://" . CONF_PRINCIPAL_HOSTNAME . "/v1/cloudfront/cert/{certId}");

define("CONF_CF_SECRET", "/run/secrets/rudl_cf_secret");

