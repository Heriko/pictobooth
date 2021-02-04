<?php
/**
 * Popup compatibility package
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Smart_Filters_Package' ) ) {

	/**
	 * Define Jet_Engine_Smart_Filters_Package class
	 */
	class Jet_Engine_Smart_Filters_Package {

		public function __construct() {
			add_filter(
				'jet-smart-filters/providers/jet-engine/stored-settings',
				array( $this, 'store_layout_settings' ),
				10,
				2
			);

			add_filter( 'jet-engine/ajax/get_listing/response', array( $this, 'add_to_response_filters_data' ), 10, 2 );

			add_filter( 'jet-engine/listing/grid/is_lazy_load', array( $this, 'maybe_disable_lazy_load_listing' ), 10, 2 );
		}

		/**
		 * Add the filters data to response data.
		 *
		 * @param array $response
		 * @param array $widget_settings
		 *
		 * @return array
		 */
		public function add_to_response_filters_data( $response, $widget_settings ) {

			if ( empty( $widget_settings['lazy_load'] ) ) {
				return $response;
			}

			if ( empty( $widget_settings['_element_id'] ) ) {
				$query_id = 'default';
			} else {
				$query_id = $widget_settings['_element_id'];
			}

			$filters_data = array();

			$filters_settings = array(
				'queries'   => jet_smart_filters()->query->get_default_queries(),
				'settings'  => jet_smart_filters()->providers->get_provider_settings(),
				'props'     => jet_smart_filters()->query->get_query_props(),
			);

			foreach ( $filters_settings as $param => $data ) {
				if ( ! empty( $data['jet-engine'][ $query_id ] ) ) {
					$filters_data[ $param ][ $query_id ] = $data['jet-engine'][ $query_id ];
				}
			}

			if ( ! empty( $filters_data ) ) {
				$response['filters_data'] = $filters_data;
			}

			if ( jet_smart_filters()->indexer->data ) {
				jet_smart_filters()->indexer->data->setup_queries_from_request();
				$response['indexer_data'] = jet_smart_filters()->indexer->data->prepare_provider_counts();
			}

			return $response;
		}

		/**
		 * Disable lazy loading if reload type filters are applied
		 *
		 * @param bool  $is_lazy_load
		 * @param array $settings
		 *
		 * @return bool
		 */
		public function maybe_disable_lazy_load_listing( $is_lazy_load, $settings ) {

			if ( ! $is_lazy_load ) {
				return $is_lazy_load;
			}

			if ( empty( $_REQUEST['jet-smart-filters'] ) ) {
				return $is_lazy_load;
			}

			$data     = explode( ';', $_REQUEST['jet-smart-filters'] );
			$provider = ! empty( $data[0] ) ? $data[0] : false;
			$query_id = empty( $settings['_element_id'] ) ? 'default' : $settings['_element_id'];

			if ( ! $provider || 'jet-engine/' . $query_id !== $provider ) {
				return $is_lazy_load;
			}

			return false;
		}

		/**
		 * Store additional settings
		 *
		 * @param  [type] $stored_settings [description]
		 * @param  [type] $widget_settings [description]
		 * @return [type]                  [description]
		 */
		public function store_layout_settings( $stored_settings, $widget_settings ) {

			$settings_to_store = array(
				'inject_alternative_items',
				'injection_items',
				'use_load_more',
				'load_more_id',
			);

			foreach ( $settings_to_store as $setting ) {
				if ( isset( $widget_settings[ $setting ] ) )  {
					$stored_settings[ $setting ] = $widget_settings[ $setting ];
				}
			}

			return $stored_settings;
		}

	}

}

new Jet_Engine_Smart_Filters_Package();
