<?php
namespace Aepro\Modules\AcfRepeater\Skins;


use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Tabs extends Skin_Base
{

    public function get_id()
    {
        return 'tabs';
    }

    public function get_title()
    {
        return __('Tabs', 'ae-pro');

    }

    protected function _register_controls_actions()
    {
        parent::_register_controls_actions();

        add_action('elementor/element/ae-acf-repeater/repeater_section/after_section_end', [$this, 'register_layout_controls']);

    }

    public function register_controls(Widget_Base $widget)
    {
        $this->parent = $widget;
        parent::register_general_controls();


    }
    public function register_layout_controls(){

        $this->start_controls_section(
            'section_tabs_style',
            [
                'label' => __( 'General', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'border_width',
            [
                'label' => __( 'Border Width', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title, {{WRAPPER}} .ae-acf-repeater-tab-title:before, {{WRAPPER}} .ae-acf-repeater-tab-title:after, {{WRAPPER}} .ae-acf-repeater-tab-content, {{WRAPPER}} .ae-acf-repeater-tabs-content-wrapper' => 'border-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .advance-style .ae-acf-repeater-tab-title:before, {{WRAPPER}} .advance-style .ae-acf-repeater-tab-title:after' => 'bottom: -{{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_control(
            'tabs_content_space',
            [
                'label' => __( 'Space Between Tab/Cotent', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.ae-acf-repeater-tabs-view-horizontal .ae-acf-repeater-tabs-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.ae-acf-repeater-tabs-view-vertical .ae-acf-repeater-tabs-wrapper' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'render_type' => 'template'
            ]
        );

        $this->add_control(
            'advance_style',
            [
                'label' => __('Advance', 'ae-pro'),
                'type' => Controls_Manager::SWITCHER,
                'separator' => 'before',
                'default' => '',
                'label_on' => __('Yes', 'ae-pro'),
                'label_off' => __('No', 'ae-pro'),
                'return_value' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_tab_navigation_style',
            [
                'label' => __( 'Tab Navigation', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('advance_style') => 'yes'
                ]
            ]
        );

        $this->add_control(
            'navigation_width',
            [
                'label' => __( 'Navigation Width', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'range' => [
                    '%' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs-wrapper' => 'width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'tab_layout' => 'vertical',
                ],
            ]
        );

        $this->add_control(
            'tab_nav_background_color',
            [
                'label' => __( 'Background Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'navigation_border',
                'label' => __( 'Tabs Border', 'ae-pro' ),
                'default' => '1px',
                'fields_options' => [
                    'width' => [
                        'responsive' => false,
                    ]
                ],
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tabs-wrapper',
            ]
        );

        $this->add_control(
            'space_around_tabs',
            [
                'label' => __( 'Space Between Tab/Content', 'ae-pro' ),
                'type'   => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_tab_item_style',
            [
                'label' => __( 'Tab', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('advance_style') => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'tab_title_padding',
            [
                'label'  => __('Padding','ae-pro'),
                'type'   => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tab_style');

        $this->start_controls_tab(
            'normal_tab_style',
            [
                'label' => __('Normal', 'ae-pro')
            ]
        );

        $this->add_control(
            'tab_color',
            [
                'label' => __( 'Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT
                ]
            ]
        );

        $this->add_control(
            'tab_background_color',
            [
                'label' => __( 'Background Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tab_typography',
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tab-title',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY
                    ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tab_border',
                'label' => __( 'Border', 'ae-pro' ),
                'default' => '1px',
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tabs .ae-acf-repeater-tab-title',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid'
                    ],
                    'width'  => [
                        'default' => [
                            'top'    => 1,
                            'right'  => 1,
                            'bottom' => 1,
                            'left'   => 1,
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'tab_border_radius',
            [
                'label' => __('Border Radius', 'ae-pro'),
                'type'  => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs .ae-acf-repeater-tab-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'active_tab_style',
            [
                'label' => __('Active / Hover', 'ae-pro')
            ]
        );

        $this->add_control(
            'tab_active_color',
            [
                'label' => __( 'Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title.active, {{WRAPPER}} .ae-acf-repeater-tab-title:hover' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY
                ]
            ]
        );

        $this->add_control(
            'tab_background_color_active',
            [
                'label' => __( 'Background Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-title.active, {{WRAPPER}} .ae-acf-repeater-tab-title:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tab_border_active',
                'label' => __( 'Border', 'ae-pro' ),
                'default' => '1px',
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tabs .ae-acf-repeater-tab-title.active, {{WRAPPER}} .ae-acf-repeater-tabs-wrapper .ae-acf-repeater-tab-title:hover',
            ]
        );

        $this->add_responsive_control(
            'tab_border_radius_active',
            [
                'label' => __('Border Radius', 'ae-pro'),
                'type'  => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs .ae-acf-repeater-tab-title.active, ae-acf-repeater-tabs-wrapper .ae-acf-repeater-tab-title:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tab_title_shadow',
                'label' => __( 'Shadow', 'ae-pro' ),
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tab-title',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_content_style',
            [
                'label' => __( 'Content', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('advance_style') => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'content_border',
                'label' => __( 'Border', 'ae-pro' ),
                'selector' => '{{WRAPPER}} .ae-acf-repeater-tabs-content-wrapper .ae-acf-repeater-tab-content',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid'
                    ],
                    'width'  => [
                        'default' => [
                            'top'    => 1,
                            'right'  => 1,
                            'bottom' => 1,
                            'left'   => 1,
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => __( 'Border Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY
                ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-mobile-title, {{WRAPPER}} .ae-acf-repeater-tab-desktop-title.active,
                    {{WRAPPER}} .ae-acf-repeater-tab-title:before, {{WRAPPER}} .ae-acf-repeater-tab-title:after,
                    {{WRAPPER}} .ae-acf-repeater-tab-content, {{WRAPPER}} .ae-acf-repeater-tabs-content-wrapper' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __( 'Background Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tabs-desktop-title.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .ae-acf-repeater-tabs-content-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tab_content_padding',
            [
                'label'  => __('Padding','ae-pro'),
                'type'   => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ae-acf-repeater-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

    public function render()
    {

        $settings = $this->parent->get_settings();
        $this->generate_tabs_output($settings);



    }
}