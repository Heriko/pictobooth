<?php
/**
 * Listing document class
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Define Jet_Engine_Listings_Document class
 */
class Jet_Engine_Listings_Document {

	private $settings = array();

	/**
	 * Setup listing
	 * @param array $settings [description]
	 */
	public function __construct( $settings = array() ) {
		$this->settings = $settings;
	}

	/**
	 * Returns listing settings
	 *
	 * @param  string $setting [description]
	 * @return [type]          [description]
	 */
	public function get_settings( $setting = '' ) {

		if ( empty( $this->settings ) ) {
			return;
		}

		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : false;

	}

}
