<?php

/**
 *    Copyright (C) 2020 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace YETIsense\Firewall\Migrations;

use YETIsense\Core\Config;
use YETIsense\Base\BaseModelMigration;

class MFP1_0_0 extends BaseModelMigration
{
    public function post($model)
    {
        // Move YETIsense->Firewall->FilterRule ---> YETIsense->Firewall->Filter
        $cfgObj = Config::getInstance()->object();
        if (
            !empty($cfgObj->YETIsense) && !empty($cfgObj->YETIsense->Firewall)
                && !empty($cfgObj->YETIsense->Firewall->FilterRule)
        ) {
            // model migration created a new, empty rules section
            if (empty($cfgObj->YETIsense->Firewall->Filter->rules)) {
                unset($cfgObj->YETIsense->Firewall->Filter->rules);
                $targetdom = dom_import_simplexml($cfgObj->YETIsense->Firewall->Filter);
                foreach ($cfgObj->YETIsense->Firewall->FilterRule->children() as $child) {
                    $sourcedom = dom_import_simplexml($child);
                    $targetdom->appendChild($sourcedom);
                }
                unset($cfgObj->YETIsense->Firewall->FilterRule);
                Config::getInstance()->save();
            }
        }
    }
}
