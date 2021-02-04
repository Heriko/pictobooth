<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Listings_Ajax_Handlers' ) ) {

	class Jet_Engine_Listings_Ajax_Handlers {

		public function __construct() {
			add_action( 'wp_ajax_jet_engine_ajax', array( $this, 'handle_ajax' ) );
			add_action( 'wp_ajax_nopriv_jet_engine_ajax', array( $this, 'handle_ajax' ) );
		}

		/**
		 * Handle AJAX request
		 *
		 * @return [type] [description]
		 */
		public function handle_ajax() {

			if ( ! isset( $_REQUEST['handler'] ) || ! is_callable( array( $this, $_REQUEST['handler'] ) ) ) {
				return;
			}

			call_user_func( array( $this, $_REQUEST['handler'] ) );

		}

		/**
		 * Load more
		 * @return [type] [description]
		 */
		public function listing_load_more() {

			require jet_engine()->plugin_path( 'includes/components/elementor-views/ajax-handlers.php' );
			$elementor_ajax = new Jet_Engine_Elementor_Ajax_Handlers();
			$elementor_ajax->listing_load_more();

		}

		/**
		 * Get whole listing through AJAX
		 */
		public function get_listing() {

			$query           = ! empty( $_REQUEST['query'] ) ? $_REQUEST['query'] : array();
			$widget_settings = ! empty( $_REQUEST['widget_settings'] ) ? $_REQUEST['widget_settings'] : array();
			$post_id         = ! empty( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : false;
			$queried_id      = ! empty( $_REQUEST['queried_id'] ) ? absint( $_REQUEST['queried_id'] ) : false;
			$element_id      = ! empty( $_REQUEST['element_id'] ) ? $_REQUEST['element_id'] : false;
			$response        = array();

			if ( $queried_id ) {
				global $post;
				$post = get_post( $queried_id );
			}

			if ( $post_id && $element_id ) {
				$elementor = \Elementor\Plugin::instance();
				$document = $elementor->documents->get( $post_id );

				if ( $document ) {
					$widget = $this->find_element_recursive( $document->get_elements_data(), $element_id );

					if ( $widget ) {
						$widget_instance = $elementor->elements_manager->create_element_instance( $widget );
						$widget_settings = $widget_instance->get_settings_for_display();
						$_REQUEST['query'] = array();
					}

				}
			}

			$_widget_settings = $widget_settings;
			$is_lazy_load     = ! empty( $widget_settings['lazy_load'] ) ? filter_var( $widget_settings['lazy_load'], FILTER_VALIDATE_BOOLEAN ) : false;

			// Reset `lazy_load` to avoid looping.
			if ( $is_lazy_load ) {
				$widget_settings['lazy_load'] = '';
			}

			ob_start();

			$render_instance = jet_engine()->listings->get_render_instance( 'listing-grid', $widget_settings );

			if ( $is_lazy_load && $queried_id ) {
				jet_engine()->listings->data->set_current_object( get_post( $queried_id ) );
			}

			if ( $is_lazy_load && ! empty( $query ) ) { // for Archive pages
				jet_engine()->listings->data->set_listing(
					Elementor\Plugin::$instance->documents->get_doc_for_frontend( $widget_settings['lisitng_id'] )
				);

				$posts_query = new WP_Query( $query );
				$posts       = $posts_query->posts;

				$render_instance->posts_query = $posts_query;

				$render_instance->query_vars['page']    = $posts_query->get( 'paged' ) ? $posts_query->get( 'paged' ) : 1;
				$render_instance->query_vars['pages']   = $posts_query->max_num_pages;
				$render_instance->query_vars['request'] = $query;

				$render_instance->posts_template( $posts, $widget_settings );

			} else {
				$render_instance->render();
			}

			$response['html'] = ob_get_clean();

			$response = apply_filters( 'jet-engine/ajax/get_listing/response', $response, $_widget_settings );

			wp_send_json_success( $response );

		}

		public function find_element_recursive( $elements, $element_id ) {

			foreach ( $elements as $element ) {

				if ( $element_id === $element['id'] ) {
					return $element;
				}

				if ( ! empty( $element['elements'] ) ) {

					$element = $this->find_element_recursive( $element['elements'], $element_id );

					if ( $element ) {
						return $element;
					}
				}
			}

			return false;
		}

	}

}
