{% if not helpers.empty('YETIsense.IDS.general.enabled') %}
suricata_setup="/usr/local/yetisense/scripts/suricata/setup.sh"
suricata_enable="YES"
{% if not helpers.empty('YETIsense.IDS.general.verbosity') %}
suricata_flags="-D -{{YETIsense.IDS.general.verbosity}}"
{% endif %}
{% if YETIsense.IDS.general.ips|default("0") == "1" %}
# IPS mode, switch to netmap
suricata_netmap="YES"
{% else %}
# IDS mode, pcap live mode
{% set addFlags=[] %}
{%   for intfName in YETIsense.IDS.general.interfaces.split(',') %}
{#     store additional interfaces to addFlags #}
{%     do addFlags.append(helpers.physical_interface(intfName)) %}
{%   endfor %}
suricata_interface="{{ addFlags|join(' ') }}"
{% endif %}
{% else %}
suricata_enable="NO"
{% endif %}
