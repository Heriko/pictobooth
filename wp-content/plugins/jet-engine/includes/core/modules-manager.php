<?php
/**
 * Modules manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Modules' ) ) {

	/**
	 * Define Jet_Engine_Modules class
	 */
	class Jet_Engine_Modules {

		public  $option_name    = 'jet_engine_modules';
		private $modules        = array();
		private $active_modules = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {

			$this->preload_modules();
			$this->init_active_modules();

			add_action( 'wp_ajax_jet_engine_save_modules', array( $this, 'save_modules' ) );

		}

		/**
		 * Save active modules
		 *
		 * @return [type] [description]
		 */
		public function save_modules() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array(
					'message' => 'You don\'t have permissions to do this',
				) );
			}

			$reload         = false;
			$current        = get_option( $this->option_name, array() );
			$new            = isset( $_REQUEST['modules'] ) ? $_REQUEST['modules'] : array();
			$activated      = array_diff( $new, $current );
			$deactivated    = array_diff( $current, $new );
			$reload_modules = array(
				'booking-forms',
				'profile-builder',
				'maps-listings',
				'data-stores',
				'custom-content-types'
			);

			foreach ( $reload_modules as $module ) {
				if ( in_array( $module, $activated ) || in_array( $module, $deactivated ) ) {
					$reload = true;
				}
			}

			update_option( $this->option_name, $new );

			wp_send_json_success( array( 'reload' => $reload ) );

		}

		/**
		 * Activate module
		 *
		 * @return [type] [description]
		 */
		public function activate_module( $module ) {

			$modules = get_option( $this->option_name, array() );

			if ( ! in_array( $module, $modules ) ) {
				$modules[] = $module;
			}

			update_option( $this->option_name, $modules );

		}

		/**
		 * Returns path to file inside modules dir
		 *
		 * @param  [type] $path [description]
		 * @return [type]       [description]
		 */
		public function modules_path( $path ) {
			return jet_engine()->plugin_path( 'includes/modules/' . $path );
		}

		/**
		 * Preload modules
		 *
		 * @return void
		 */
		public function preload_modules() {

			$path        = jet_engine()->plugin_path( 'includes/modules/' );
			$all_modules = apply_filters( 'jet-engine/available-modules', array(
				'Jet_Engine_Module_Gallery_Grid'         => $path . 'gallery/grid.php',
				'Jet_Engine_Module_Gallery_Slider'       => $path . 'gallery/slider.php',
				'Jet_Engine_Module_QR_Code'              => $path . 'qr-code/qr-code.php',
				'Jet_Engine_Module_Calendar'             => $path . 'calendar/calendar.php',
				'Jet_Engine_Module_Booking_Forms'        => $path . 'forms/forms.php',
				'Jet_Engine_Module_Listing_Injections'   => $path . 'listing-injections/listing-injections.php',
				'Jet_Engine_Module_Profile_Builder'      => $path . 'profile-builder/profile-builder.php',
				'Jet_Engine_Module_Maps_Listings'        => $path . 'maps-listings/maps-listings.php',
				'Jet_Engine_Module_Dynamic_Visibility'   => $path . 'dynamic-visibility/dynamic-visibility.php',
				'Jet_Engine_Module_Data_Stores'          => $path . 'data-stores/data-stores.php',
				'Jet_Engine_Module_Custom_Content_Types' => $path . 'custom-content-types/custom-content-types.php',
			) );

			require_once jet_engine()->plugin_path( 'includes/base/base-module.php' );

			foreach ( $all_modules as $module => $file ) {
				require $file;
				$instance = new $module;
				$this->modules[ $instance->module_id() ] = $instance;
			}

		}

		/**
		 * Initialize active modulles
		 *
		 * @return void
		 */
		public function init_active_modules() {

			$modules = $this->get_active_modules();

			if ( empty( $modules ) ) {
				return;
			}

			/**
			 * Check if is new modules format or old
			 */
			if ( ! isset( $modules['gallery-grid'] ) ) {

				$fixed = array();

				foreach ( $modules as $module ) {
					$fixed[ $module ] = 'true';
				}

				$modules = $fixed;

			}

			foreach ( $modules as $module => $is_active ) {
				if ( 'true' === $is_active ) {
					$module_instance = isset( $this->modules[ $module ] ) ? $this->modules[ $module ] : false;
					if ( $module_instance ) {
						call_user_func( array( $module_instance, 'module_init' ) );
						$this->active_modules[] = $module;
					}
				}
			}

		}

		/**
		 * Get all modules list in format required for JS
		 *
		 * @return [type] [description]
		 */
		public function get_all_modules_for_js() {

			$result = array();

			foreach ( $this->modules as $module ) {

				$result[] = array(
					'value' => $module->module_id(),
					'label' => $module->module_name(),
				);

			}

			return $result;

		}

		/**
		 * Get all modules list
		 *
		 * @return [type] [description]
		 */
		public function get_all_modules() {
			$result = array();
			foreach ( $this->modules as $module ) {
				$result[ $module->module_id() ] = $module->module_name();
			}
			return $result;
		}

		/**
		 * Get active modules list
		 *
		 * @return [type] [description]
		 */
		public function get_active_modules() {

			$active_modules = get_option( $this->option_name, array() );

			// backward compatibility
			if ( ! empty( $active_modules ) ) {
				if ( in_array( 'true', $active_modules ) || in_array( 'false', $active_modules ) ) {
					$new_format = array();
					foreach ( $active_modules as $module => $is_active ) {
						if ( 'true' === $is_active ) {
							$new_format[] = $module;
						}
					}
					$active_modules = $new_format;
				}

			}

			return $active_modules;
		}

		/**
		 * Check if pased module is currently active
		 *
		 * @param  [type]  $module_id [description]
		 * @return boolean            [description]
		 */
		public function is_module_active( $module_id = null ) {
			return in_array( $module_id, $this->active_modules );
		}

		/**
		 * Get module instance by module ID
		 *
		 * @param  [type] $module_id [description]
		 * @return [type]            [description]
		 */
		public function get_module( $module_id = null ) {
			return isset( $this->modules[ $module_id ] ) ? $this->modules[ $module_id ] : false;
		}

	}

}
