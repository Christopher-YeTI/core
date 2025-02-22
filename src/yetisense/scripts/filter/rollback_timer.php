#!/usr/local/bin/php
<?php

/*
 * Copyright (C) 2020 Deciso B.V.
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

require_once('script/load_phalcon.php');

if (count($argv) >= 2) {
    $revision = preg_replace("/[^0-9.]/", "", $argv[1]);
    if (!empty($revision)) {
        $lckfile = "/tmp/filter_{$revision}.lock";
        file_put_contents($lckfile, "");
        // give the api 60 seconds to callback
        for ($i = 0; $i < 60; ++$i) {
            if (!file_exists($lckfile)) {
                // got feedback
                exit(0);
            }
            sleep(1);
        }
        @unlink($lckfile);
        // no feedback, revert
        $mdlFilter = new YETIsense\Firewall\Filter();
        if ($mdlFilter->rollback($revision)) {
            (new YETIsense\Core\Backend())->configdRun('filter reload');
        } else {
            syslog(LOG_WARNING, "unable to revert to unexisting revision : {$revision}");
        }
    }
}
