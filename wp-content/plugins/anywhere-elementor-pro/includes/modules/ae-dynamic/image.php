<?php

namespace Aepro\Modules\AeDynamic;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Plugin;
use function is_numeric;

class Image extends Data_Tag
{
    public function get_name()
    {
        return 'ae-image';
    }

    public function get_title()
    {
        return __('Repeater Media', 'ae-pro');
    }

    public function get_group()
    {
        return 'acf';
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
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

    protected function get_supported_fields()
    {
        return [
            'image',
            'file',
            'oembed',
        ];
    }

    public function get_value(array $options = [])
    {

        $settings = $this->get_settings();
        $value = DynamicHelper::instance()->get_repeater_data($settings);
        if(is_array($value)){
            $image_data['id'] = $value['id'];
            $image_data['url'] = $value['url'];
        }
        elseif(is_numeric($value)){
            $image_url = wp_get_attachment_url($value);
            $image_data['id'] = $value;
            $image_data['url'] = $image_url;
        }
        else{
            $image_id = attachment_url_to_postid($value);
            $image_data['id'] = $image_id;
            $image_data['url'] = $value;
        }
        return $image_data;
    }
}
