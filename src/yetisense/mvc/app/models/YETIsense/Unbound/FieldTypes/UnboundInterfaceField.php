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

namespace YETIsense\Unbound\FieldTypes;

use YETIsense\Base\FieldTypes\BaseListField;
use YETIsense\Core\Config;

/**
 * Class UnboundDomainField
 * @package YETIsense\Unbound\FieldTypes
 */
class UnboundInterfaceField extends BaseListField
{
    /**
     * Iterate over all interfaces in the configuration and only exclude virtual interfaces.
     */
    public function actionPostLoadingEvent()
    {
        $config = Config::getInstance()->object();

        foreach ($config->interfaces->children() as $key => $node) {
            if (empty($node->virtual)) {
                $this->internalOptionList[$key] = !empty($node->descr) ? (string)$node->descr : strtoupper($key);
            }
        }

        foreach ($config->openvpn->children() as $mode => $setting) {
            if (!empty($setting)) {
                $key = 'ovpn' . substr($mode, 8, 1) . (string)$setting->vpnid;
                $type = substr($mode, 8, 6);
                $this->internalOptionList[$key] = "OpenVPN {$type} (" . (!empty($setting->description) ?
                    (string)$setting->description : (string)$setting->vpnid) . ")";
            }
        }

        natcasesort($this->internalOptionList);
    }
}
