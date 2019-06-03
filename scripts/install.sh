#!/bin/bash


set -x -e

apt-get update
apt-get install -y nginx

useradd nginx
rm -R /etc/nginx/*