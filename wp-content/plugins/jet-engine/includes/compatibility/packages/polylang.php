<?php
/**
 * Polylang compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Polylang_Package' ) ) {

	class Jet_Engine_Polylang_Package {

		public function __construct() {
			add_filter( 'jet-engine/listings/frontend/rendered-listing-id', array( $this, 'set_translated_listing' ) );
		}

		/**
		 * Set translated listing ID to show
		 *
		 * @param int|string $listing_id Listing ID
		 *
		 * @return false|int|null
		 */
		public function set_translated_listing( $listing_id ) {

			if ( function_exists( 'pll_get_post' ) ) {

				$translation_listing_id = pll_get_post( $listing_id );

				if ( null === $translation_listing_id ) {
					// the current language is not defined yet
					return $listing_id;
				} elseif ( false === $translation_listing_id ) {
					//no translation yet
					return $listing_id;
				} elseif ( $translation_listing_id > 0 ) {
					// return translated post id
					return $translation_listing_id;
				}
			}

			return $listing_id;
		}

	}

}

new Jet_Engine_Polylang_Package();
