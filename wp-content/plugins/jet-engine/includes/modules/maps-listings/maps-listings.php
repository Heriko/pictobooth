<?php
/**
 * User profile builder module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Maps_Listings' ) ) {

	/**
	 * Define Jet_Engine_Module_Maps_Listings class
	 */
	class Jet_Engine_Module_Maps_Listings extends Jet_Engine_Module_Base {

		public $instance = null;

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'maps-listings';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Maps Listings', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {
			add_action( 'jet-engine/init', array( $this, 'create_instance' ) );
		}

		/**
		 * Create module instance
		 *
		 * @return [type] [description]
		 */
		public function create_instance( $jet_engine ) {
			require $jet_engine->modules->modules_path( 'maps-listings/inc/module.php' );
			$this->instance = \Jet_Engine\Modules\Maps_Listings\Module::instance();
		}

	}

}
