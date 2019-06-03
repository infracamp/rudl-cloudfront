<?php
/**
 * This file is copied to config.php by kick
 *
 * Placeholders (\%CONF_ENVNAME\%) are replaced by the values found in environment
 */

define ("DEV_MODE", (bool)"1");


if (DEV_MODE === true) {
    define ("CONF_MANAGER_HOSTNAME", "localhost:4000");
} else {
    define ("CONF_MANAGER_HOSTNAME", "rudl-manager");
}

define ("CONF_SSL_CERT_STORE", "/mnt/ssl");
define ("CONF_CLOUDFRONT_RUN_CONFIG", "/mnt/cloudfront-run.json");


define ("CONF_MANAGER_GET_CONFIG_URL", "http://" . CONF_MANAGER_HOSTNAME . "/v1/cloudfront/config");
define ("CONF_MANAGER_GET_CERT_URL", "http://" . CONF_MANAGER_HOSTNAME . "/v1/cloudfront/cert/{certId}");

define("CONF_MANAGER_CERT_SECRET", "/run/secrets/_int_rudl_cf_secret");

