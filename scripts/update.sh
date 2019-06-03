#!/bin/bash

set -e -x

mkdir -p /mnt/ssl
chmod 700 /mnt/ssl

php -f /opt/bin/update-nginx.php

