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
namespace YETIsense\Firewall\Api;

class NptController extends FilterBaseController
{
    protected static $categorysource = "npt.rule";

    public function searchRuleAction()
    {
        $category = $this->request->get('category');
        $filter_funct = function ($record) use ($category) {
            return empty($category) || array_intersect(explode(',', $record->categories), $category);
        };
        return $this->searchBase(
            "npt.rule",
            ['enabled', 'sequence', 'source_net', 'destination_net', 'trackif', 'description'],
            "sequence",
            $filter_funct
        );
    }

    public function setRuleAction($uuid)
    {
        return $this->setBase("rule", "npt.rule", $uuid);
    }

    public function addRuleAction()
    {
        return $this->addBase("rule", "npt.rule");
    }

    public function getRuleAction($uuid = null)
    {
        return $this->getBase("rule", "npt.rule", $uuid);
    }

    public function delRuleAction($uuid)
    {
        return $this->delBase("npt.rule", $uuid);
    }

    public function toggleRuleAction($uuid, $enabled = null)
    {
        return $this->toggleBase("npt.rule", $uuid, $enabled);
    }
}
