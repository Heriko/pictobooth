<?php

namespace Aepro\Modules\Woo\Widgets;

use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;


class AeWooStockStatus extends Widget_Base{
    public function get_name() {
        return 'ae-woo-stock-status';
    }

    public function is_enabled(){

        if(AE_WOO){
            return true;
        }

        return false;
    }
    
    public function get_title() {
        return __( 'AE - Woo Stock Status', 'ae-pro' );
    }

    public function get_icon() {
        return 'eicon-woocommerce';
    }

    public function get_categories() {
        return [ 'ae-template-elements' ];
    }

    public function _register_controls()
    {
        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'General', 'ae-pro' ),
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __( 'Alignment', 'ae-pro' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'ae-pro' ),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'ae-pro' ),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'ae-pro' ),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_stock_style',
            [
                'label' => __( 'General', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'text_color',
            [
                'label' => __( 'Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT
                ],
                'selectors' => [
                    '{{WRAPPER}} .ae-element-woo-stock-status .stock' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY
                ],
                'selector' => '{{WRAPPER}} .ae-element-woo-stock-status .stock',
            ]
        );
        $this->end_controls_section();
    }

    public function render(){
        global $product;
        $settings = $this->get_settings();
        $product = Aepro::$_helper->get_ae_woo_product_data();
        if(!$product){
            return '';
        }

        $this->add_render_attribute( 'woo-stock-status-class', 'class', 'ae-element-woo-stock-status' );

        $availability      = $product->get_availability();
        $availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>';
        ?>
        <?php if(isset($availability['availability'])) { ?>
            <span <?php echo $this->get_render_attribute_string('woo-stock-status-class'); ?>>
            <?php echo apply_filters('woocommerce_stock_html', $availability_html, $availability['availability'], $product); ?>
        </span>
            <?php
        }

    }
}