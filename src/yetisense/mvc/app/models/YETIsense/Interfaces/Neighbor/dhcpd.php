<?php

/*
 * Copyright (C) 2023 Deciso B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace YETIsense\Interfaces\Neighbor;

use YETIsense\Core\Config;

class dhcpd
{
    public function collect()
    {
        $result = [];
        $intfmap = [];
        $config = Config::getInstance()->object();
        if ($config->dhcpd->count() > 0) {
            foreach ($config->dhcpd->children() as $intf => $node) {
                foreach ($node->children() as $key => $data) {
                    if ($key == 'staticmap') {
                        if (!empty($data->arp_table_static_entry) || !empty($node->staticarp)) {
                            $result[] = [
                                'etheraddr' => (string)$data->mac,
                                'ipaddress' => (string)$data->ipaddr,
                                'descr' => (string)$data->descr,
                                'source' => sprintf('dhcpd-%s', $intf)
                            ];
                        }
                    }
                }
            }
        }
        return $result;
    }
}
