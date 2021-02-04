<?php
/**
 * Dynamic vistibility conditions module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Dynamic_Visibility' ) ) {

	/**
	 * Define Jet_Engine_Module_Dynamic_Visibility class
	 */
	class Jet_Engine_Module_Dynamic_Visibility extends Jet_Engine_Module_Base {

		public $instance = null;

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'dynamic-visibility';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Dynamic Visibility for Widgets and Sections', 'jet-engine' );
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
			require $jet_engine->modules->modules_path( 'dynamic-visibility/inc/module.php' );
			$this->instance = \Jet_Engine\Modules\Dynamic_Visibility\Module::instance();
		}

	}

}
