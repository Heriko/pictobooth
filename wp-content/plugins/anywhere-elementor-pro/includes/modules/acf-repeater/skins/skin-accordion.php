<?php
namespace Aepro\Modules\AcfRepeater\Skins;


use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Aepro\Base\Widget_Base;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Skin_Accordion extends Skin_Base
{

    public function get_id()
    {
        return 'accordion';
    }

    public function get_title()
    {
        return __('Accordion', 'ae-pro');

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
            'section_title_style',
            [
                'label' => __( 'Accordion', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'accordion_border',
                'label' => __( 'Border', 'ae-pro' ),
                'selector' => '{{WRAPPER}} .ae-accordion-item',
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
                    'color'  => [
                        'default' => '#7A7A7A'
                    ],
                ],
            ]
        );

        /*$this->add_control(
            'border_width',
            [
                'label' => __( 'Border Width', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item' => 'border-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-content' => 'border-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-title.elementor-active' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_color',
            [
                'label' => __( 'Border Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-content' => 'border-top-color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-title.elementor-active' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );*/

        $this->add_control(
            'accordion_space',
            [
                'label' => __( 'Space Between', 'ae-pro' ),
                'separator' => 'before',
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ae-accordion-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ae-tab-content' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'accordion_transition_speed',
            [
                'label' => __( 'Transition Speed', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1000,
                        'step' => 100,
                    ],
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_toggle_style_title',
            [
                'label' => __( 'Title', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'title_border',
                'label' => __( 'Border', 'ae-pro' ),
                'selector' => '{{WRAPPER}} .ae-tab-title',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid'
                    ],
                    'width'  => [
                        'default' => [
                            'top'    => 0,
                            'right'  => 0,
                            'bottom' => 1,
                            'left'   => 0,
                        ],
                    ],
                    'color'  => [
                        'default' => '#7A7A7A'
                    ],
                ],
            ]
        );

        $this->add_control(
            'title_background',
            [
                'label' => __( 'Background Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_background_active',
            [
                'label' => __( 'Background Color Active', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title.elementor-active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __( 'Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY
                ]
            ]
        );

        $this->add_control(
            'tab_active_color',
            [
                'label' => __( 'Active Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title.elementor-active' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT
                ]
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .elementor-accordion .elementor-tab-title',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY
                ]
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label' => __( 'Padding', 'ae-pro' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_align',
            [
                'label' => __( 'Alignment', 'ae-pro' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Start', 'ae-pro' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Middle', 'ae-pro'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __( 'End', 'ae-pro' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => is_rtl() ? 'right' : 'left',
                'toggle' => false,
                'label_block' => false,
                'selectors' => [
                    '{{WRAPPER}} .ae-tab-title' => 'text-align: {{VALUE}};',
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_toggle_style_icon',
            [
                'label' => __( 'Icon', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => __( 'Alignment', 'ae-pro' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Start', 'ae-pro' ),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => __( 'End', 'ae-pro' ),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => is_rtl() ? 'right' : 'left',
                'toggle' => false,
                'label_block' => false,
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __( 'Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title .elementor-accordion-icon i:before' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title .elementor-accordion-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_active_color',
            [
                'label' => __( 'Active Color', 'ae-pro' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title.elementor-active .elementor-accordion-icon i:before' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-title.elementor-active .elementor-accordion-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_space',
            [
                'label' => __( 'Spacing', 'ae-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-icon.elementor-accordion-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-accordion .elementor-accordion-icon.elementor-accordion-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_content',
            [
                'label' => __( 'Content', 'ae-pro' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_background_color',
            [
                'label' => __( 'Background', 'elementor' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'content_border',
                'label' => __( 'Border', 'ae-pro' ),
                'selector' => '{{WRAPPER}} .ae-tab-content',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid'
                    ],
                    'width'  => [
                        'default' => [
                            'top'    => 0,
                            'right'  => 0,
                            'bottom' => 0,
                            'left'   => 0,
                        ],
                    ],
                    'color'  => [
                        'default' => '#7A7A7A'
                    ],
                ]
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __( 'Padding', 'elementor' ),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-accordion .elementor-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function render()
    {

        $settings = $this->parent->get_settings();
        $this->generate_accordion_output($settings);



    }
}