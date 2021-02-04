<?php
namespace Jet_Engine\Modules\Maps_Listings;

class Lat_Lng {

	public $geo_api_url  = 'https://maps.googleapis.com/maps/api/geocode/json';
	public $meta_key     = '_jet_maps_coord';
	public $field_groups = array();
	public $done         =  false;
	public $failures     = array();

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		$this->hook_preload();
	}

	/**
	 * Hook meta-fields preloading
	 *
	 * @return [type] [description]
	 */
	public function hook_preload() {

		$preload = Module::instance()->settings->get( 'enable_preload_meta' );

		if ( ! $preload ) {
			return;
		}

		$preload_fields = Module::instance()->settings->get( 'preload_meta' );

		if ( empty( $preload_fields ) ) {
			return;
		}

		$preload_fields = explode( ',', $preload_fields );

		foreach ( $preload_fields as $field ) {
			$field = trim( $field );
			$fields = explode( '+', $field );

			if ( 1 === count( $fields ) ) {
				add_action( 'cx_post_meta/before_save_meta/' . $field, array( $this, 'preload' ), 10, 2 );
			} else {
				$this->field_groups[] = $fields;
			}

		}

		if ( ! empty( $this->field_groups ) ) {
			add_action( 'cx_post_meta/after_save', array( $this, 'preload_groups' ) );
		}

	}

	/**
	 * Get address sring from post_id and field names array
	 */
	public function get_address_from_fields_group( $post_id, $fields = array() ) {

		$group = array();

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( ! empty( $_POST[ $field ] ) ) {
				$group[] = $_POST[ $field ];
			} else {
				$group[] = get_post_meta( $post_id, $field, true );
			}
		}

		$group = array_filter( $group );

		if ( empty( $group ) ) {
			return false;
		} else {
			return implode( ', ', $group );
		}

	}

	/**
	 * Preload fields groups
	 */
	public function preload_groups( $post_id ) {

		if ( $this->done ) {
			return;
		}

		foreach ( $this->field_groups as $fields ) {

			$address = $this->get_address_from_fields_group( $post_id, $fields );

			if ( ! $address ) {
				continue;
			}

			$coord = $this->get( $post_id, $address );

		}

		$this->done = true;

	}

	/**
	 * Preload field address
	 *
	 * @param  [type] $post_id [description]
	 * @param  [type] $address [description]
	 * @return [type]          [description]
	 */
	public function preload( $post_id, $address ) {

		if ( empty( $address ) ) {
			return;
		}

		$coord = $this->get( $post_id, $address );

	}

	/**
	 * Returns remote coordinates by location
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_remote( $location ) {

		$api_key           = Module::instance()->settings->get( 'api_key' );
		$use_geocoding_key = Module::instance()->settings->get( 'use_geocoding_key' );
		$geocoding_key     = Module::instance()->settings->get( 'geocoding_key' );

		if ( $use_geocoding_key && $geocoding_key ) {
			$api_key = $geocoding_key;
		}

		// Do nothing if api key not provided
		if ( ! $api_key ) {
			return false;
		}

		// Prepare request data
		$location    = esc_attr( $location );
		$api_key     = esc_attr( $api_key );
		$request_url = add_query_arg(
			array(
				'address' => urlencode( $location ),
				'key'     => urlencode( $api_key )
			),
			esc_url( $this->geo_api_url )
		);

		$response = wp_remote_get( $request_url );
		$json     = wp_remote_retrieve_body( $response );
		$data     = json_decode( $json, true );

		$coord = isset( $data['results'][0]['geometry']['location'] )
			? $data['results'][0]['geometry']['location']
			: false;

		if ( ! $coord ) {
			return false;
		}

		return $coord;

	}

	/**
	 * Get not-post related coordinates
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_from_transient( $location ) {

		$key   = md5( $location );
		$coord = get_transient( $key );

		if ( ! $coord ) {

			$coord = $this->get_remote( $location );

			if ( $coord ) {
				set_transient( $key, $coord, WEEK_IN_SECONDS );
			}

		}

		return $coord;

	}

	/**
	 * Prints failures message
	 */
	public function failures_message() {

		if ( empty( $this->failures ) ) {
			return;
		}

		if ( 5 <= count( $this->failures ) ) {
			$message = __( 'We can`t get coordinates for multiple locations', 'jet-engine' );
		} else {

			$locations = array();

			foreach ( $this->failures as $post_id => $location ) {
				$locations[] = sprintf( '%1$s (ID #%2$s)', $location, $post_id );
			}

			$message = __( 'We can`t get coordinates for locations: ', 'jet-engine' ) . implode( ', ', $locations );

		}

		$message .= __( '. Please check your API key (you can validate it in maps settings or check in Google Console), make sure Geocoding API is enabled.', 'jet-engine' );

		return sprintf( '<div style="border: 1px solid #f00; color: #f00;  padding: 20px; margin: 10px 0;">%s</div>', $message );

	}

	public function maybe_add_offset( $coordinates = array() ) {
		
		$add_offset = Module::instance()->settings->get( 'add_offset' );

		if ( ! $add_offset ) {
			return $coordinates;
		}

		$offset_rate = apply_filters( 'jet-engine/maps-listing/offset-rate', 100000 );

		$offset_lat = ( 10 - rand( 0, 20 ) ) / $offset_rate;
		$offset_lng = ( 10 - rand( 0, 20 ) ) / $offset_rate;

		if ( isset( $coordinates['lat'] ) ) {
			$coordinates['lat'] = $coordinates['lat'] + $offset_lat;
		}

		if ( isset( $coordinates['lng'] ) ) {
			$coordinates['lng'] = $coordinates['lng'] + $offset_lng;
		}

		return $coordinates;

	}

	/**
	 * Returns lat and lang for passed address
	 *
	 * @param  [type] $address [description]
	 * @return [type]          [description]
	 */
	public function get( $post_id, $location ) {

		if ( is_array( $location ) ) {
			return $this->maybe_add_offset( $location );
		}

		$key   = md5( $location );
		$meta  = get_post_meta( $post_id, $this->meta_key, true );

		if ( ! empty( $meta ) && $key === $meta['key'] ) {
			return $this->maybe_add_offset( $meta['coord'] );
		}

		$coord = $this->get_remote( $location );

		if ( ! $coord ) {
			if ( $location ) {
				$this->failures[ $post_id ] = $location;
			}
			return false;
		}

		update_post_meta( $post_id, $this->meta_key, array(
			'key'   => $key,
			'coord' => $coord,
		) );

		return $this->maybe_add_offset( $coord );

	}

}
