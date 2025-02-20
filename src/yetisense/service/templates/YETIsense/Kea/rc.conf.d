{% if not helpers.empty('YETIsense.Kea.dhcp4.general.interfaces') and not helpers.empty('YETIsense.Kea.dhcp4.general.enabled') %}
kea_enable="YES"
kea_setup="/usr/local/sbin/pluginctl -c kea_sync"
{% else %}
kea_enable="NO"
{% endif %}
