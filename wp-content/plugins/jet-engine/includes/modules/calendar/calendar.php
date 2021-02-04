<?php
/**
 * Calendar widget module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Calendar' ) ) {

	/**
	 * Define Jet_Engine_Module_Calendar class
	 */
	class Jet_Engine_Module_Calendar extends Jet_Engine_Module_Base {

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'calendar';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Calendar', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_calendar_widget' ), 20 );
			add_action( 'wp_ajax_jet_engine_calendar_get_month', array( $this, 'calendar_get_month' ) );
			add_action( 'wp_ajax_nopriv_jet_engine_calendar_get_month', array( $this, 'calendar_get_month' ) );
		}

		/**
		 * Ajax handler for months navigation
		 *
		 * @return [type] [description]
		 */
		public function calendar_get_month() {

			ob_start();

			add_filter( 'jet-engine/listing/grid/custom-settings', array( $this, 'add_settings' ) );

			if ( ! class_exists( 'Elementor\Jet_Listing_Grid_Widget' ) ) {
				require_once jet_engine()->plugin_path( 'includes/components/elementor-views/static-widgets/grid.php' );
			}

			if ( ! class_exists( 'Elementor\Jet_Listing_Calendar_Widget' ) ) {
				require_once jet_engine()->modules->modules_path( 'calendar/widget.php' );
			}

			$current_post = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : false;

			if ( $current_post ) {
				global $post;
				$post = get_post( $current_post );
			}

			Elementor\Plugin::instance()->frontend->start_excerpt_flag( null );

			$widget = new Elementor\Jet_Listing_Calendar_Widget();
			$widget->render_posts();

			wp_send_json_success( array(
				'content' => ob_get_clean(),
			) );

		}

		/**
		 * Add custom settings for AJAX request
		 */
		public function add_settings( $settings ) {
			return isset( $_REQUEST['settings'] ) ? $_REQUEST['settings'] : array();
		}

		/**
		 * Register calendar widget
		 *
		 * @return void
		 */
		public function register_calendar_widget( $widgets_manager ) {

			if ( jet_engine()->elementor_views ) {
				jet_engine()->elementor_views->register_widget(
					jet_engine()->modules->modules_path( 'calendar/widget.php' ),
					$widgets_manager,
					'Elementor\Jet_Listing_Calendar_Widget'
				);
			}

		}

		/**
		 * Get render instance.
		 *
		 * @param  array $settings
		 * @return Jet_Listing_Render_Calendar
		 */
		public function get_render_instance( $settings ) {

			if ( ! class_exists( 'Jet_Engine_Render_Base' ) ) {
				require jet_engine()->plugin_path( 'includes/components/listings/render/base.php' );
			}

			if ( ! class_exists( 'Jet_Engine_Render_Listing_Grid' ) ) {
				require jet_engine()->plugin_path( 'includes/components/listings/render/listing-grid.php' );
			}

			if ( ! class_exists( 'Jet_Listing_Render_Calendar' ) ) {
				require jet_engine()->modules->modules_path( 'calendar/render.php' );
			}

			return new Jet_Listing_Render_Calendar( $settings );
		}

	}

}
