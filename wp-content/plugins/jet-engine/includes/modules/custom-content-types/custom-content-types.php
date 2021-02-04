<?php
/**
 * Custom content types module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Custom_Content_Types' ) ) {

	/**
	 * Define Jet_Engine_Module_Custom_Content_Types class
	 */
	class Jet_Engine_Module_Custom_Content_Types extends Jet_Engine_Module_Base {

		public $instance = null;

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'custom-content-types';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Custom Content Types', 'jet-engine' );
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
			require $jet_engine->modules->modules_path( 'custom-content-types/inc/module.php' );
			$this->instance = \Jet_Engine\Modules\Custom_Content_Types\Module::instance();
		}

	}

}
