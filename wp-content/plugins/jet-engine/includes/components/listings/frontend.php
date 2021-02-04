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

if ( ! class_exists( 'Jet_Engine_Frontend' ) ) {

	/**
	 * Define Jet_Engine_Frontend class
	 */
	class Jet_Engine_Frontend {

		private $listing_id = null;
		private $processed_listing_id = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			$this->register_listing_assets();
		}

		/**
		 * Register listing assets
		 *
		 * @return void
		 */
		public function register_listing_assets() {

			wp_register_style(
				'jet-engine-frontend',
				jet_engine()->plugin_url( 'assets/css/frontend.css' ),
				array(),
				jet_engine()->get_version()
			);

		}

		/**
		 * Enqueue front-end scripts
		 *
		 * @return void
		 */
		public function frontend_scripts() {

			wp_enqueue_script(
				'jet-engine-frontend',
				jet_engine()->plugin_url( 'assets/js/frontend.js' ),
				array( 'jquery' ),
				jet_engine()->get_version(),
				true
			);

			do_action( 'jet-engine/listings/frontend-scripts' );

			wp_localize_script( 'jet-engine-frontend', 'JetEngineSettings', array(
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			) );

		}

		/**
		 * Enqueue front-end styles
		 *
		 * @return void
		 */
		public function frontend_styles() {
			wp_enqueue_style( 'jet-engine-frontend' );
		}

		/**
		 * Preview scripts
		 *
		 * @return void
		 */
		public function preview_scripts() {

			wp_enqueue_script( 'jquery-slick' );
			wp_enqueue_script( 'imagesloaded' );
			wp_enqueue_script( 'jet-engine-frontend' );

			do_action( 'jet-engine/listings/preview-scripts' );

		}

		/**
		 * Enqueues masonry assets
		 *
		 * @return void
		 */
		public function enqueue_masonry_assets() {
			wp_enqueue_script( 'imagesloaded' );
		}

		/**
		 * Set currently processing listing ID
		 *
		 * @param string|integer $listing_id
		 */
		public function set_listing( $listing_id = null ) {
			$this->listing_id = $listing_id;
		}

		/**
		 * Unset information about current listing
		 *
		 * @return void
		 */
		public function reset_listing() {
			$this->reset_data();
			$this->listing_id = null;
		}

		/**
		 * Get listing item content
		 *
		 * @param  $post
		 * @return string
		 */
		public function get_listing_item( $post ) {

			$this->setup_data( $post );

			$listing_id = apply_filters( 'jet-engine/listings/frontend/rendered-listing-id', $this->listing_id );

			if ( jet_engine()->blocks_views && jet_engine()->blocks_views->is_blocks_listing( $listing_id ) ) {
				return jet_engine()->blocks_views->render->get_listing_content( $listing_id );
			} else {
				return jet_engine()->elementor_views->frontend->get_listing_content( $listing_id );
			}

		}

		/**
		 * Setup data
		 *
		 * @param $post_obj
		 */
		public function setup_data( $post_obj = null ) {

			if ( 'posts' === jet_engine()->listings->data->get_listing_source() ) {
				global $post;
				$post = $post_obj;
				setup_postdata( $post );
			}

			jet_engine()->listings->data->set_current_object( $post_obj );

		}

		/**
		 * Reset data
		 *
		 * @return void
		 */
		public function reset_data() {

			do_action( 'jet-engine/listings/frontend/reset-data', jet_engine()->listings->data, $this );

			if ( 'posts' === jet_engine()->listings->data->get_listing_source() ) {
				wp_reset_postdata();
			}

			jet_engine()->listings->data->reset_current_object();

		}

		/**
		 * Get custom action url.
		 *
		 * @param string $action
		 * @param array  $args
		 *
		 * @return string
		 */
		public function get_custom_action_url( $action = '', $args = array() ) {
			$default_args = array(
				'action' => $action,
				'event'  => 'click',
			);

			$query_args = array_merge( $default_args, $args );

			return sprintf( '#jet-engine-action&%s', build_query( $query_args ) );
		}

	}

}
