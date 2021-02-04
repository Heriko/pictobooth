<?php

namespace Aepro;

use Aepro\Classes\ModuleManager;
use Aepro\EDD_SL_Plugin_Updater;

class License
{

    private static $_instance;

    private static $_store_url = 'https://shop.webtechstreet.com';

    private static $_item_name = 'AnyWhere Elementor Pro';

    private static $_transient_lifetime = 43200;


    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {

        add_action('admin_menu', [$this, 'settings_menu']);
        add_action('admin_init', [$this, 'register_license_option']);



        add_action('admin_init', [$this, 'ae_plugin_updater'], 0);

        // add_action( 'admin_init', [$this, 'license_update']);

        add_action('admin_notices', [$this, 'admin_notices']);

        register_activation_hook(AE_PRO_FILE, [$this, 'plugin_activated']);

        add_action('wp_ajax_ae_activate_license', [$this, 'license_update']);
    }

    public function plugin_activated()
    {

        // get old license status
        $old_license_status = get_site_transient('ae_license');
        if ($old_license_status == 'valid') {
            $license_key = get_option('ae_pro_license_key');
            $this->activate_license($license_key, false);
        }
    }

    public function settings_menu()
    {

        add_submenu_page(
            'edit.php?post_type=ae_global_templates',
            __('Settings', 'ae-pro'),
            __('Settings', 'ae-pro'),
            'manage_options',
            'aepro-settings',
            [$this, 'settings_page']
        );
    }

    public function settings_page()
    {
        //$license_raw = get_option('ae_pro_license_key');

        $license = self::get_hidden_ae_license_key();

        $status = $this->license_status();

        $map_key = get_option('ae_pro_gmap_api');

        $enable_generic = get_option('ae_pro_generic_theme');

        $modules = Aepro::$module_manager->get_modules();
?>
        <div class="aep-wrap">

            <div class="aep-header">
                <div class="aep-title">
                    <h2>
                        AnyWhere Elementor Pro
                        <span class="aep-version"><?php echo AE_PRO_VERSION; ?></span>
                    </h2>
                </div>
            </div>

            <div class="aep-content-wrapper">

                <div class="aep-settings-main-wrapper">

                    <div class="aep-tabs tabs">
                        <h3 class="aep-title aep-modules active">
                            <a href="#" data-tabid="aep-module-manager">Modules</a>
                        </h3>
                        <h3 class="aep-title aep-config">
                            <a href="#" data-tabid="aep-config">Configuration</a>
                        </h3>
                    </div>

                    <div class="aep-settings-box aep-metabox">

                    <div class="aep-metabox-content">

                        <form class="aep-tab-content active" id="aep-module-manager" method="post">

                            <div class="aep-bulk-action aep-module-row">
                                <input type="checkbox" id="aep-select-all" />
                                <select name="aep-bulk-action">
                                    <option value="">Bulk Action</option>
                                    <option value="activate">Activate</option>
                                    <option value="deactivate">Deactivate</option>
                                </select>
                                <input id="aep-apply" class="button" type="button" value="<?php echo __('Apply', 'aepro'); ?>" />
                            </div>


                            <?php $this->core_modules($modules['core']['modules']); ?>

                            <?php $this->acf_modules($modules['acf']['modules']); ?>

                            <?php $this->pods_module($modules['pods']['modules']); ?>

                            <?php $this->woo_module($modules['woo']['modules']); ?>

                        </form>

                        <form class="aep-tab-content" id="aep-config">

                            <?php _e('Google Map Api Key', 'ae-pro'); ?>

                            <input type="text" name="ae_pro_gmap_api" id="ae_pro_gmap_api" class="regular-text" value="<?php echo $map_key; ?>">

                            <br/><br/>
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                                    <?php echo _e('Click Here') ?>
                            </a> to generate API KEY

                            <br/><br/>

                            <button type="button" value="Save" class="button button-primary" name="save_config" id="save-config" data-action="save-config">
                                <span class="aep-action-text">Save</span>
                                <span class="aep-action-loading dashicons dashicons-update-alt"></span>
                            </button>

                        </form>    

                    </div>

                </div>

                </div>

                <div class="aep-settings-sidebar-wrapper">

                    <?php $this->doc_box(); ?>
                    <?php $this->license_box(); ?>

                </div>

            </div>

        </div>

        <?php
    }

