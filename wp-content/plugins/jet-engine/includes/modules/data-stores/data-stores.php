<?php
/**
 * Dynamic vistibility conditions module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Data_Stores' ) ) {

	/**
	 * Define Jet_Engine_Module_Data_Stores class
	 */
	class Jet_Engine_Module_Data_Stores extends Jet_Engine_Module_Base {

		public $instance = null;

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'data-stores';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Data Stores', 'jet-engine' );
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
			require $jet_engine->modules->modules_path( 'data-stores/inc/module.php' );
			$this->instance = \Jet_Engine\Modules\Data_Stores\Module::instance();
		}

	}

}
