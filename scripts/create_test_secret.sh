#!/bin/bash

if [  ! -e /run/secrets/rudl_cf_secret ]
then

    mkdir -p /run/secrets
    echo 'testsecret012345678901234567890123456789' > /run/secrets/rudl_cf_secret
fi;