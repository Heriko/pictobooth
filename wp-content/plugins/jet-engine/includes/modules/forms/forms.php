<?php
/**
 * Booking form module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Booking_Forms' ) ) {

	/**
	 * Define Jet_Engine_Module_Booking_Forms class
	 */
	class Jet_Engine_Module_Booking_Forms extends Jet_Engine_Module_Base {

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'booking-forms';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Forms', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {
			add_action( 'jet-engine/init', array( $this, 'create_instances' ) );
		}

		/**
		 * Create required instances
		 *
		 * @param  [type] $jet_engine [description]
		 * @return [type]             [description]
		 */
		public function create_instances( $jet_engine ) {

			require $jet_engine->modules->modules_path( 'forms/manager.php' );
			$jet_engine->forms = new Jet_Engine_Booking_Forms();

			// For backward compatibility
			$jet_engine->forms->booking = $jet_engine->forms;

		}

	}

}
