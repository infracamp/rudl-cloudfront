global
    log 127.0.0.1    local0
    log 127.0.0.1    local1 notice
    chroot /var/lib/haproxy
    stats socket /run/haproxy/admin.sock mode 660 level admin
    stats timeout 30s
    user haproxy
    group haproxy
    daemon

    # Default SSL material locations
    ca-base /etc/ssl/certs
    crt-base /etc/ssl/private
    tune.ssl.default-dh-param 2048

    # Default ciphers to use on SSL-enabled listening sockets.
    # For more information, see ciphers(1SSL). This list is from:
    #  https://hynek.me/articles/hardening-your-web-servers-ssl-ciphers/
    ssl-default-bind-ciphers ECDH+AESGCM:DH+AESGCM:ECDH+AES256:DH+AES256:ECDH+AES128:DH+AES:ECDH+3DES:DH+3DES:RSA+AESGCM:RSA+AES:RSA+3DES:!aNULL:!MD5:!DSS
    ssl-default-bind-options no-sslv3


defaults
    log     global
    mode    http
    option  httplog
    option  dontlognull
    timeout http-request 5s
    timeout connect 5000
    timeout client  50000
    timeout server  300s
    errorfile 400 /etc/haproxy/errors/400.http
    errorfile 403 /etc/haproxy/errors/403.http
    errorfile 408 /etc/haproxy/errors/408.http
    errorfile 500 /etc/haproxy/errors/500.http
    errorfile 502 /etc/haproxy/errors/502.http
    errorfile 503 /etc/haproxy/errors/503.html
    errorfile 504 /etc/haproxy/errors/504.http

listen stats
   bind *:88
   stats enable
   stats refresh 5s
   stats show-node
   stats auth contiadmin:killacat
   stats uri  /

############################
## Frontends (HTTP/HTTPS)
############################



{section > aclhost}

{/section}


frontend http-in
    bind *:80
    timeout http-request 5s

    ## Letsencrypt finden
    acl has_letsencrypt_acme_signature path_beg /.well-known/acme-challenge/
    use_backend int_letsencrypt if has_letsencrypt_acme_signature

    http-request set-header X-SSL no
    http-request set-header X-Orig-Src %[src]

    ## Domain acl's
    ## Achtung: spezifischere Domains müssen weiter oben stehen. (First match wins)

{= aclhost}

    default_backend {{DEFAULT_SERVICE}}

frontend https-in
    bind *:443 ssl crt /etc/haproxy/ssl/default.pem crt /etc/haproxy/ssl/ no-sslv3
    timeout http-request 5s

    acl has_letsencrypt_acme_signature path_beg /.well-known/acme-challenge/
    use_backend int_letsencrypt if has_letsencrypt_acme_signature

    option tcplog
    mode http

    tcp-request inspect-delay 5s
    tcp-request content accept if { req_ssl_hello_type 1 }

    http-request set-header X-SSL yes
    http-request set-header X-Orig-Src %[src]

    ## List virtual Domains here:
    ## Not used (only one backend). #acl vn_is_example_com req_ssl_sni -i example.com

{=aclhost}

    default_backend {{DEFAULT_SERVICE}}

#############################
## Default Backends
#############################


backend Abuse
    stick-table type ip size 1m expire 60m store gpc0


backend int_error
    mode http
    balance roundrobin
    option forwardfor
    server int_site 127.0.0.1:8000 check

backend int_letsencrypt
    mode http
    balance roundrobin
    option forwardfor
    server int_site {resolve name="rudl-manager"}:80 check

#####################################
## Hier folgend die services
#####################################

{{BACKENDS}}