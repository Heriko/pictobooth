<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Base' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Base class
	 */
	abstract class Jet_Engine_Blocks_Views_Type_Base {

		protected $namespace = 'jet-engine/';

		public function __construct() {

			$attributes = $this->get_attributes();

			/**
			 * Set default blocks attributes to avoid errors
			 */
			$attributes['className'] = array(
				'type' => 'string',
				'default' => '',
			);

			register_block_type(
				$this->namespace . $this->get_name(),
				array(
					'attributes'      => $attributes,
					'render_callback' => array( $this, 'render_callback' ),
					'editor_style'    => 'jet-engine-frontend',
				)
			);
		}

		abstract public function get_name();

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		abstract public function get_attributes();

		/**
		 * Retruns attra from input array if not isset, get from defaults
		 *
		 * @return [type] [description]
		 */
		public function get_attr( $attr = '', $all = array() ) {
			if ( isset( $all[ $attr ] ) ) {
				return $all[ $attr ];
			} else {
				$defaults = $this->get_attributes();
				return isset( $defaults[ $attr ]['default'] ) ? $defaults[ $attr ]['default'] : '';
			}
		}

		/**
		 * Allow to filter raw attributes from block type instance to adjust JS and PHP attributes format
		 *
		 * @param  [type] $attributes [description]
		 * @return [type]             [description]
		 */
		public function prepare_attributes( $attributes ) {
			return $attributes;
		}

		public function render_callback( $attributes = array() ) {

			$item       = $this->get_name();
			$listing    = isset( $_REQUEST['listing'] ) ? $_REQUEST['listing'] : array();
			$object_id  = isset( $_REQUEST['object'] ) ? absint( $_REQUEST['object'] ) : array();
			$attributes = $this->prepare_attributes( $attributes );
			$render     = jet_engine()->listings->get_render_instance( $item, $attributes );

			if ( ! $render ) {
				return __( 'Item renderer class not found', 'jet-engine' );
			}

			$render->setup_listing( $listing, $object_id, true );

			return $render->get_content();

		}

	}

}