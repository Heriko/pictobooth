<?php
/**
 * Base class for module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Base' ) ) {

	/**
	 * Define Jet_Engine_Module_Base class
	 */
	abstract class Jet_Engine_Module_Base {

		/**
		 * Module ID
		 *
		 * @return string
		 */
		abstract public function module_id();

		/**
		 * Module name
		 *
		 * @return string
		 */
		abstract public function module_name();

		/**
		 * Module init
		 *
		 * @return void
		 */
		abstract public function module_init();

	}

}
