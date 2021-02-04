<?php

namespace Aepro\Modules\AeDynamic;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Plugin;

class Text extends Tag
{
    public function get_name()
    {
        return 'ae-text';
    }

    public function get_title()
    {
        return __('Repeater Text', 'ae-pro');
    }

    public function get_group()
    {
        return 'acf';
    }

    public function get_categories()
    {

        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
        ];
    }

    public function get_panel_template_setting_key()
    {
        return 'key';
    }

    protected function _register_controls()
    {

        DynamicHelper::instance()->ae_get_group_fields($this, $this->get_supported_fields());
    }

    public function get_supported_fields()
    {
        return [
            'text',
            'textarea',
            'number',
            'email',
            'password',
            'wysiwyg',
            //            'select',
            //            'checkbox',
            //            'radio',
            //            'true_false',

            // Pro
            //            'oembed',
            'google_map',
            'date_picker',
            'time_picker',
            'date_time_picker',
            'color_picker',
        ];
    }

    public function render()
    {
        $settings = $this->get_settings();
        $value = DynamicHelper::instance()->get_repeater_data($settings);
        echo $value;
    }


    //    protected function get_field_group(){
    //
    //        if ( function_exists( 'acf_get_field_groups' ) ) {
    //            $acf_groups = acf_get_field_groups();
    //        } else {
    //            $acf_groups = apply_filters( 'acf/get_field_groups', [] );
    //        }
    //
    //        foreach ( $acf_groups as $acf_group ) {
    //            if (function_exists('acf_get_fields')) {
    //                if (isset($acf_group['ID']) && !empty($acf_group['ID'])) {
    //                    $fields = acf_get_fields($acf_group['ID']);
    //                } else {
    //                    $fields = acf_get_fields($acf_group);
    //                }
    //            } else {
    //                $fields = apply_filters('acf/field_group/get_fields', [], $acf_group['id']);
    //            }
    //            $options=[];
    //            foreach ($fields as $field){
    //                if($field['type'] == 'repeater'){
    //                    $sub_fields = $field['sub_fields'];
    //                    foreach ($sub_fields as $sub_field){
    //                        $options[$sub_field['key']] = $sub_field['label'];
    //                    }
    //                }
    //            }
    //            if(!empty($options)){
    //                $groups [] = [
    //                    'label'     =>  $acf_group['title'],
    //                    'options'   =>  $options,
    //                ];
    //            }
    //
    //
    //        }
    //        return $groups;
    //
    //    }





}
