<?php

namespace Aepro\Classes;

use Elementor\Controls_Manager;
use Aepro\Modules\AeDynamic;

class ModuleManager
{

    const TAB_AE_PRO = 'tab_ae_pro';

    private $modules = [];

    public function __construct()
    {

        add_action('plugins_loaded', [$this, 'init_modules']);
        add_action('plugins_loaded', [$this, 'elementor_widget_registered']);

        add_filter('elementor/init', [$this, 'add_ae_tab'], 10, 1);

        add_action('wp_ajax_aep_module', [$this, 'save_modules']);

        add_action('wp_ajax_aep_save_config', [ $this, 'save_config']);

        add_action('elementor/elements/categories_registered', [$this, 'register_category'], -999);

        // Register Dynamic Tags
        add_action('elementor/dynamic_tags/register_tags', [$this, 'register_dynamic_tags']);
    }

    function register_category()
    {

        \Elementor\Plugin::instance()->elements_manager->add_category(
            'ae-template-elements',
            [
                'title'  => 'AE Template Elements',
                'icon' => 'fa fa-plug'
            ],
            1
        );
    }

    function init_modules()
    {

        // Test Work
        $this->modules = [];

        $this->modules['core'] = [
            'label'   => __('General', 'ae-pro'),
            'modules' => [

                'author' => [
                    'label' => 'Post Author',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'breadcrumb' => [
                    'label' => 'Breadcrumb',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'google-map' => [
                    'label' => 'CF Google Map',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'custom-field' => [
                    'label' => 'Custom Field',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'portfolio' => [
                    'label' => 'Portfolio',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-blocks' => [
                    'label' => 'Post blocks',
                    'type' => 'widget',
                    'enabled' => true
                ],

                'post-comments' => [
                    'label' => 'Post Comments',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-content' => [
                    'label' => 'Post Content',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-title' => [
                    'label' => 'Post Title',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-image' => [
                    'label' => 'Post Image',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-meta' => [
                    'label' => 'Post Meta',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-navigation' => [
                    'label' => 'Post Navigation',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'post-readmore' => [
                    'label' => 'Post Read-More',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'searchform' => [
                    'label' => 'Search Form',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'tax-custom-field' => [
                    'label' => 'Taxonomy Custom Field',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'taxonomy' => [
                    'label' => 'Taxonomy',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'taxonomy-blocks' => [
                    'label' => 'Taxonomy Blocks',
                    'type'  => 'widget',
                    'enabled' => true
                ],

                'bg-slider' => [
                    'label' => __('Background Slider', 'ae-pro'),
                    'type' => 'feature',
                    'enabled' => true
                ],

                'dynamic-bg' => [
                    'label' => __('Dynamic Backgrounds', 'ae-pro'),
                    'type' => 'feature',
                    'enabled' => true
                ]

            ]
        ];


        $this->modules['acf'] = [
            'label' => __('ACF', 'ae-pro'),
            'modules' => [

                'acf-fields' => [
                    'label' => 'ACF Fields',
                    'type'  => 'widget',
                    'enabled' => true,
                    'not-available' => __("Requires 'Advanced Custom Field' or 'Advanced Custom Field Pro' plugin installed and activated", 'ae-pro')
                ],

                'acf-gallery' => [
                    'label' => 'ACF Gallery',
                    'type'  => 'widget',
                    'enabled' => true,
                    'not-available' => __("Requires 'Advanced Custom Field Pro' plugin installed and activated", 'ae-pro')
                ],

                'acf-repeater' => [
                    'label' => 'ACF Repeater',
                    'type'  => 'widget',
                    'enabled' => true,
                    'not-available' => __("Requires 'Advanced Custom Field Pro' plugin installed and activated", 'ae-pro')
                ]
            ]
        ];

        $this->modules['pods'] = [
            'label' => __('Pods', 'ae-pro'),
            'modules' => [

                'Pods-fields' => [
                    'label' => 'Pods Fields',
                    'type'  => 'widget',
                    'enabled' => true,
                    'not-available' => __("Requires 'Pods' plugin installed and activated", 'ae-pro')
                ],
            ]
        ];

        $this->modules['woo'] = [
            'label' => __('WooCommerce Integration', 'ae-pro'),
            'modules' => [
                'woo' => [
                    'label' => __('WooCommerce Widgets', 'ae-pro'),
                    'type' => 'widget',
                    'enabled' => true,
                    'not-available' => __("Requires 'WooCommerce' plugin installed and activated", 'ae-pro')
                ]
            ]
        ];

        $saved_modules = get_option('aep_modules');

        if ($saved_modules !== false) {
            foreach ($this->modules as $group => $modules) {

                foreach ($modules['modules'] as $modulekey => $moduleName) {

                    if (isset($saved_modules[$modulekey])) {
                        $this->modules[$group]['modules'][$modulekey]['enabled'] = $saved_modules[$modulekey];
                    } else {
                        $this->modules[$group]['modules'][$modulekey]['enabled'] = true;
                    }
                }
            }
        }

        $this->modules = apply_filters('wts_aep_active_modules', $this->modules);
    }

    function get_modules()
    {

        return $this->modules;
    }

    function elementor_widget_registered()
    {

        foreach ($this->modules as $group) {

            if (is_array($group['modules']) && count($group['modules'])) {

                foreach ($group['modules'] as $key => $value) {

                    if ($value['enabled']) {
                        $class_name = str_replace('-', ' ', $key);
                        $class_name = str_replace(' ', '', ucwords($class_name));
                        $class_name = 'Aepro' . '\\Modules\\' . $class_name . '\\Module';
                        $class_name::instance();
                    }
                }
            }
        }
    }

    function add_ae_tab()
    {
        Controls_Manager::add_tab(self::TAB_AE_PRO, __('AE PRO', 'ae-pro'));
    }

    function save_modules()
    {

        $module_data = $_POST['moduleData'];

        // get saved modules
        $saved_modules = get_option('aep_modules');

        foreach ($module_data as $key => $action) {

            if ($action == 'deactivate') {
                $saved_modules[$key] = false;
            } else {
                $saved_modules[$key] = true;
            }
        }

        update_option('aep_modules', $saved_modules);

        wp_send_json([
            'modules' => $saved_modules
        ]);
    }

    function register_dynamic_tags($dynamic_tags)
    {
        if (AE_ACF) {
            if (!is_plugin_active('elementor-pro/elementor-pro.php')) {
                \Elementor\Plugin::$instance->dynamic_tags->register_group('acf', [
                    'title' => 'ACF'
                ]);
            }
            $dynamic_tags->register_tag(AeDynamic\Text::class);
            $dynamic_tags->register_tag(AeDynamic\Option::class);
            $dynamic_tags->register_tag(AeDynamic\Url::class);
            $dynamic_tags->register_tag(AeDynamic\Image::class);
            $dynamic_tags->register_tag(AeDynamic\Gallery::class);
            $dynamic_tags->register_tag(AeDynamic\Boolean::class);
        }
    }

    function save_config(){
        
        check_ajax_referer('aep_ajax_nonce', 'nonce');

        
        $gmap_api = sanitize_text_field($_POST['config']['ae_pro_gmap_api']);

        update_option('ae_pro_gmap_api', trim($gmap_api));

        wp_send_json([
            'success' => 1
        ]);
    }
}
