<?php
/**
 * Listing Grid View
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views_Type_Grid' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views_Type_Listing_Grid class
	 */
	class Jet_Engine_Blocks_Views_Type_Listing_Grid extends Jet_Engine_Blocks_Views_Type_Base {

		/**
		 * Returns block name
		 *
		 * @return string
		 */
		public function get_name() {
			return 'listing-grid';
		}

		/**
		 * Return attributes array
		 *
		 * @return array
		 */
		public function get_attributes() {
			return array(
				'lisitng_id' => array(
					'type' => 'string',
					'default' => '',
				),
				'columns' => array(
					'type' => 'number',
					'default' => 3,
				),
				'columns_tablet' => array(
					'type' => 'number',
					'default' => 3,
				),
				'columns_mobile' => array(
					'type' => 'number',
					'default' => 1,
				),
				'is_archive_template' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'post_status' => array(
					'type'    => 'array',
					'items'   => array( 'type' => 'string' ),
					'default' => array( 'publish' ),
				),
				'posts_num' => array(
					'type' => 'number',
					'default' => 6,
				),
				'not_found_message' => array(
					'type' => 'string',
					'default' => __( 'No data was found', 'jet-engine' ),
				),
				'custom_posts_query' => array(
					'type' => 'string',
					'default' => '',
				),
				'hide_widget_if' => array(
					'type' => 'string',
					'default' => '',
				),
			);
		}

		public function render_callback( $attributes = array() ) {
			$item       = $this->get_name();
			$attributes = $this->prepare_attributes( $attributes );
			$render     = jet_engine()->listings->get_render_instance( $item, $attributes );
			$listing_id = $attributes['lisitng_id'];

			if ( ! $render ) {
				return __( 'Listing renderer class not found', 'jet-engine' );
			}

			ob_start();

			$render->render();

			if ( $listing_id && ! jet_engine()->blocks_views->is_blocks_listing( $listing_id )
				&& jet_engine()->has_elementor()
				&& ! wp_style_is( 'elementor-frontend', 'registered' )
			) {
				Elementor\Plugin::$instance->frontend->register_styles();
				Elementor\Plugin::$instance->frontend->enqueue_styles();
				Elementor\Plugin::$instance->frontend->print_fonts_links();

				wp_print_styles( 'elementor-frontend' );

				$css_file = Elementor\Core\Files\CSS\Post::create( $listing_id );
				$css_file->print_css();
			}

			$content = ob_get_clean();

			return $content;
		}

	}

}