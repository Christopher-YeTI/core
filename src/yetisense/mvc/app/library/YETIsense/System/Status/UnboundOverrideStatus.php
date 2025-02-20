<?php

/*
 * Copyright (C) 2025 Deciso B.V.
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

namespace YETIsense\System\Status;

use YETIsense\System\AbstractStatus;
use YETIsense\System\SystemStatusCode;

class UnboundOverrideStatus extends AbstractStatus
{
    public function __construct()
    {
        $this->internalPriority = 2;
        $this->internalPersistent = true;
        $this->internalTitle = gettext('Configuration override');
        $this->internalIsBanner = true;
        $this->internalScope[] = '/ui/unbound/*';
    }

    public function collectStatus()
    {
        /* XXX: custom overwrites don't live in their own path, which makes detection less practical */
        $managed_files = [];
        $tmp = @file_get_contents('/usr/local/yetisense/service/templates/YETIsense/Unbound/core/+TARGETS') ?? '';
        foreach (explode("\n", $tmp) as $line) {
            if (strpos($line, 'unbound.yetisense.d') > 1) {
                $managed_files[] = trim(explode(":", $line)[1]);
            }
        }
        foreach (glob('/usr/local/etc/unbound.yetisense.d/*.conf') as $filename) {
            if (!in_array($filename, $managed_files)) {
                $this->internalMessage = gettext(
                    'The configuration contains manual overwrites, these may interfere with the settings configured here.'
                );
                $this->internalStatus = SystemStatusCode::NOTICE;
                break;
            }
        }
    }
}
