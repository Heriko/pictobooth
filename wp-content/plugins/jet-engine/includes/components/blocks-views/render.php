<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Render' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Render class
	 */
	class Jet_Engine_Blocks_Views_Render {

		private $contents = array();
		private $enqueued_css = array();

		public function __construct() {
			add_action( 'enqueue_block_assets', array( jet_engine()->frontend, 'frontend_styles' ) );

			add_action( 'wp_footer', array( $this, 'print_css' ) );
			add_action( 'jet-engine/listing/grid/after', array( $this, 'print_preview_css' ) );
		}

		/**
		 * Print preview CSS
		 *
		 * @return [type] [description]
		 */
		public function print_preview_css() {
			$this->print_css();
		}

		public function print_css() {
			foreach ( $this->enqueued_css as $css ) {
				if ( ! empty( $css ) ) {
					echo $css;
				}
			}
		}

		/**
		 * Returns listing content for given listing ID
		 *
		 * @return [type] [description]
		 */
		public function get_listing_content( $listing_id ) {
			$content = $this->get_raw_content( $listing_id );
			$this->enqueue_listing_css( $listing_id );
			return $this->parse_content( $content );
		}

		/**
		 * Prse listing item content
		 *
		 * @param  [type] $content [description]
		 * @return [type]          [description]
		 */
		public function parse_content( $content ) {

			return preg_replace_callback(
				'/<!--\s+(?P<closer>\/)?wp:(?P<namespace>[a-z][a-z0-9_-]*\/)?(?P<name>[a-z][a-z0-9_-]*)\s+(?P<attrs>{(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?(?P<void>\/)?-->/s',
				function( $matches ) {

					$namespace = isset( $matches['namespace'] ) ? $matches['namespace'] : false;

					if ( ! $namespace || 'jet-engine/' !== $matches['namespace'] ) {
						return $matches[0];
					}

					$name     = isset( $matches['name'] ) ? $matches['name'] : false;
					$settings = isset( $matches['attrs'] ) ? $matches['attrs'] : '';
					$settings = json_decode( $settings, true );

					if ( ! $name ) {
						return $matches[0];
					}

					if ( empty( $settings ) ) {
						$settings = array();
					}

					$render = jet_engine()->listings->get_render_instance( $name, $settings );

					if ( ! $render ) {
						return $matches[0];
					} else {
						return $render->get_content();
					}

				},
				$content
			);

		}

		public function enqueue_listing_css( $listing_id ) {

			if ( isset( $this->enqueued_css[ $listing_id ] ) ) {
				return;
			}

			$css = get_post_meta( $listing_id, '_jet_engine_listing_css', true );
			$result = '';

			if ( $css ) {
				$css    = str_replace( 'selector', '.jet-listing-grid--' . $listing_id, $css );
				$result = '<style>' . $css . '</style>';
			}

			$this->enqueued_css[ $listing_id ] = $result;

		}



		/**
		 * Returns raw listing content
		 *
		 * @param  [type] $listing_id [description]
		 * @return [type]             [description]
		 */
		public function get_raw_content( $listing_id ) {

			if ( ! isset( $this->contents[ $listing_id ] ) ) {
				$post = get_post( $listing_id );
				$this->contents[ $listing_id ] = $post->post_content;
			}

			return $this->contents[ $listing_id ];
		}

	}

}