<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Dynamic_Meta' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Dynamic_Meta class
	 */
	class Jet_Engine_Blocks_Views_Type_Dynamic_Meta extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return [type] [description]
		 */
		public function get_name() {
			return 'dynamic-meta';
		}

		public function prepare_attributes( $attributes = array() ) {

			$attributes['meta_items'] = array();

			if ( ! isset( $attributes['date_enabled'] ) || true === $attributes['date_enabled'] ) {

				$attributes['meta_items'][] = array(
					'type'          => 'date',
					'selected_icon' => $this->get_attr( 'date_selected_icon', $attributes ),
					'prefix'        => $this->get_attr( 'date_prefix', $attributes ),
					'suffix'        => $this->get_attr( 'date_suffix', $attributes ),
				);

			}

			if ( ! isset( $attributes['author_enabled'] ) || true === $attributes['author_enabled'] ) {

				$attributes['meta_items'][] = array(
					'type'          => 'author',
					'selected_icon' => $this->get_attr( 'author_selected_icon', $attributes ),
					'prefix'        => $this->get_attr( 'author_prefix', $attributes ),
					'suffix'        => $this->get_attr( 'author_suffix', $attributes ),
				);

			}

			if ( ! isset( $attributes['comments_enabled'] ) || true === $attributes['comments_enabled'] ) {

				$attributes['meta_items'][] = array(
					'type'          => 'comments',
					'selected_icon' => $this->get_attr( 'comments_selected_icon', $attributes ),
					'prefix'        => $this->get_attr( 'comments_prefix', $attributes ),
					'suffix'        => $this->get_attr( 'comments_suffix', $attributes ),
				);

			}

			$unset = array(
				'date_enabled',
				'date_selected_icon',
				'date_prefix',
				'date_suffix',
				'author_enabled',
				'author_selected_icon',
				'author_prefix',
				'author_suffix',
				'comments_enabled',
				'comments_selected_icon',
				'comments_prefix',
				'comments_suffix',
			);


			foreach ( $unset as $key ) {
				if ( isset( $attributes[ $key ] ) ) {
					unset( $attributes[ $key ] );
				}
			}

			return $attributes;
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return array(
				'date_enabled' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'date_selected_icon' => array(
					'type' => 'number',
				),
				'date_selected_icon_url' => array(
					'type' => 'string',
					'default' => '',
				),
				'date_prefix' => array(
					'type' => 'string',
					'default' => '',
				),
				'date_suffix' => array(
					'type' => 'string',
					'default' => '',
				),
				'date_format' => array(
					'type' => 'string',
					'default' => 'F-j-Y',
				),
				'date_link' => array(
					'type' => 'string',
					'default' => 'archive',
				),
				'author_enabled' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'author_selected_icon' => array(
					'type' => 'number',
				),
				'author_selected_icon_url' => array(
					'type' => 'string',
					'default' => '',
				),
				'author_prefix' => array(
					'type' => 'string',
					'default' => '',
				),
				'author_suffix' => array(
					'type' => 'string',
					'default' => '',
				),
				'author_link' => array(
					'type' => 'string',
					'default' => 'archive',
				),
				'comments_enabled' => array(
					'type' => 'boolean',
					'default' => true,
				),
				'comments_selected_icon' => array(
					'type' => 'number',
				),
				'comments_selected_icon_url' => array(
					'type' => 'string',
					'default' => '',
				),
				'comments_prefix' => array(
					'type' => 'string',
					'default' => '',
				),
				'comments_suffix' => array(
					'type' => 'string',
					'default' => '',
				),
				'comments_link' => array(
					'type' => 'string',
					'default' => 'single',
				),
				'zero_comments_format' => array(
					'type' => 'string',
					'default' => '0',
				),
				'one_comment_format' => array(
					'type' => 'string',
					'default' => '1',
				),
				'more_comments_format' => array(
					'type' => 'string',
					'default' => '%',
				),
				'layout' => array(
					'type' => 'string',
					'default' => 'inline',
				),
			);
		}

	}

}