FROM infracamp/kickstart-flavor-gaia:testing

ENV DEV_CONTAINER_NAME="rudl-cloudfront"

ENV CONF_PRINCIPAL_SERVICE="rudl-principal"
ENV CONF_NGINX_ERROR_LOG="/var/log/nginx/error.log"
ENV CONF_NGINX_ACCESS_LOG="/var/log/nginx/access.log main"

ENV CONF_CLUSTER_NAME="unnamed"
ENV CONF_METRICS_HOST=""


ADD / /opt
RUN ["bash", "-c",  "chown -R user /opt"]
RUN ["/kickstart/flavorkit/scripts/start.sh", "build"]

ENTRYPOINT ["/kickstart/flavorkit/scripts/start.sh", "standalone"]
