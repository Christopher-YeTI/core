{% set cp_zones = [] %}
{% if helpers.exists('YETIsense.captiveportal.zones.zone') %}
{%      for cp_item in helpers.toList('YETIsense.captiveportal.zones.zone')  %}
{%          if cp_item.enabled|default("0") == '1' %}
{%              do cp_zones.append(cp_key) %}
{%          endif %}
{%      endfor %}
{% endif %}
{# collect enabled #}
{% set shapers = [] %}
{% if helpers.exists('YETIsense.TrafficShaper') %}
{%     if helpers.exists('YETIsense.TrafficShaper.pipes.pipe') %}
{%         for pipe in helpers.toList('YETIsense.TrafficShaper.pipes.pipe') %}
{%             if pipe.enabled|default('0') == '1' %}
{%                 do shapers.append(cp_key) %}
{%             endif%}
{%         endfor%}
{%     endif %}
{% endif %}
dummynet_enable="YES"
firewall_enable="{% if shapers or cp_zones %}YES{% else %}NO{% endif %}"
firewall_script="/usr/local/etc/rc.ipfw"
ipfw_defer="YES"
