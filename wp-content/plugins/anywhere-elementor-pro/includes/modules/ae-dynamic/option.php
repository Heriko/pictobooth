<?php

namespace Aepro\Modules\AeDynamic;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Plugin;
use Aepro\Classes\AcfMaster;
use Aepro\Frontend;

class Option extends Tag
{
    public function get_name()
    {
        return 'ae-option';
    }

    public function get_title()
    {
        return __('Repeater Option', 'ae-pro');
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
        ];
    }

    public function get_panel_template_setting_key()
    {
        return 'key';
    }

    protected function _register_controls()
    {

        DynamicHelper::instance()->ae_get_group_fields($this, $this->get_supported_fields());

        $this->add_control(
            'display_data',
            [
                'label' =>  __('Display Data', 'ae-pro'),
                'type'  =>  Controls_Manager::SELECT,
                'options'   =>  [
                    'key'   =>  __('Key', 'ae-pro'),
                    'value' =>  __('Value', 'ae-pro')
                ],
                'default'   =>  'value',
            ]
        );
        $this->add_control(
            'separator',
            [
                'label'     =>  __('Separator', 'ae-pro'),
                'type'      =>    Controls_Manager::TEXT,
                'default'       =>  ',',
                'render_type'   =>  'template',
            ]
        );
    }

    public function get_supported_fields()
    {
        return [
            'select',
            'checkbox',
            'radio',
            'true_false',
            'button_group',
            // Pro
        ];
    }

    public function render()
    {
	    $settings = $this->get_settings();
	    if(!empty($settings['acf_repeater'])){
		    $repeater_data =  explode(':', $settings['acf_repeater']);
	    }

	    if(!empty($repeater_data[0]) && !empty($repeater_data[1]) && !empty($repeater_data[2])){
		    $repeater_is = $repeater_data[0];
		    $repeater = $repeater_data[2];
		    $field_name = $settings[$repeater_data[1] . ':' . $repeater_data[2]];

	    }

	    if (!empty($repeater)) {
		    $value = '';
		    $separator = '';
		    if (!Frontend::$_in_repeater_block) {
			    //            echo 'editor';
			    if ($repeater_is == 'option') {
				    $field_object   = get_field_object($repeater, 'option');
				    $repeater_field = get_field($repeater, 'option');
				    if(!empty($field_name)){
					    $field_data   = acf_get_sub_field($field_name, $field_object);
					    $selected       = $repeater_field[0][$field_name];
				    }
				    if(!empty($field_data)){
					    $choices      = $field_data['choices'];
				    }

				    $separator      = $settings['separator'];
				    if(!empty($choices)){
					    if ($settings['display_data'] == 'value') {
						    $value = $this->show_value($choices, $selected, $separator);
					    } else {
						    $value = $this->show_key($choices, $selected, $separator);
					    }
				    }

			    } else {
				    $post_data    = Aepro::$_helper->get_demo_post_data();
				    $post_id      = $post_data->ID;
				    $field_object = get_field_object($repeater, $post_id);
				    $repeater_field = get_field($repeater, $post_id, true);
				    if(!empty($field_name)){
					    $field_data   = acf_get_sub_field($field_name, $field_object);
					    $selected       = $repeater_field[0][$field_name];
				    }
				    if(!empty($field_data)){
					    $choices      = $field_data['choices'];
				    }
				    $separator      = $settings['separator'];
				    if(!empty($choices)){
					    if ($settings['display_data'] == 'value') {
						    $value = $this->show_value($choices, $selected, $separator);
					    } else {
						    $value = $this->show_key($choices, $selected, $separator);
					    }
				    }

			    }
		    } else {
			    //            echo 'frontend';
			    if(!empty($field_name)){
				    $field_object = get_sub_field_object($field_name, true);
				    $selected     = get_sub_field($field_name, true);
			    }
			    if(!empty($field_object)){
				    $choices      = $field_object['choices'];
			    }
			    $separator    = $settings['separator'];
			    if(!empty($choices)){
				    if ($settings['display_data'] == 'value') {
					    $value = $this->show_value($choices, $selected, $separator);
				    } else {
					    $value = $this->show_key($choices, $selected, $separator);
				    }
			    }

		    }

		    echo $value;
	    }
    }

    function show_value($choices, $selected, $selector)
    {
        $value = '';
        if (is_array($selected)) {
            //echo 'value array';
            $selected_item = [];
            foreach ($choices as $key => $choice) {
                if (in_array($key, $selected, true)) {
                    $selected_item[] = $choice;
                }
            }
            $value = implode($selector, $selected_item);
        } else {
            foreach ($choices as $key => $choice) {
                if ($key === $selected) {
                    $value = $choice;
                }
            }
        }
        return $value;
    }

    function show_key($choices, $selected, $selector)
    {
        $value = '';
        //print_r($selected);
        if (is_array($selected)) {
            $selected_item = [];
            foreach ($choices as $key => $choice) {
                if (in_array($key, $selected)) {
                    $selected_item[] = $key;
                }
            }
            $value = implode($selector, $selected_item);
        } else {
            foreach ($choices as $key => $choice) {
                if ($key === $selected) {
                    $value = $key;
                }
            }
        }

        return $value;
    }
}
