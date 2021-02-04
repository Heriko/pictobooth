<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Image' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Image class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Image extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-image';
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return array(
				'dynamic_image_source' => array(
					'type' => 'string',
					'default' => 'post_thumbnail',
				),
				'dynamic_image_source_custom' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_option' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_image_size' => array(
					'type' => 'string',
					'default' => 'full',
				),
				'dynamic_avatar_size' => array(
					'type' => 'number',
					'default' => 50,
				),
				'linked_image' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'image_link_source' => array(
					'type' => 'string',
					'default' => '_permalink',
				),
				'image_link_option' => array(
					'type' => 'string',
					'default' => '',
				),
				'open_in_new' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'rel_attr' => array(
					'type' => 'string',
					'default' => '',
				),
				'hide_if_empty' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'fallback_image' => array(
					'type' => 'number',
				),
				'fallback_image_url' => array(
					'type' => 'string',
					'default' => '',
				),
			);
		}

	}

}