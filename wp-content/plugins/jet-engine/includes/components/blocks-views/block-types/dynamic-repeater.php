<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Repeater' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Repeater class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Repeater extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-repeater';
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return array(
				'dynamic_field_source' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_option' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_format' => array(
					'type' => 'string',
					'default' => '<span>%name%</span>',
				),
				'item_tag' => array(
					'type' => 'string',
					'default' => 'div',
				),
				'items_delimiter' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_before' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_after' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_counter' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'dynamic_field_leading_zero' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'dynamic_field_counter_after' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_counter_position' => array(
					'type' => 'string',
					'default' => 'at-left',
				),
				'hide_if_empty' => array(
					'type' => 'boolean',
					'default' => false,
				),
			);
		}

	}

}