#
# Automatic generated configuration for netflow.
# Do not edit this file manually.
#
{%
  if helpers.exists('YETIsense.Netflow.capture.interfaces')
  and
  YETIsense.Netflow.capture.interfaces.strip()
  and
  YETIsense.Netflow.capture.targets.strip()%}
netflow_enable="YES"
{% else %}
netflow_enable="NO"
{% endif %}
