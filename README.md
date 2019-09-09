# Cloudfront Nginx


## Environment variables


| name | default |
|------|---------|
| `CONF_NGINX_ERROR_LOG` | |
| `CONF_NGINX_ACCESS_LOG` | |


## Logging to Syslog

Set environment variables to

```
syslog:server=[2001:db8::1]:1234,facility=local7,tag=rudl-cloundfront,severity=info
```