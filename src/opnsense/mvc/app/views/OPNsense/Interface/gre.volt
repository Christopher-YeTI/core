{#
 # Copyright (c) 2024 Deciso B.V.
 # All rights reserved.
 #
 # Redistribution and use in source and binary forms, with or without modification,
 # are permitted provided that the following conditions are met:
 #
 # 1. Redistributions of source code must retain the above copyright notice,
 #    this list of conditions and the following disclaimer.
 #
 # 2. Redistributions in binary form must reproduce the above copyright notice,
 #    this list of conditions and the following disclaimer in the documentation
 #    and/or other materials provided with the distribution.
 #
 # THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 # INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 # AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 # AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 # OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 # SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 # INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 # CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 # ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 # POSSIBILITY OF SUCH DAMAGE.
 #}

<script>
    $( document ).ready(function() {
        $("#{{formGridGre['table_id']}}").UIBootgrid(
            {   search:'/api/interfaces/gre_settings/searchItem/',
                get:'/api/interfaces/gre_settings/getItem/',
                set:'/api/interfaces/gre_settings/setItem/',
                add:'/api/interfaces/gre_settings/addItem/',
                del:'/api/interfaces/gre_settings/delItem/'
            }
        );
        $("#reconfigureAct").SimpleActionButton();
        ajaxGet('/api/interfaces/gre_settings/get_if_options', [], function(data, status){
            if (data.single) {
                $(".net_selector").replaceInputWithSelector(data);
            }
        });

    });
</script>
<div class="tab-content content-box">
    {{ partial('layout_partials/base_bootgrid_table', formGridGre)}}
</div>
<section class="page-content-main">
    <div class="content-box">
        <div class="col-md-12">
            <br/>
            <div id="greChangeMessage" class="alert alert-info" style="display: none" role="alert">
                {{ lang._('After changing settings, please remember to apply them.') }}
            </div>
            <button class="btn btn-primary" id="reconfigureAct"
                    data-endpoint='/api/interfaces/gre_settings/reconfigure'
                    data-label="{{ lang._('Apply') }}"
                    data-error-title="{{ lang._('Error reconfiguring GRE') }}"
                    type="button"
            ></button>
            <br/><br/>
        </div>
    </div>
</section>


{{ partial("layout_partials/base_dialog",['fields':formDialogGre,'id':formGridGre['edit_dialog_id'],'label':lang._('Edit GRE')])}}
