#!/bin/bash


while true
do
    php -f /opt/bin/update-nginx.php > /dev/null || sleep 10
    sleep 1
done;