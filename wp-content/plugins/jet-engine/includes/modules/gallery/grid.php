<?php
/**
 * Gallery grid module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Gallery_Grid' ) ) {

	/**
	 * Define Jet_Engine_Module_Gallery_Grid class
	 */
	class Jet_Engine_Module_Gallery_Grid extends Jet_Engine_Module_Base {

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'gallery-grid';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Grid Gallery for Dynamic Field widget', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {

			add_filter( 'jet-engine/listings/allowed-callbacks', array( $this, 'add_grid_cb' ) );
			add_filter( 'jet-engine/listing/dynamic-field/callback-args', array( $this, 'cb_args' ), 10, 4 );
			add_action( 'jet-engine/listing/dynamic-field/callback-controls', array( $this, 'cb_controls' ) );
			add_action( 'jet-engine/listing/dynamic-field/misc-style-controls', array( $this, 'style_controls' ) );

		}

		/**
		 * Add grid gallery to callbacks
		 *
		 * @param array $callbacks [description]
		 */
		public function add_grid_cb( $callbacks = array() ) {
			$callbacks['jet_engine_img_gallery_grid'] = __( 'Images gallery grid', 'jet-engine' );
			return $callbacks;
		}

		/**
		 * Add gallery style controls
		 *
		 * @param  [type] $widget [description]
		 * @return [type]         [description]
		 */
		public function style_controls( $widget ) {

			$widget->add_responsive_control(
				'img_gallery_gap',
				array(
					'label'      => __( 'Images gap', 'jet-engine' ),
					'type'       => Elementor\Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'range'      => array(
						'px' => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'selectors'  => array(
						$widget->css_selector( ' .jet-engine-gallery-grid__item' ) => 'padding: calc( {{SIZE}}{{UNIT}}/2 );',
						$widget->css_selector( ' .jet-engine-gallery-grid' ) => 'margin: calc( -{{SIZE}}{{UNIT}}/2 );',
					),
					'condition' => array(
						'dynamic_field_filter' => 'yes',
						'filter_callback'      => array( 'jet_engine_img_gallery_grid' ),
					),
				)
			);

			$widget->start_controls_tabs( 'tabs_overlay_style' );

			$widget->start_controls_tab(
				'tab_overlay_normal',
				array(
					'label' => esc_html__( 'Image Overlay', 'jet-engine' ),
				)
			);

			$widget->add_control(
				'img_overlay_color',
				array(
					'label'     => __( 'Color', 'jet-engine' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						$widget->css_selector( ' .jet-engine-gallery-item-wrap:after' ) => 'background: {{VALUE}}',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->start_controls_tab(
				'tab_overlay_hover',
				array(
					'label' => esc_html__( 'Hover Overlay', 'jet-engine' ),
				)
			);

			$widget->add_control(
				'img_hover_overlay_color',
				array(
					'label'     => __( 'Color', 'jet-engine' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						$widget->css_selector( ' .jet-engine-gallery-item-wrap:hover:after' ) => 'background: {{VALUE}}',
					),
				)
			);

			$widget->end_controls_tab();

			$widget->end_controls_tabs();

			$widget->add_control(
				'img_icon_color',
				array(
					'separator' => 'before',
					'label'     => __( 'Lightbox Icon Color', 'jet-engine' ),
					'type'      => Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						$widget->css_selector( ' .jet-engine-gallery-item-wrap:before' ) => 'color: {{VALUE}}',
					),
				)
			);
		}

		/**
		 * Add call-back related controls
		 *
		 * @param  [type] $widget [description]
		 * @return [type]         [description]
		 */
		public function cb_controls( $widget ) {

			$widget->add_responsive_control(
				'img_columns',
				array(
					'label'     => esc_html__( 'Columns', 'jet-engine' ),
					'type'      => Elementor\Controls_Manager::SELECT,
					'default'   => 3,
					'options'   => array(
						1 => 1,
						2 => 2,
						3 => 3,
						4 => 4,
						5 => 5,
						6 => 6,
					),
					'condition' => array(
						'dynamic_field_filter' => 'yes',
						'filter_callback'      => array( 'jet_engine_img_gallery_grid' ),
					),
				)
			);

			$widget->add_control(
				'img_gallery_size',
				array(
					'label'     => __( 'Images Size', 'jet-engine' ),
					'type'      => Elementor\Controls_Manager::SELECT,
					'default'   => 'full',
					'options'   => jet_engine_get_image_sizes(),
					'condition' => array(
						'dynamic_field_filter' => 'yes',
						'filter_callback'      => array( 'jet_engine_img_gallery_grid' ),
					),
				)
			);

			$widget->add_control(
				'img_gallery_lightbox',
				array(
					'label'        => esc_html__( 'Use lightbox', 'jet-engine' ),
					'type'         => Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'jet-engine' ),
					'label_off'    => esc_html__( 'No', 'jet-engine' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition' => array(
						'dynamic_field_filter' => 'yes',
						'filter_callback'      => array( 'jet_engine_img_gallery_grid' ),
					),
				)
			);

		}

		/**
		 * Callback arguments
		 *
		 * @param  [type] $args     [description]
		 * @param  [type] $callback [description]
		 * @param  [type] $settings [description]
		 * @param  [type] $widget   [description]
		 * @return [type]           [description]
		 */
		public function cb_args( $args, $callback, $settings, $widget ) {

			if ( 'jet_engine_img_gallery_grid' !== $callback ) {
				return $args;
			}

			$gallery_args = array(
				'size'        => ! empty( $settings['img_gallery_size'] ) ? $settings['img_gallery_size'] : 'full',
				'lightbox'    => ! empty( $settings['img_gallery_lightbox'] ) ? true : false,
				'cols_desk'   => ! empty( $settings['img_columns'] ) ? $settings['img_columns'] : 3,
				'cols_tablet' => ! empty( $settings['img_columns_tablet'] ) ? $settings['img_columns_tablet'] : 3,
				'cols_mobile' => ! empty( $settings['img_columns_mobile'] ) ? $settings['img_columns_mobile'] : 1,
			);

			return array_merge( $args, array( $gallery_args ) );

		}

	}

}
