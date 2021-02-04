<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Link' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Link class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Link extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-link';
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return apply_filters( 'jet-engine/blocks-views/block-types/attributes/dynamic-link', array(
				'dynamic_link_source' => array(
					'type'    => 'string',
					'default' => '_permalink',
				),
				'dynamic_link_option' => array(
					'type'    => 'string',
					'default' => '',
				),
				'dynamic_link_profile_page' => array(
					'type'    => 'string',
					'default' => '',
				),
				'dynamic_link_source_custom' => array(
					'type'    => 'string',
					'default' => '',
				),
				'delete_link_dialog' => array(
					'type'    => 'string',
					'default' => __( 'Are you sure you want to delete this post?', 'jet-engine' ),
				),
				'delete_link_redirect' => array(
					'type'    => 'string',
					'default' => '',
				),
				'delete_link_type' => array(
					'type'    => 'string',
					'default' => 'trash',
				),
				'selected_link_icon' => array(
					'type'    => 'number',
				),
				'selected_link_icon_url' => array(
					'type'    => 'string',
					'default' => '',
				),
				'link_label' => array(
					'type'    => 'string',
					'default' => '%title%',
				),
				'link_wrapper_tag' => array(
					'type'    => 'string',
					'default' => 'div',
				),
				'add_query_args' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'query_args' => array(
					'type' => 'string',
				),
				'url_prefix' => array(
					'type' => 'string',
				),
				'open_in_new' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'rel_attr' => array(
					'type'    => 'string',
					'default' => '',
				),
				'hide_if_empty' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			) );
		}

	}

}