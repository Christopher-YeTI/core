<?php

/*
 * Copyright (C) 2022 Deciso B.V.
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

namespace YETIsense\Interfaces\FieldTypes;

use YETIsense\Base\FieldTypes\BaseListField;
use YETIsense\Core\Config;

class VipInterfaceField extends BaseListField
{
    private static $interfaces = [];
    protected function actionPostLoadingEvent()
    {
        if (empty(self::$interfaces)) {
            $configHandle = Config::getInstance()->object();
            if (!empty($configHandle->interfaces)) {
                foreach ($configHandle->interfaces->children() as $ifname => $node) {
                    if (!empty((string)$node->type) && $node->type == 'group') {
                        continue;
                    } elseif (!empty((string)$node->if) && $node->if == 'enc0') {
                        continue;
                    }
                    $descr = !empty((string)$node->descr) ? (string)$node->descr : strtoupper($ifname);
                    self::$interfaces[$ifname] = $descr;
                }
            }
        }
        $this->internalOptionList = self::$interfaces;
        return parent::actionPostLoadingEvent();
    }
}