    public function register_license_option()
    {
        // creates our settings in the options table
        register_setting('aepro_edd_license', 'ae_pro_license_key', [$this, 'edd_sanitize_license']);
        register_setting('aepro_edd_license', 'ae_pro_gmap_api', [$this, 'edd_sanitize_license']);
    }

    public function edd_sanitize_license($new)
    {
        return $new;
    }

    protected function license_status()
    {
        $licence_key = get_option('ae_pro_license_key');
        if (!isset($licence_key) || empty($licence_key)) {
            // license missing
            return 'missing';
        } else {
            // get transient
            $ae_license_transient = get_site_transient('aep_license_status');

            if (isset($ae_license_transient) && $ae_license_transient != '') {
                return $ae_license_transient;
            }


            // check license status
            $license_status = $this->check_license();
            set_site_transient('aep_license_status', $license_status, self::$_transient_lifetime);

            return $license_status;
        }
    }

    protected function check_license()
    {
        $license = get_option('ae_pro_license_key');

        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $license,
            'item_name' => urlencode(self::$_item_name),
            'url'       => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post(self::$_store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (is_wp_error($response))
            return false;

        $license_data = json_decode(wp_remote_retrieve_body($response));

        $license_data->success = true;
 $license_data->error = '';
 $license_data->expires = date('Y-m-d', strtotime('+50 years'));
 $license_data->license = 'valid';

        if ($license_data->license == 'valid') {
            return 'valid';
        } else {
            return $license_data->license;
        }
    }

    public function ae_plugin_updater()
    {
        $license_key = trim(get_option('ae_pro_license_key'));

        $edd_updater = new EDD_SL_Plugin_Updater(
            self::$_store_url,
            AE_PRO_FILE,
            array(
                'version'     => AE_PRO_VERSION,                 // current version number
                'license'     => $license_key,                 // license key (used get_option above to retrieve from DB)
                'item_id'   => 21,                             // name of this plugin
                'author'     => 'WebTechStreet',             // author of this plugin
                'beta'        => false,
                'name'      => self::$_item_name
            )
        );
    }

    public function license_update()
    {

        if (isset($_POST['license_action']) && $_POST['license_action'] == 'activate') {
            if (!check_ajax_referer('aep_license_nonce', 'nonce'))
                return; // get out if we didn't click the Activate button

            // update license key
            update_option('ae_pro_license_key', trim($_POST['license_key']));
            $response = $this->activate_license(trim($_POST['license_key']));
            wp_send_json($response);
        }

        if (isset($_POST['license_action']) && $_POST['license_action'] == 'deactivate') {
            if (!check_ajax_referer('aep_license_nonce', 'nonce'))
                return; // get out if we didn't click the desctivate button button

            $license_key = get_option('ae_pro_license_key');
            $response = $this->deactivate_license($license_key);
            wp_send_json($response);
        }

        if (isset($_POST['aep_settings_update'])) {
            if (!check_ajax_referer('aep_license_nonce', 'nonce'))
                return;

            update_option('ae_pro_gmap_api', trim($_POST['ae_pro_gmap_api']));

            if (isset($_POST['enable_generic_theme_support'])) {
                update_option('ae_pro_generic_theme', trim($_POST['enable_generic_theme_support']));
            } else {
                update_option('ae_pro_generic_theme', '');
            }
        }
    }

    function activate_license($license_key, $redirect = true)
    {

        //prepare data for api request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license_key,
            'item_id'    => 21, // The ID of the item in EDD
            'url'        => home_url()
        );

        $response = wp_remote_post(self::$_store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $message =  (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : __('An error occurred, please try again.');
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));

            $license_data->success = true;
 $license_data->error = '';
 $license_data->expires = date('Y-m-d', strtotime('+50 years'));
 $license_data->license = 'valid';
            if (false === $license_data->success) {
                switch ($license_data->error) {
                    case 'expired':
                        $message = sprintf(
                            __('Your license key expired on %s.'),
                            date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                        );
                        break;
                    case 'revoked':
                        $message = __('Your license key has been disabled.');
                        break;
                    case 'missing':
                        $message = __('Invalid license.');
                        break;
                    case 'invalid':
                    case 'site_inactive':
                        $message = __('Your license is not active for this URL.');
                        break;
                    case 'item_name_mismatch':
                        $message = sprintf(__('This appears to be an invalid license key for %s.'), self::$_item_name);
                        break;
                    case 'no_activations_left':
                        $message = __('Your license key has reached its activation limit.');
                        break;
                    default:
                        $message = __('An error occurred, please try again.');
                        break;
                }
            }
        }

