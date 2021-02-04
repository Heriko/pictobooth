<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Terms' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Terms class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Terms extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-terms';
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return array(
				'from_tax' => array(
					'type' => 'string',
					'default' => '',
				),
				'show_all_terms' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'terms_num' => array(
					'type' => 'number',
					'default' => 1,
				),
				'terms_delimiter' => array(
					'type' => 'string',
					'default' => ',',
				),
				'terms_linked' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'selected_terms_icon' => array(
					'type' => 'number',
				),
				'selected_terms_icon_url' => array(
					'type' => 'string',
					'default' => '',
				),
				'terms_prefix' => array(
					'type' => 'string',
					'default' => '',
				),
				'terms_suffix' => array(
					'type' => 'string',
					'default' => '',
				),
				'hide_if_empty' => array(
					'type' => 'boolean',
					'default' => false,
				),
			);
		}

	}

}