<?php
/**
 * QR Code embed module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_QR_Code' ) ) {

	/**
	 * Define Jet_Engine_Module_QR_Code class
	 */
	class Jet_Engine_Module_QR_Code extends Jet_Engine_Module_Base {

		private $qr_code_api = 'https://api.qrserver.com/v1/create-qr-code/';

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'qr-code';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'QR Code for Dynamic Field widget', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {

			add_filter( 'jet-engine/listings/allowed-callbacks', array( $this, 'add_qr_code_cb' ) );
			add_filter( 'jet-engine/listing/dynamic-field/callback-args', array( $this, 'cb_args' ), 10, 4 );
			add_action( 'jet-engine/listing/dynamic-field/callback-controls', array( $this, 'cb_controls' ) );

		}

		/**
		 * Add grid gallery to callbacks
		 *
		 * @param array $callbacks [description]
		 */
		public function add_qr_code_cb( $callbacks = array() ) {
			$callbacks['jet_engine_get_qr_code'] = __( 'QR Code', 'jet-engine' );
			return $callbacks;
		}

		/**
		 * Add call-back related controls
		 *
		 * @param  [type] $widget [description]
		 * @return [type]         [description]
		 */
		public function cb_controls( $widget ) {

			$widget->add_control(
				'qr_code_size',
				array(
					'label' => esc_html__( 'QR Code Size', 'jet-engine' ),
					'type' => Elementor\Controls_Manager::SLIDER,
					'range' => array(
						'px' => array(
							'min' => 50,
							'max' => 400,
						),
					),
					'condition' => array(
						'dynamic_field_filter' => 'yes',
						'filter_callback'      => array( 'jet_engine_get_qr_code' ),
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

			if ( 'jet_engine_get_qr_code' !== $callback ) {
				return $args;
			}

			$size = ! empty( $settings['qr_code_size'] ) ? absint( $settings['qr_code_size']['size'] ) : 150;

			return array_merge( $args, array( $size ) );

		}

		/**
		 * Get QR Code for meta key
		 *
		 * @param  [type]  $meta_value [description]
		 * @param  integer $size       [description]
		 * @return [type]              [description]
		 */
		public function get_qr_code( $value = null, $size = 150 ) {

			$hash   = 'qr_' . substr( base64_encode( $size . $value ), 0, 30 );
			$cached = get_transient( $hash );

			if ( $cached ) {
				return $cached;
			}

			$request = add_query_arg(
				array(
					'size'   => $size . 'x' . $size,
					'data'   => $value,
					'format' => 'svg',
				),
				$this->qr_code_api
			);

			$response = wp_remote_get( $request );
			$svg      = wp_remote_retrieve_body( $response );

			set_transient( $hash, $svg, DAY_IN_SECONDS );

			return $svg;
		}

	}

}