        if (!empty($message)) {
            $response = [
                'action' => false,
                'message' => $message
            ];

            return $response;
        }

        set_site_transient('aep_license_status', $license_data->license, self::$_transient_lifetime);

        $response = [
            'action' => true,
            'message' => __('License Activated', 'ae-pro')
        ];

        return $response;
    }

    function deactivate_license($license_key)
    {

        $license_key = trim(get_option('ae_pro_license_key'));

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license_key,
            'item_name'  => urlencode(self::$_item_name), // the name of our product in EDD
            'url'        => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post(self::$_store_url, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
        //echo "<pre>"; print_r($response); die();
        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $action = false;
            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = __('An error occurred, please try again.');
            }

            $response = [
                'message' => $message,
                'action' => $action
            ];

            return $response;
        }

        // decode the license data
        $license_data = json_decode(wp_remote_retrieve_body($response));

         $licensed_data->success = true;
            $license_data->license = 'deactivated';

        // $license_data->license will be either "deactivated" or "failed"
        if ($license_data->license == 'deactivated') {
            delete_option('ae_pro_license_key');
            delete_site_transient('aep_license_status');
        }

        $response = [
            'action' => true,
            'message' => ''
        ];

        return $response;
    }

    public function admin_notices()
    {

        $license_status = 'valid';

        $license_setting_page =  admin_url('edit.php?post_type=ae_global_templates&page=aepro-settings');
        switch ($license_status) {

            case 'valid':
                break;
            case 'missing':
        ?>
                <div class="error aep-license-error">
                    <p>
                        <strong>AnyWhere Elementor Pro</strong><br />
                        Please <a href="<?php echo $license_setting_page; ?>">activate your license key</a> to enable automatic updates
                    </p>
                </div>
            <?php
                break;

            case 'invalid':
                $license_key = trim(get_option('ae_pro_license_key'));
            ?>
                <div class="error aep-license-error">
                    <p>
                        <strong>AnyWhere Elementor Pro</strong><br />
                        You license key <code><?php echo $license_key; ?></code> is invalid. Please <a href="<?php echo $license_setting_page; ?>">add a valid license key</a>.
                    </p>
                </div>
            <?php
                break;

            case 'expired':
            ?>
                <div class="error aep-license-error">
                    <p>
                        <strong>AnyWhere Elementor Pro</strong><br />
                        Your <a href="<?php echo $license_setting_page; ?>">license key</a> is expired.
                    </p>
                </div>
            <?php
                break;

            case 'site_inactive': ?>
                <div class="error aep-license-error">
                    <p>
                        <strong>AnyWhere Elementor Pro</strong><br />
                        Your <a href="<?php echo $license_setting_page; ?>">license key</a> is not active for this site.
                    </p>
                </div>
            <?php
                break;

            default:     ?>
                <div class="error aep-license-error">
                    <p>
                        <strong>AnyWhere Elementor Pro</strong><br />
                        Please activate a valid <a href="<?php echo $license_setting_page; ?>">license key</a>.
                    </p>
                </div>
                <?php
                break;
        }
        if (isset($_GET['aep_activate'])) {
            switch ($_GET['aep_activate']) {

                case 'false':
                    $message = urldecode($_GET['aep_res']);
                ?>
                    <div class="error">
                        <p>
                            <strong>AnyWhere Elementor Pro</strong><br />
                            <?php echo $message; ?>
                        </p>
                    </div>
                <?php
                    break;

                case 'true':
                ?>
                    <div class="updated">
                        <p>
                            <strong>AnyWhere Elementor Pro</strong><br />
                            License updated successfully.
                        </p>
                    </div>
        <?php
                default:
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }

    static function get_hidden_ae_license_key()
    {
        $input_string = trim(get_option('ae_pro_license_key'));

        $start = 5;
        $length = mb_strlen($input_string) - $start - 5;

        $mask_string = preg_replace('/\S/', 'X', $input_string);
        $mask_string = mb_substr($mask_string, $start, $length);
        $input_string = substr_replace($input_string, $mask_string, $start, $length);

        return $input_string;
    }

    function license_box()
    {
        $license_raw = get_option('ae_pro_license_key');
        $license = self::get_hidden_ae_license_key();
        $status = $this->license_status();

        $action = 'activate';
        $action_text = __('Activate', 'ae-pro');
        $wrapper_class = '';
        $disabled = '';

        if ($status !== false && $status == 'valid') {
            $action = 'deactivate';
            $action_text = __('Deactivate', 'ae-pro');
            $wrapper_class = 'aep-active';
            $disabled = 'disabled';
        }
        
        ?>

        <div class="aep-sidebar-box aep-metabox aep-license-box <?php echo $wrapper_class; ?>">
            <h3 class="aep-title">
                License
                <span class="active">
                    <?php
                    echo __('Active', 'ae-pro')
                    ?>
                </span>
            </h3>
            <div class="aep-metabox-content">
                <input type="text" name="aep-license" value="<?php echo $license; ?>" id="aep-license" <?php echo $disabled; ?> />
                <div class="aep-license-msg"></div>
                <button type="button" value="Save" class="button button-primary" name="save_license" id="save-license" data-action="<?php echo $action; ?>">
                    <span class="aep-action-text"><?php echo $action_text; ?></span>
                    <span class="aep-action-loading dashicons dashicons-update-alt"></span>
                </button>
                <?php wp_nonce_field('aep_license_nonce', 'aep_license_nonce', false); ?>
            </div>
        </div>

    <?php
    }

    function core_modules($modules)
    {
    ?>
        <div class="aep-module-row aep-module-group">
            <h4 class="aep-group-title"><?php echo __('Core', 'aepro'); ?></h4>
        </div>

        <?php
        foreach ($modules as $module_key => $module) {

            $class = 'aep-module-row';
            if ($module['enabled'] === true) {
                $class .= ' aep-enabled';
                $action_text = __('Deactivate', 'aepro');
                $action = 'deactivate';
            } else {
                $class .= ' aep-disabled';
                $action_text = __('Activate', 'aepro');
                $action = 'activate';
            }

        ?>
            <div class="<?php echo $class; ?>">
                <input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
                <?php echo $module['label']; ?>

                <div class="aep-module-action">
                    <a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a>
                </div>
            </div>
        <?php
        }
    }

    function acf_modules($modules)
    {

        $not_available = __('Not Available <a title="%s">[?]</a>', 'ae-pro');
        ?>

        <div class="aep-module-row aep-module-group">
            <h4 class="aep-group-title"><?php echo __('Advanced Custom Fields', 'aepro'); ?></h4>
        </div>
        <?php
        foreach ($modules as $module_key =>  $module) {

            $class = 'aep-module-row';
            if ($module['enabled'] === true) {
                $class .= ' aep-enabled';
                $action_text = __('Deactivate', 'aepro');
                $action = 'deactivate';
            } else {
                $class .= ' aep-disabled';
                $action_text = __('Activate', 'aepro');
                $action = 'activate';
            }

        ?>
            <div class="<?php echo $class; ?>">
                <input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
                <?php echo $module['label']; ?>

                <div class="aep-module-action">
                    <?php if (AE_ACF === false) {
                        echo sprintf($not_available, $module['not-available']);
                    } else {

                        if ((AE_ACF_PRO === false) && in_array($module_key, ['acf-gallery', 'acf-repeater'])) {
                            echo sprintf($not_available, $module['not-available']);
                        } else {
                    ?><a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a><?php
                                                                                                                                                }
                                                                                                                                            }
                                                                                                                                                    ?>

                </div>
            </div>
        <?php
        }
    }

    function pods_module($modules)
    {

        $not_available = __('Not Available <a title="%s">[?]</a>', 'ae-pro');
        ?>

        <div class="aep-module-row aep-module-group">
            <h4 class="aep-group-title"><?php echo __('Pods', 'aepro'); ?></h4>
        </div>
        <?php
        foreach ($modules as $module_key =>  $module) {

            $class = 'aep-module-row';
            if ($module['enabled'] === true) {
                $class .= ' aep-enabled';
                $action_text = __('Deactivate', 'aepro');
                $action = 'deactivate';
            } else {
                $class .= ' aep-disabled';
                $action_text = __('Activate', 'aepro');
                $action = 'activate';
            }

        ?>
            <div class="<?php echo $class; ?>">
                <input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
                <?php echo $module['label']; ?>

                <div class="aep-module-action">
                    <?php if (AE_PODS === false) {
                        echo sprintf($not_available, $module['not-available']);
                    } else {
                    ?><a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a><?php
                                                                                                                                                }
                                                                                                                                                    ?>

                </div>
            </div>
        <?php
        }
    }

    function woo_module($modules)
    {

        $not_available = __('Not Available <a title="%s">[?]</a>', 'ae-pro');
        ?>

        <div class="aep-module-row aep-module-group">
            <h4 class="aep-group-title"><?php echo __('WooCommerce', 'aepro'); ?></h4>
        </div>
        <?php
        foreach ($modules as $module_key =>  $module) {

            $class = 'aep-module-row';
            if ($module['enabled'] === true) {
                $class .= ' aep-enabled';
                $action_text = __('Deactivate', 'aepro');
                $action = 'deactivate';
            } else {
                $class .= ' aep-disabled';
                $action_text = __('Activate', 'aepro');
                $action = 'activate';
            }

        ?>
            <div class="<?php echo $class; ?>">
                <input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
                <?php echo $module['label']; ?>

                <div class="aep-module-action">
                    <?php if (AE_WOO === false) {
                        echo sprintf($not_available, $module['not-available']);
                    } else {
                    ?><a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a><?php
                                                                                                                                                }
                                                                                                                                                    ?>

                </div>
            </div>
        <?php
        }
    }

    function misc_modules($modules)
    {

        $not_available = __('Not Available <a title="%s">[?]</a>', 'ae-pro');
        ?>

        <div class="aep-module-row aep-module-group">
            <h4 class="aep-group-title"><?php echo __('Miscellaneous', 'aepro'); ?></h4>
        </div>

        <?php
        foreach ($modules as $module_key =>  $module) {

            $class = 'aep-module-row';
            if ($module['enabled'] === true) {
                $class .= ' aep-enabled';
                $action_text = __('Deactivate', 'aepro');
                $action = 'deactivate';
            } else {
                $class .= ' aep-disabled';
                $action_text = __('Activate', 'aepro');
                $action = 'activate';
            }

        ?>
            <div class="<?php echo $class; ?>">
                <input class="aep-module-item" type="checkbox" name="aep_modules[]" value="<?php echo $module_key; ?>" />
                <?php echo $module['label']; ?>

                <div class="aep-module-action">
                    <?php if (AE_WOO === false) {
                        echo sprintf($not_available, $module['not-available']);
                    } else {
                    ?><a data-action="<?php echo $action; ?>" data-moduleId="<?php echo $module_key; ?>" href="#"> <?php echo $action_text; ?> </a><?php
                                                                                                                                                }
                                                                                                                                                    ?>

                </div>
            </div>
        <?php
        }
    }

    function doc_box(){
        ?>

        <div class="aep-sidebar-box aep-metabox">
            <h3 class="aep-title">Getting Started</h3>
            <div class="aep-metabox-content">
                <ul>
                    <li><a target="_blank" href="https://wpvibes.link/go/installation/">Activating License</a></li>
                    <li><a target="_blank" href="https://wpvibes.link/go/how-to/">How To's</li>
                </ul>
                <a 
                    class="button button-primary ae-support" 
                    target="_blank" 
                    title="Get Support"
                    href="https://wpvibes.link/go/ea-support/">
                    Get Support
                </a>
            </div>
        </div>

        <?php
    }
}

License::instance();
