<?php
/**
 * Base class for listing renderers
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Render_Base' ) ) {

	abstract class Jet_Engine_Render_Base {

		private $settings = null;

		public function __construct( $settings = array() ) {
			$this->settings = $this->get_parsed_settings( $settings );
		}

		public function get_settings( $setting = null ) {
			if ( $setting ) {
				return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : false;
			} else {
				return $this->settings;
			}
		}

		/**
		 * Returns parsed settings
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function get_parsed_settings( $settings = array() ) {
			$defaults = $this->default_settings();
			$settings = wp_parse_args( $settings, $defaults );

			foreach ( $defaults as $key => $default_value ) {
				if ( null === $settings[ $key ] ) {
					$settings[ $key ] = $default_value;
				}
			}

			return $settings;
		}

		/**
		 * Returns plugin default settings
		 *
		 * @return array
		 */
		public function default_settings() {
			return array();
		}

		/**
		 * Returns required settings
		 *
		 * @return array
		 */
		public function get_required_settings() {
			$required = array();
			$settings = $this->get_settings();
			$default  = $this->default_settings();

			foreach ( $default as $key => $value ) {
				if ( isset( $settings[ $key ] ) ) {
					$required[ $key ] = $settings[ $key ];
				}
			}

			return $required;
		}

		public function get( $setting = null, $default = false ) {
			if ( isset( $this->settings[ $setting ] ) ) {
				return $this->settings[ $setting ];
			} else {
				$defaults = $this->default_settings();
				return isset( $defaults[ $setting ] ) ? $defaults[ $setting ] : $default;
			}
		}

		public function get_content() {
			ob_start();
			$this->render();
			return ob_get_clean();
		}

		/**
		 * Setup listing
		 * @param  [type] $listing_settings [description]
		 * @param  [type] $object_id        [description]
		 * @return [type]                   [description]
		 */
		public function setup_listing( $listing_settings, $object_id, $glob = false ) {

			jet_engine()->listings->data->set_listing( jet_engine()->listings->get_new_doc( $listing_settings ) );
			$source = ! empty( $listing_settings['listing_source'] ) ? $listing_settings['listing_source'] : 'posts';

			switch ( $source ) {

				case 'posts':
				case 'repeater':

					if ( $glob ) {
						global $post;
						$post = get_post( $object_id );
						setup_postdata( $post );
						$object = $post;
					} else {
						$object = get_post( $object_id );
					}

					break;

				case 'terms':
					$tax    = ! empty( $listing_settings['listing_tax'] ) ? $listing_settings['listing_tax'] : '';
					$object = get_term( $object_id, $tax );

					break;

				case 'users':
					$object = get_user_by( 'ID', $object_id );
					break;

				default:

					$object = apply_filters(
						'jet-engine/listing/render/object/' . $source,
						false,
						$object_id,
						$listing_settings,
						$this
					);

					break;

			}

			jet_engine()->listings->data->set_current_object( $object );

		}

		abstract public function get_name();

		/**
		 * Render listing item content
		 *
		 * @return [type] [description]
		 */
		abstract public function render();

	}

}
