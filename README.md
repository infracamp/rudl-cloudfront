# Cloudfront Nginx


## Environment variables


| name | default |
|------|---------|
| `CONF_NGINX_ERROR_LOG` | |
| `CONF_NGINX_ACCESS_LOG` | |
| `CONF_CLUSTER_NAME` | |


## Logging to Syslog

Set environment variables to

```
syslog:server=[2001:db8::1]:1234,facility=local7,tag=rudlcf,severity=info json_combined
```



## Developing

Create a secret:

```bash
mkdir -p $HOME/.kickstart/secrets/rudl-cloudfront/ && echo "dev-secret" > $HOME/.kickstart/secrets/rudl-cloudfront/rudl_cf_secret
```
