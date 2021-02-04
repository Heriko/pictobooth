<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Field' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Field class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Field extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-field';
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
					'default' => 'object',
				),
				'dynamic_field_post_object' => array(
					'type' => 'string',
					'default' => 'post_title',
				),
				'dynamic_field_relation_type' => array(
					'type' => 'string',
					'default' => 'grandparents',
				),
				'dynamic_field_relation_post_type' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_post_meta' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_option' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_post_meta_custom' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_wp_excerpt' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'dynamic_excerpt_more' => array(
					'type' => 'string',
					'default' => '...',
				),
				'dynamic_excerpt_length' => array(
					'type' => 'string',
					'default' => '',
				),
				'selected_field_icon' => array(
					'type' => 'number',
				),
				'selected_field_icon_url' => array(
					'type' => 'string',
					'default' => '',
				),
				'field_tag' => array(
					'type' => 'string',
					'default' => 'div',
				),
				'hide_if_empty' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'field_fallback' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_filter' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'filter_callback' => array(
					'type' => 'string',
					'default' => '',
				),
				'date_format' => array(
					'type' => 'string',
					'default' => 'F j, Y',
				),
				'num_dec_point' => array(
					'type' => 'string',
					'default' => '.',
				),
				'num_thousands_sep' => array(
					'type' => 'string',
					'default' => ',',
				),
				'num_decimals' => array(
					'type' => 'number',
					'default' => 2,
				),
				'related_list_is_single' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'related_list_is_linked' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'related_list_tag' => array(
					'type' => 'string',
					'default' => 'ul',
				),
				'multiselect_delimiter' => array(
					'type' => 'string',
					'default' => ',',
				),
				'switcher_true' => array(
					'type' => 'string',
					'default' => '',
				),
				'switcher_false' => array(
					'type' => 'string',
					'default' => '',
				),
				'dynamic_field_custom' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'dynamic_field_format' => array(
					'type' => 'string',
					'default' => '%s',
				),
			);
		}

	}

}