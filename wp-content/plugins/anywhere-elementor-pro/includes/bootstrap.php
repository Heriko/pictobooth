<?php

namespace Aepro;

use Aepro\Classes\ModuleManager;
use function class_exists;
use Elementor;
use Elementor\Plugin;

class Aepro
{

    private static $_instance = null;

    public $_hook_positions = array();

    public static $_helper = null;

    public static $module_manager = null;

    public static $_theme = null;

    /** @var array Themes that are fully supported in core */
    protected $supported_themes = ['generatepress', 'oceanwp', 'astra', 'hestia', 'twentyseventeen', 'wpbf', 'page-builder-framework'];

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init()
    {

        add_post_type_support('ae_global_templates', 'elementor');
        add_filter('widget_text', 'do_shortcode');
    }

    /**
     * Plugin constructor.
     */
    private function __construct()
    {


        $this->load_hook_positions();

        self::$module_manager = new ModuleManager();

        $this->_includes();




        self::$_helper = new Helper();

        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, '_plugins_loaded']);
        add_action('elementor/init', [$this, 'elementor_loaded']);

        // for frontend scripts & styles
        add_action('wp_enqueue_scripts', [$this, '_enqueue_scripts']);

        // elementor editor scripts & styles
        add_action('elementor/editor/wp_head', [$this, '_editor_enqueue_scripts']);


        // for admin scripts & styles
        add_action('admin_enqueue_scripts', [$this, '_admin_enqueue_scripts']);


        add_action( 'elementor/widgets/widgets_registered', [$this, 'elementor_widget_registered']);
        
        add_filter('manage_ae_global_templates_posts_columns', [$this, 'set_custom_edit_ae_global_templates_posts_columns']);
        add_action('manage_ae_global_templates_posts_custom_column', [$this, 'add_ae_global_templates_columns'], 10, 2);
        add_filter('ae_pro_filter_hook_positions', [$this, 'theme_hooks']);


        // woo template hook
        add_filter('wc_get_template_part', [$this, 'load_wc_layout'], 10, 3);

        // woo scripts setup
        add_action('template_redirect', [$this, 'ae_woo_setup']);

        add_action('after_setup_theme', [$this, 'editor_woo_scripts']);

        // TODO:: Do this only if product page is using AE Template
        add_filter('woocommerce_enqueue_styles', [$this, 'load_wc_styles'], 99, 1);


        add_action('woocommerce_init', [$this, 'woo_init']);

        $map_key = get_option('ae_pro_gmap_api');
        if ($map_key) {
            add_filter('acf/fields/google_map/api', [$this, 'register_acf_map_key']);

            add_action('acf/init', [$this, 'register_acf_pro_map_key']);
        }
        add_filter('template_redirect', [$this, 'block_template_frontend']);

        add_filter('template_include', [$this, 'ae_template_canvas']);

        add_action('admin_init', [$this, 'db_upgrade_script']);

        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);


    }

    public function ae_template_canvas($template)
    {
        if (is_singular('ae_global_templates')) {
            $helper = new Helper();

            if ($helper->is_canvas_enabled(get_the_ID())) {
                $template = ELEMENTOR_PATH . '/modules/page-templates/templates/canvas.php';
                return $template;
            }

            if ($helper->is_heder_footer_enabled(get_the_ID())) {
                $template = ELEMENTOR_PATH . '/modules/page-templates/templates/header-footer.php';
                return $template;
            }
        }

        return $template;
    }

    public function register_acf_pro_map_key()
    {
        $map_key = get_option('ae_pro_gmap_api');
        acf_update_setting('google_api_key', $map_key);
    }

    public function register_acf_map_key($api)
    {
        $map_key = get_option('ae_pro_gmap_api');

        $api['key'] = $map_key;

        return $api;
    }

    public  function get_script_suffix()
    {
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        return $suffix;
    }

    public function plugin_activated()
    {
        //echo "Plugin Activated";
    }
    public function woo_init()
    {
        if (is_product() || isset($_REQUEST['ae_global_templates'])) {
            \WC_Frontend_Scripts::load_scripts();
            wp_enqueue_script('wc-single-product');
            wp_enqueue_script('wc-product-gallery-zoom');
            wp_enqueue_script('flexslider');
            wp_enqueue_script('photoswipe-ui-default');
            wp_enqueue_style('photoswipe-default-skin');
            add_action('wp_footer', 'woocommerce_photoswipe');
        }

        if (isset($_REQUEST['ae_global_templates'])) {
            add_theme_support('wc-product-gallery-zoom');
            add_theme_support('wc-product-gallery-lightbox');
            add_theme_support('wc-product-gallery-slider');
        }
    }

    public function load_wc_styles($styles)
    {
        return $styles;
    }

    function _editor_enqueue_scripts()
    {

        wp_enqueue_script('aepro-editor-js', AE_PRO_URL . 'includes/assets/js/editor' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), AE_PRO_VERSION);

        wp_localize_script('aepro-editor-js', 'aepro', array(
            'ajaxurl' => admin_url('admin-ajax.php'),

        ));

        wp_enqueue_style('vegas-css', AE_PRO_URL . 'includes/assets/lib/vegas/vegas' . AE_PRO_SCRIPT_SUFFIX . '.css');
        wp_enqueue_script('vegas', AE_PRO_URL . 'includes/assets/lib/vegas/vegas' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), '2.4.0', true);
        wp_enqueue_script('ae-elementor-editor-js', AE_PRO_URL . 'includes/assets/js/common' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery', 'ae-gmap'), AE_PRO_VERSION);

        $localize_data = array(
            'plugin_url' => plugins_url('anywhere-elementor-pro')
        );
        wp_localize_script('ae-elementor-editor-js', 'aepro_editor', $localize_data);


        wp_enqueue_script('swiper');

        $map_key = get_option('ae_pro_gmap_api');
        if ($map_key) {
            wp_enqueue_script('ae-gmap', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key);
        }

        wp_enqueue_script('ae-masonry', AE_PRO_URL . 'includes/assets/lib/masonry/js/masonry.pkgd' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery', 'jquery-masonry'), '2.0.1', true);

        wp_enqueue_style('aep-editor', AE_PRO_URL . 'includes/assets/css/aep-editor.css');

        wp_enqueue_style('aep-font', AE_PRO_URL . 'includes/assets/lib/aep-icons/style.css');
    }


    public function theme_hooks($hook_positions)
    {
        global $ae_template;
        $theme_class = "\Aepro\Themes\\".$ae_template.'\\Ae_Theme';
       
        if (class_exists($theme_class)) {
            $theme_obj = new $theme_class;
            $hook_positions = $theme_obj->theme_hooks($hook_positions);
        }
        return $hook_positions;
    }




    public function set_custom_edit_ae_global_templates_posts_columns($columns)
    {
        //unset( $columns['author'] );
        $columns['ae_shortcode_column'] = __('Shortcode', 'ae-pro');
        $columns['ae_global_template_column'] = __('Is Global', 'ae-pro');
        $columns['ae_render_mode_column'] = __('Render Mode', 'ae-pro');
        return $columns;
    }
    public function add_ae_global_templates_columns($column, $post_id)
    {

        switch ($column) {

            case 'ae_shortcode_column':
                echo '<input type=\'text\' class=\'widefat\' value=\'[INSERT_ELEMENTOR id="' . $post_id . '"]\' readonly="">';
                break;

            case 'ae_global_template_column':
                $is_global = get_post_meta($post_id, 'ae_apply_global', true);
                if (!empty($is_global)) {
                    echo '<span class="dashicons dashicons-star-filled" style="color:#ffd71c;"></span>';
                }
                break;

            case 'ae_render_mode_column':
                $helper = new Helper();
                $render_mode = get_post_meta($post_id, 'ae_render_mode', true);
                if (!empty($render_mode)) {
                    $render_modes = $helper->get_ae_render_mode_hook();

                    if (isset($render_modes[$render_mode])) {
                        echo $render_modes[$render_mode];
                    } else {
                        echo '<span style="color:#ff6033">' . $render_mode . '</span>';
                    }
                }
                break;
        }
    }

    public function _plugins_loaded()
    {

        load_plugin_textdomain('ae-pro', false, AE_PRO_FILE . 'includes/languages/ae-pro');

        if (!did_action('elementor/loaded')) {
            /* TO DO */
            add_action('admin_notices', array($this, 'ae_pro_fail_load'));
            return;
        }

        // WPML Compatibility
        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php') && is_plugin_active('wpml-string-translation/plugin.php')) {
            require_once AE_PRO_PATH . 'includes/wpml/class-wpml-ae-woo-tabs.php';
            require_once AE_PRO_PATH . 'includes/wpml/wpml-compatibility.php';
        }

        /**
         * Define ACF Constants
         *
         */
        if (class_exists('acf_pro')) {
            define('AE_ACF', true);
            define('AE_ACF_PRO', true);
        } elseif (class_exists('ACF')) {
            define('AE_ACF', true);
            define('AE_ACF_PRO', false);
        } else {
            define('AE_ACF', false);
            define('AE_ACF_PRO', false);
        }

        /**
         * Define Pods Constants
         *
         */

        if (is_plugin_active('pods/init.php')) {
            define('AE_PODS', true);
        } else {
            define('AE_PODS', false);
        }

        /** Define WooCommerce Constants
         * 
         * 
         */

        if (class_exists('woocommerce')) {
            define('AE_WOO', true);
        } else {
            define('AE_WOO', false);
        }

        /**
         * Define SEO Plugin Constants
         *
         */

        if (function_exists('yoast_breadcrumb')) {
            define('AE_YOAST_SEO', true);
        } else {
            define('AE_YOAST_SEO', false);
        }
        if (function_exists('rank_math_the_breadcrumbs')) {
            define('AE_RANK_MATH', true);
        } else {
            define('AE_RANK_MATH', false);
        }

        $elementor_version_required = '3.0';
        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
            add_action( 'admin_notices', array($this,'ae_elementor_requried_version_fail' ));
            return;
        }
    }

    private function _includes()
    {
        global $ae_template;

        $enable_generic = get_option('ae_pro_generic_theme');

        $enable_generic = 1;
        
        if (!in_array($ae_template, $this->supported_themes) && $enable_generic == 1) {
            $ae_template = 'generic';
        }
       
        if (file_exists(AE_PRO_PATH . 'includes/themes/' . $ae_template . '/Ae_Theme.php')) {
            require_once AE_PRO_PATH . 'includes/themes/' . $ae_template . '/Ae_Theme.php';

            $theme_class = '\Aepro\Themes\\'.$this->dashesToCamelCase($ae_template).'\Ae_Theme';
            self::$_theme = new $theme_class();
            
        } else {
            add_action('after_setup_theme', function () {
                do_action('ae_external_theme_support');
            });
        }

        // Todo :: load only one frontend
        require_once AE_PRO_PATH . 'includes/frontend.php';
        require_once AE_PRO_PATH . 'includes/template.php';

        require_once AE_PRO_PATH . 'includes/post_helper.php';
        require_once AE_PRO_PATH . 'includes/rules.php';

        require_once AE_PRO_PATH . 'includes/helper.php';
        require_once AE_PRO_PATH . 'includes/post-type.php';


        if (is_admin()) {
            require_once AE_PRO_PATH . 'includes/admin/admin.php';
            require_once AE_PRO_PATH . 'includes/admin/admin-helper.php';
            require_once AE_PRO_PATH . 'includes/admin/template-config.php';
            //require_once AE_PRO_PATH.'includes/license/admin.php';
        }

        require_once AE_PRO_PATH . 'includes/license/EDD_SL_Plugin_Updater.php';
        require_once AE_PRO_PATH . 'includes/license-manager.php';

        if ($this->licence_activated()) {
            //require_once AE_PRO_PATH.'includes/license/wp-updates-plugin.php';
            //new AE_Updater( 'http://wp-updates.com/api/2/plugin',plugin_basename(AE_PRO_PATH.'anywhere-elementor-pro.php') );
        }
    }

    public function licence_activated()
    {
        return true;
    }

    public function _enqueue_scripts()
    {
        global $wp;

        wp_enqueue_style('ae-pro-css', AE_PRO_URL . 'includes/assets/css/ae-pro' . AE_PRO_SCRIPT_SUFFIX . '.css', AE_PRO_VERSION);
        wp_enqueue_script('ae-pro-js', AE_PRO_URL . 'includes/assets/js/ae-pro' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), AE_PRO_VERSION, true);
        wp_enqueue_script('aepro-editor-js', AE_PRO_URL . 'includes/assets/js/common' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), AE_PRO_VERSION, true);

        wp_register_style('vegas-css', AE_PRO_URL . 'includes/assets/lib/vegas/vegas' . AE_PRO_SCRIPT_SUFFIX . '.css');
        wp_register_script('vegas', AE_PRO_URL . 'includes/assets/lib/vegas/vegas' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), '2.4.0', true);


        $helper = new Helper();

        if (Plugin::instance()->preview->is_preview_mode()) {

            $post_css = $helper->ae_get_post_css();
            wp_add_inline_style('ae-pro-css', $post_css);

            if (class_exists('ACF') || class_exists('acf')) {

                $post_cf_css = $helper->ae_get_cf_image_css();
                wp_add_inline_style('ae-pro-css', $post_cf_css);

                $post_term_cf_css = $helper->ae_get_term_cf_image_css();
                wp_add_inline_style('ae-pro-css', $post_term_cf_css);
            }
        }


        wp_enqueue_script('wc-single-product');
        wp_enqueue_style('woocommerce-general');


        wp_localize_script('ae-pro-js', 'aepro', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'current_url' => base64_encode($helper->get_current_url_non_paged()),
            'breakpoints' => Elementor\Core\Responsive\Responsive::get_breakpoints(),
        ));
        $localize_data = array(
            'plugin_url' => plugins_url('anywhere-elementor-pro')
        );
        wp_localize_script('aepro-editor-js', 'aepro_editor', $localize_data);

        $map_key = get_option('ae_pro_gmap_api');
        if ($map_key) {
            wp_register_script('ae-gmap', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key);
        }

        wp_enqueue_script('ae-masonry', AE_PRO_URL . 'includes/assets/lib/masonry/js/masonry.pkgd' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery', 'jquery-masonry'), '2.0.1', true);
        wp_register_script('ae-infinite-scroll', AE_PRO_URL . 'includes/assets/lib/infinite-scroll/infinite-scroll.pkgd' . AE_PRO_SCRIPT_SUFFIX . '.js', array('jquery'), '3.0.5', true);
    }

    public function _admin_enqueue_scripts()
    {
        $screen = get_current_screen();
        if ($screen->post_type == 'ae_global_templates') {
            $localize_data = [];
            $localize_data['aep_nonce'] = wp_create_nonce('aep_ajax_nonce');
            wp_enqueue_script('ae-admin-js', AE_PRO_URL . 'includes/admin/admin-scripts' . AE_PRO_SCRIPT_SUFFIX . '.js', array(), AE_PRO_VERSION);
            wp_localize_script('ae-admin-js', 'aepro', $localize_data);
            wp_enqueue_style('aep-select2', AE_PRO_URL . 'includes/assets/lib/select2/css/select2' . AE_PRO_SCRIPT_SUFFIX . '.css');
            wp_enqueue_script('aep-select2', AE_PRO_URL . 'includes/assets/lib/select2/js/select2' . AE_PRO_SCRIPT_SUFFIX . '.js', ['jquery']);
        }
    }

    public function load_hook_positions()
    {
        $hook_positions = array(
            '' => esc_html__('None', 'ae-pro'),
            'custom' => esc_html__('Custom', 'ae-pro'),
        );
        $this->_hook_positions = $hook_positions;
    }

    public function get_hook_positions()
    {
        return $this->_hook_positions;
    }

    public function elementor_loaded()
    {


        require_once AE_PRO_PATH . 'includes/aep-finder.php';

        add_action('elementor/finder/categories/init', function ($categories_manager) {
            // Add the category
            $categories_manager->add_category('aep-finder', new Aep_Finder());
        });
    }


    public function load_wc_layout($template, $slug, $name)
    {

        global $product, $ae_template;
        $helper = new Helper();
        $ae_wc_template = '';

        if ($slug == 'content' && $name == 'single-product') {
            $ae_wc_template = $helper->get_ae_active_post_template($product->get_id(), 'product');
            if ($ae_wc_template != '' && is_numeric($ae_wc_template)) {
                $ae_wc_path =  AE_PRO_PATH . 'includes/wc/ae-wc-single.php';
                return $ae_wc_path;
            }
        }


        if ($slug == 'content' && $name == 'product') {
            $ae_wc_template = $helper->get_woo_archive_template();

            if ($ae_wc_template != '' && is_numeric($ae_wc_template)) {
                if ($helper->is_full_override($ae_wc_template)) {
                    $ae_theme = new Ae_Theme();
                    $ae_theme->setOverride('full');

                    $ae_theme->setUseCanvas($helper->is_canvas_enabled($ae_wc_template));
                    $ae_wc_path = $ae_theme->load_archive_template($template);
                } else {
                    $ae_wc_path =  AE_PRO_PATH . 'includes/wc/ae-wc-archive.php';
                }

                return $ae_wc_path;
            }
        }



        return $template;
    }

    public function elementor_widget_registered(){
        
        // FacetWP Integration
        if(class_exists('FacetWP')){
            require_once AE_PRO_PATH. 'includes/classes/facetwp-master.php';
        }
    }

    function ae_woo_setup()
    {
        global $post;
        global $product;

        if (!class_exists('woocommerce')) {
            return false;
        }

        if (is_product()) {
            $helper = new Helper();
            $ae_product_template = $helper->get_ae_active_post_template($post->ID, 'product');

            if ($ae_product_template) {
                add_theme_support('wc-product-gallery-zoom');
                add_theme_support('wc-product-gallery-lightbox');
                add_theme_support('wc-product-gallery-slider');
            }
        }
    }

    function editor_woo_scripts()
    {

        if (is_singular('ae_global_templates')) {
            add_theme_support('wc-product-gallery-zoom');
            add_theme_support('wc-product-gallery-lightbox');
            add_theme_support('wc-product-gallery-slider');
        }
    }

    public function block_template_frontend()
    {

        if (is_singular('ae_global_templates') && !current_user_can('edit_posts')) {
            wp_redirect(site_url(), 301);
            die;
        }
    }

    public function db_upgrade_script()
    {

        //check if upgrade required
        $upgrade_required = get_option('aepro_27_upgrade_run');

        if ($upgrade_required != 1) {

            // check posts with meta key
            $args = array(
                'meta_key' => 'ae_enable_canvas',
                'meta_value' => 'true',
                'post_type' => 'ae_global_templates',
                'post_status' => 'any',
                'posts_per_page' => -1
            );
            $posts = get_posts($args);

            if (count($posts)) {

                // set new meta key
                foreach ($posts as $p) {
                    update_post_meta($p->ID, 'ae_elementor_template', 'ec');
                }
            }

            update_option('aepro_27_upgrade_run', '1');
        }
    }

    public function ae_pro_fail_load()
    {

        $plugin = 'elementor/elementor.php';

        if (_is_elementor_installed()) {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            $message = sprintf(__('<b>AnyWhere Elementor Pro</b> is not working because you need to activate the <b>Elementor</b> plugin.', 'ae-pro'), '<strong>', '</strong>');
            $action_url   = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);
            $button_label = __('Activate Elementor', 'ae-pro');
        } else {
            if (!current_user_can('install_plugins')) {
                return;
            }
            $message = sprintf(__('<b>AnyWhere Elementor Pro</b> is not working because you need to install the <b>Elementor</b> plugin.', 'ae-pro'), '<strong>', '</strong>');
            $action_url   = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');
            $button_label = __('Install Elementor', 'ae-pro');
        }

        $button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

        printf('<div class="%1$s"><p>%2$s</p>%3$s</div>', 'notice notice-error', $message, $button);
    }

    public function plugin_row_meta($plugin_meta, $plugin_file)
    {

        if (AE_PRO_BASE === $plugin_file) {
            $row_meta = [
                'docs' => '<a href="https://aedocs.webtechstreet.com/" aria-label="' . esc_attr(__('View Documentation', 'ae-pro')) . '" target="_blank">' . __('Docs', 'ae-pro') . '</a>',
            ];

            $plugin_meta = array_merge($plugin_meta, $row_meta);
        }

        return $plugin_meta;
    }

    function dashesToCamelCase($string, $capitalizeFirstCharacter = true) 
    {

        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    function ae_elementor_requried_version_fail() {
        if ( ! current_user_can( 'update_plugins' ) ) {
            return;
        }
        $elementor_version_required = '3.0.0';
        $file_path = 'elementor/elementor.php';
        $upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
        $message = '<p>' . __( 'AnyWhere Elementor Pro requires Elementor ' . $elementor_version_required . '. Please update Elementor to continue.', 'ae-pro' ) . '</p>';
        $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, __( 'Update Elementor Now', 'ae-pro' ) ) . '</p>';
        echo '<div class="error">' . $message . '</div>';
    }

}

Aepro::instance();
