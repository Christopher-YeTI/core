"""
    Copyright (c) 2016-2023 Ad Schellevis <ad@yetisense.org>
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice,
     this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimer in the
     documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
"""
import datetime
import tempfile
import os
import ipaddress
import time
import ujson
import requests
import zipfile
import syslog
from configparser import ConfigParser
from .base import BaseContentParser


class GEOIP(BaseContentParser):
    _updater_conf = '/usr/local/etc/filter_geoip.conf'
    _stats_output = '/usr/local/share/GeoIP/alias.stats'
    _target_dir = '/usr/local/share/GeoIP/alias'

    @classmethod
    def _update(cls):
        url = None
        if os.path.exists(cls._updater_conf):
            cnf = ConfigParser()
            cnf.read(cls._updater_conf)
            if cnf.has_section('settings') and cnf.has_option('settings', 'url'):
                url = cnf.get('settings', 'url').strip()

        result = {
            'address_count': 0 ,
            'file_count': 0,
            'timestamp': None,
            'locations_filename': None,
            'address_sources': {'IPv4': None, 'IPv6': None}
        }
        if url is not None and url.lower().startswith('http'):
            # flush data from remote url to temp file and unpack from there
            with tempfile.NamedTemporaryFile() as tmp_stream:
                try:
                    r = requests.get(url)
                except Exception as e:
                    syslog.syslog(syslog.LOG_ERR, 'geoip update failed : %s' % e)
                    return result
                if r.status_code == 200:
                    tmp_stream.write(r.content)
                    tmp_stream.seek(0)
                    try:
                        with zipfile.ZipFile(tmp_stream, mode='r', compression=zipfile.ZIP_DEFLATED) as zf:
                            # fetch zip file contents
                            file_handles = dict()
                            for item in zf.infolist():
                                if item.file_size > 0:
                                    filename = os.path.basename(item.filename)
                                    file_handles[filename] = item
                                    if filename.lower().find('locations-en.csv') > -1:
                                        result['locations_filename'] = filename
                                    elif filename.lower().find('ipv4.csv') > -1:
                                        result['address_sources']['IPv4'] = filename
                                    elif filename.lower().find('ipv6.csv') > -1:
                                        result['address_sources']['IPv6'] = filename
                            # only process geo ip data when archive contains country definitions
                            if result['locations_filename'] is not None:
                                dt = datetime.datetime(*file_handles[result['locations_filename']].date_time).isoformat()
                                result['timestamp'] = dt
                                country_codes = dict()
                                # parse geoname_id to country code map
                                locations = zf.open(file_handles[result['locations_filename']]).read()
                                for line in locations.decode().split('\n'):
                                    parts = line.split(',')
                                    if len(parts) > 4 and parts[0].isdigit():
                                        if len(parts[4]) >= 1:
                                            country_codes[parts[0]] = parts[4]
                                        elif parts[2] == 'EU':
                                            country_codes[parts[0]] = parts[2]
                                # process all details into files per country / protocol
                                for proto in ['IPv4', 'IPv6']:
                                    if result['address_sources'][proto] is not None:
                                        output_handles = dict()
                                        country_blocks = zf.open(file_handles[result['address_sources'][proto]]).read()
                                        for line in country_blocks.decode().split('\n'):
                                            parts = line.split(',')
                                            if len(parts) > 1 and parts[1] in country_codes:
                                                country_code = country_codes[parts[1]]
                                                if country_code not in output_handles:
                                                    if not os.path.exists(cls._target_dir):
                                                        os.makedirs(cls._target_dir)
                                                    output_handles[country_code] = open(
                                                        '%s/%s-%s'%(cls._target_dir, country_code,proto), 'w'
                                                    )
                                                    result['file_count'] += 1
                                                output_handles[country_code].write("%s\n" % parts[0])
                                                result['address_count'] += 1
                                        for country_code in output_handles:
                                            output_handles[country_code].close()
                    except zipfile.BadZipFile as e:
                        syslog.syslog(syslog.LOG_ERR, 'geoip update failed : %s' % e)
                        return result

                    open(cls._stats_output,'w').write(ujson.dumps(result))

        return result

    def __init__(self, proto='IPv4', **kwargs):
        super().__init__(**kwargs)
        self._proto = proto.split(',')

    def download(self):
        return self._update()

    def iter_addresses(self, country):
        do_update = True
        if os.path.isfile('%s/NL-IPv4' % self._target_dir):
            fstat = os.stat('%s/NL-IPv4' % self._target_dir)
            if (time.time() - fstat.st_mtime) < (86400 - 90) and fstat.st_size > 1024:
                do_update = False
        if do_update:
            syslog.syslog(
                syslog.LOG_NOTICE,
                'geoip updated (files: %(file_count)d lines: %(address_count)d)' % self._update()
            )

        for proto in self._proto:
            geoip_filename = "%s/%s-%s" % ( self._target_dir, country, proto)
            if os.path.isfile(geoip_filename):
                with open(geoip_filename) as f_in:
                    for address in f_in:
                        try:
                            ipaddress.ip_network(address.strip(), strict=False)
                            yield address.strip()
                        except (ipaddress.AddressValueError, ValueError):
                            pass
