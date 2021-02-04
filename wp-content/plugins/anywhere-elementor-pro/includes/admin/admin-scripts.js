jQuery(document).ready(function($){

    var wrapper = $('#aep_config_box');
    wrapper.find('#ae-config-general').attr('aria-hidden', false);
    wrapper.find(".f-row").attr('aria-hidden', true);

    var modules = aepro.modules;

    initialLoad();

    activate_post_load();
    activate_term_load();
    activate_acf_repeater_fields_load();
    ae_modules();

    jQuery(document).on('change',
            '[name="ae_apply_global"], ' +
            '[name="ae_render_mode"], ' +
            '[name="ae_hook_apply_on[]"], ' +
            '[name="ae_usage"], ' +
            '[name="ae_repeater_loc"]',
        function(){
            wrapper.find(".f-row").attr('aria-hidden', true);
            initialLoad();

    });

    $(".ae-config-wrapper").on('click', '.ae-config-nav a', function(e){
        e.preventDefault();

        $(".ae-config-nav li").attr('aria-selected', false);
        $(this).closest('li').attr('aria-selected', true);

        href = $(this).attr('href');

        $('.ae-config-content').attr('aria-hidden', true);
        $(href).attr('aria-hidden', false);
    });





    function activate_post_load(){
        jQuery('#ae_preview_post_ID').aeselect2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function (params) {
                    render_mode = jQuery('[name="ae_render_mode"]').val();
                    if(render_mode != 'block_layout' && render_mode != 'acf_repeater_layout'){
                        post_type = jQuery('#ae_rule_post_type').val();
                    }else{
                        post_type = 'any';
                    }

                    return {
                        q: params.term,
                        action: 'ae_prev_post',
                        post_type: post_type
                    }
                },
                processResults: function (res) {
                    return {
                        results: res.data
                    }
                }
            },
            minimumInputLength: 2
        });
    }

    function activate_term_load(){
        jQuery('#ae_preview_term').aeselect2({
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                data: function (params) {
                    taxonomy = jQuery('#ae_rule_taxonomy').val();
                    return {
                        q: params.term,
                        action: 'ae_prev_term',
                        taxonomy: taxonomy
                    }
                },
                processResults: function (res) {
                    return {
                        results: res.data
                    }
                }
            },
            minimumInputLength: 2
        });
    }

    function activate_acf_repeater_fields_load(){
        jQuery('#ae_preview_post_ID, #ae_repeater_loc').on('change',function () {


            render_mode = $('[name="ae_render_mode"]').val();
            if(render_mode != 'acf_repeater_layout'){
                return;
            }

            id = jQuery('#ae_preview_post_ID').val();
            repeater_loc = jQuery('#ae_repeater_loc').val();
            jQuery.ajax({
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'ae_acf_repeater_fields',
                    post_id: id,
                    repeater_loc: repeater_loc
                },
                success: function (res) {
                    jQuery("#ae_acf_repeater_name").find('option').remove().end();
                    if(res.data.length){
                        jQuery.each(res.data, function(i, d) {
                            jQuery("#ae_acf_repeater_name").append(jQuery("<option/>", {
                                value: d.id,
                                text: d.text
                            }));
                        });
                    }
                }
            });
        });

    }

    function ae_modules() {

        const aep_wrap  = document.querySelector('.aep-wrap');
        if(aep_wrap === null){
            // Not on settings page
            return;
        }

        const selectAll = document.querySelector('#aep-select-all');
        const moduleCtas = document.querySelectorAll('.aep-module-action');
        const applyAll = document.querySelector("#aep-apply");
        const saveLicense = document.querySelector('#save-license');
        const saveConfig = document.querySelector('#save-config');

        const tabs = document.querySelectorAll('.aep-tabs .aep-title a');


        // Settings Tab
        tabs.forEach( tab => {
            tab.addEventListener('click', function(e){
                
                e.preventDefault();
                
                const tab_anchors = document.querySelectorAll('.aep-tabs .aep-title');
                const tab_id = e.target.dataset.tabid;
            
                tab_anchors.forEach( tab_anchor => tab_anchor.classList.remove('active') );
                e.target.parentElement.classList.add('active');

                console.log(`#${tab_id}`);
                document.querySelectorAll('.aep-tab-content').forEach( tab_content => tab_content.classList.remove('active') );
                document.querySelector(`#${tab_id}`).classList.add('active');

            });
        });
        


        // Select All for Bulk Action
        selectAll.addEventListener('change', function(e){
            
            const modules = document.querySelectorAll('.aep-module-item');
            if(this.checked){
                modules.forEach(function(module){
                    module.checked = true;
                });
            }else{
                modules.forEach(function(module){
                    module.checked = false;
                });
            }
        });


        // Bind event for Activate/Deactivate button
        moduleCtas.forEach(function(moduleAction){
            
            moduleAction.addEventListener('click', function(e){
                
                e.stopPropagation();
                e.preventDefault();

                const cta = e.target;
                const moduleKey = cta.dataset.moduleid;
                const moduleAction = cta.dataset.action;
                
                let moduleData = {};
                moduleData[moduleKey] = moduleAction;

                cta.classList.add('updating');
                
                $.ajax({
                    url: ajaxurl,
                    method: 'post',
                    data: {
                        action : 'aep_module',
                        moduleData
                    },
                    success: function(res){
                        
                        const modules = res.modules;
                        
                        for(module in modules){
                            if (modules.hasOwnProperty(module)) {
                                let status = modules[module];
                                let module_anchor = document.querySelector(`[data-moduleid='${module}']`);
                                
                                if(status === false){
                                    module_anchor.textContent = 'Activate';
                                    module_anchor.dataset.action = 'activate';
                                    module_anchor.parentElement.parentElement.classList.remove('aep-enabled');
                                    module_anchor.parentElement.parentElement.classList.add('aep-disabled');
                                }else{
                                    module_anchor.textContent = 'Deactivate';
                                    module_anchor.dataset.action = 'deactivate';
                                    module_anchor.parentElement.parentElement.classList.add('aep-enabled');
                                    module_anchor.parentElement.parentElement.classList.remove('aep-disabled');
                                }

                                module_anchor.classList.remove('updating');
                                // uncheck all checkboxes
                                moduleCBs = document.querySelectorAll('.aep-module-item');
                                moduleCBs.forEach( modulecb => modulecb.checked = false );
                            }
                        }

                    }
                });
                
            });
        });

        // Apply all button
        applyAll.addEventListener('click', function(e){

            const bulkAction = document.querySelector('[name="aep-bulk-action"]').value;
            const moduleData = {};
            if(bulkAction === ''){
                alert('Please select an action');
                return;
            }

            
            modules = document.querySelectorAll('.aep-module-item');
            modules.forEach(function(module){
                
                if(module.checked){
                    moduleData[module.value] = bulkAction;
                    module.nextSibling.nextSibling.children[0].classList.add('updating');
                }

            });

            if(Object.keys(moduleData).length === 0){
                alert('Please select atleast one module');
                return;
            }

            // all set - now call the ajax and update modules. 
            $.ajax({
                url: ajaxurl,
                method: 'post',
                data: {
                    action : 'aep_module',
                    moduleData
                },
                success: function(res){
                    
                    const modules = res.modules;
                    
                    for(module in modules){
                        if (modules.hasOwnProperty(module)) {

                            const status = modules[module];
                            const module_anchor = document.querySelector(`[data-moduleid='${module}']`);
                            
                            // Change anchor content
                            // Change dataset action
                            // toggle enabled class
                            if(status === false){
                                module_anchor.textContent = 'Activate';
                                module_anchor.dataset.action = 'activate';
                                module_anchor.parentElement.parentElement.classList.remove('aep-enabled');
                                module_anchor.parentElement.parentElement.classList.add('aep-disabled');
                            }else{
                                module_anchor.textContent = 'Deactivate';
                                module_anchor.dataset.action = 'deactivate';
                                module_anchor.parentElement.parentElement.classList.remove('aep-disabled');
                                module_anchor.parentElement.parentElement.classList.add('aep-enabled');
                            }

                            module_anchor.classList.remove('updating');
                            // uncheck all checkboxes
                            moduleCBs = document.querySelectorAll('.aep-module-item');
                            moduleCBs.forEach( modulecb => modulecb.checked = false );
                        }
                    }

                }
            });
        });

        // Activate License 
        saveLicense.addEventListener('click', function(e){
            
            const license_key = document.querySelector('#aep-license').value;
            const license_action = this.dataset.action;
            const license_box = document.querySelector('.aep-license-box');

            const license_msg_box = document.querySelector('.aep-license-msg');

            const nonce = document.querySelector('#aep_license_nonce').value;

            const button = this;

            license_msg_box.textContent = '';

            if(license_key === ''){
                alert('Please enter the license key');
                return;
            }

            button.classList.add('loading');
            
            // run fetch
            const data = {
                action : 'ae_activate_license',
                license_key,
                license_action, 
                nonce
            }

            $.ajax({
                method: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data,
                success: function(data){
                    

                    if(data.action === false){
                        license_msg_box.textContent = data.message;
                    }else{

                        if(license_action == 'activate'){
                            // set active class to wrapper
                            license_box.classList.add('aep-active');

                            // change button text
                            document.querySelector('.aep-license-box .aep-action-text').textContent = 'Deactivate';

                            // change button data-action
                            button.dataset.action = 'deactivate';

                            // set license field disabled
                            document.querySelector('#aep-license').disabled = true; 

                            const errorbox = document.querySelector('.aep-license-error');
                            
                            if(errorbox){
                                errorbox.remove();
                            }
                            

                        }else{
                            // remove active class from wrapper
                            license_box.classList.remove('aep-active');
                            
                            // change button text
                            document.querySelector('.aep-license-box .aep-action-text').textContent = 'Activate';

                            // change button data-action
                            button.dataset.action = 'activate';

                            // make license key blank and remove disabled. 
                            document.querySelector('#aep-license').value = '';
                            document.querySelector('#aep-license').disabled = false;  
                        }
                        

                    }
                    button.classList.remove('loading');
                    
                }
            });

        });

        saveConfig.addEventListener('click', function(e){
            
            const ae_pro_gmap_api = document.querySelector('#ae_pro_gmap_api').value;
            const btn = this;

            btn.classList.add('loading');

            $.ajax({
                url: ajaxurl,
                method: 'post',
                data: {
                    action: 'aep_save_config',
                    config: {
                        ae_pro_gmap_api
                    },
                    nonce: aepro.aep_nonce
                    
                },
                success: function(res){
                    btn.classList.remove('loading');
                }
            })
        })

        async function postData(url = '', data = {}) {

            // Prepare form data
            let formData = new FormData();
            for (let [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }
            console.log(formData);
            // Default options are marked with *
            const response = await fetch(url, {
              method: 'POST', // *GET, POST, PUT, DELETE, etc.
              mode: 'cors', // no-cors, *cors, same-origin
              cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
              credentials: 'same-origin', // include, *same-origin, omit
              headers: {
                  //'Content-Type': 'application/json'
                  // 'Content-Type': 'application/x-www-form-urlencoded',
                  'Content-Type': 'multipart/form-data'
              },
              redirect: 'follow', // manual, *follow, error
              referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
              body: formData // body data type must match "Content-Type" header
            });
            return response.json(); // parses JSON response into native JavaScript objects
          }

    }

    function initialLoad(){
        showfield('ae_render_mode');

        $('#sec-rules').hide();

        var render_mode = $('[name="ae_render_mode"]').val();

        switch(render_mode){
            case 'post_type_archive_template'   :    pt_archive();
                                                     break;

            case 'post_template'                :    post_template();
                                                     break;

            case 'archive_template'             :    archive_template();
                                                     break;

            case 'block_layout'                 :    block_layout();
                                                     break;

            case 'normal'                       :    normal();
                                                     break;

            case '404'                          :    _404();
                                                     break;

            case 'search'                       :    _search();
                                                     break;

            case 'author_template'              :   _author();
                                                     break;

            case 'date_template'                :   _date();
                                                    break;

            case 'acf_repeater_layout'          :   acf_repeater_layout();
                                                    break;
        }
    }

    function showfield(field){
        $('[name="' + field +'"]').closest('.f-row').attr('aria-hidden', false);
    }

    function _404(){
        showfield('ae_elementor_template');
    }

    function _search(){
        showfield('ae_elementor_template');
    }

    function archive_template(){
        //showfield('ae_preview_post_ID');
        showfield('ae_apply_global');
        showfield('ae_rule_taxonomy');
        showfield('ae_full_override');
        showfield('ae_elementor_template');
        showfield('ae_preview_term');

    }

    function block_layout(){
        showfield('ae_preview_post_ID');

    }

    function acf_repeater_layout(){
        showfield('ae_repeater_loc');

        repeater_loc = $('[name="ae_repeater_loc"]').val();
        
        if(repeater_loc == 'post'){
            showfield('ae_preview_post_ID');
        }
        showfield('ae_acf_repeater_name');

    }

    function normal(){
        showfield('ae_usage');

        usage_area = $('[name="ae_usage"]').val();

        if(usage_area == 'custom'){
            showfield("ae_custom_usage_area");
        }

        if(usage_area != ''){

            showfield('ae_apply_global');
            auto_apply = $('[name="ae_apply_global"]').is(":checked");

            if(!auto_apply){
                $('li.ae-rules').show();
                // auto apply not set.. reveal advanced rules
                showfield('ae_hook_apply_on[]');

                page_types = $("input[name='ae_hook_apply_on[]']:checked").map(function () {return this.value;}).get();

                // show post options in case of single post

                if(page_types.indexOf('single') >= 0){
                    showfield('ae_hook_post_types[]');
                    showfield('ae_hook_posts_selected');
                    showfield('ae_hook_posts_excluded');
                }

                if(page_types.indexOf('archive') >= 0){
                    showfield('ae_hook_taxonomies[]');
                    showfield('ae_hook_terms_selected');
                    showfield('ae_hook_terms_excluded');
                }
            }



        }
    }

    function post_template(){
        showfield('ae_preview_post_ID');
        showfield('ae_apply_global');
        showfield('ae_rule_post_type');
        showfield('ae_elementor_template');
    }

    function pt_archive(){
        //showfield('ae_preview_post_ID');
        showfield('ae_rule_post_type_archive');
        showfield('ae_full_override');
        showfield('ae_elementor_template');
    }

    function _author() {
        showfield('ae_apply_global');
        showfield('ae_elementor_template');
        showfield('ae_preview_author');
    }

    function _date() {
        //showfield('ae_apply_global');
        showfield('ae_elementor_template');
    }

});


( function( $ ) {

    // Write all new code here

} )( jQuery );

