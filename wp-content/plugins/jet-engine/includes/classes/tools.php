<?php
/**
 * Tools class
 */

class Jet_Engine_Tools {

	/**
	 * Process
	 * @param  [type] $filename [description]
	 * @param  string $file     [description]
	 * @return [type]           [description]
	 */
	public static function file_download( $filename = null, $file = '', $type = 'application/json' ) {

		set_time_limit( 0 );

		@session_write_close();

		if( function_exists( 'apache_setenv' ) ) {
			@apache_setenv('no-gzip', 1);
		}

		@ini_set( 'zlib.output_compression', 'Off' );

		nocache_headers();

		header( "Robots: none" );
		header( "Content-Type: " . $type );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );
		header( "Content-Transfer-Encoding: binary" );

		// Set the file size header
		header( "Content-Length: " . strlen( $file ) );

		echo $file;
		die();

	}

	/**
	 * Returns all post types list to use in JS components
	 *
	 * @return [type] [description]
	 */
	public static function get_post_types_for_js( $placeholder = false ) {

		$post_types = get_post_types( array(), 'objects' );
		$types_list = self::prepare_list_for_js( $post_types, 'name', 'label' );

		if ( $placeholder && is_array( $placeholder ) ) {
			$types_list = array_merge( array( $placeholder ), $types_list );
		}

		return $types_list;
	}

	/**
	 * Return all taxonomies list to use in JS components
	 *
	 * @return [type] [description]
	 */
	public static function get_taxonomies_for_js() {
		$taxonomies = get_taxonomies( array(), 'objects' );
		return self::prepare_list_for_js( $taxonomies, 'name', 'label' );
	}

	/**
	 * Returns all registeredroles for JS
	 */
	public static function get_user_roles_for_js() {

		$roles = self::get_user_roles();
		$result = array();

		foreach ( $roles as $role => $label ) {
			$result[] = array(
				'value' => $role,
				'label' => $label,
			);
		}

		return $result;

	}

	/**
	 * Returns all registered user roles
	 *
	 * @return [type] [description]
	 */
	public static function get_user_roles() {

		if ( ! function_exists( 'get_editable_roles' ) ) {
			return array();
		} else {
			$roles  = get_editable_roles();
			$result = array();

			foreach ( $roles as $role => $data ) {
				$result[ $role ] = $data['name'];
			}

			return $result;
		}

	}

	/**
	 * Prepare passed array for using in JS options
	 *
	 * @return [type] [description]
	 */
	public static function prepare_list_for_js( $array = array(), $value_key = null, $label_key = null ) {

		$result = array();

		if ( ! is_array( $array ) || empty( $array ) ) {
			return $result;
		}

		foreach ( $array as $item ) {

			$value = null;
			$label = null;

			if ( is_object( $item ) ) {
				$value = $item->$value_key;
				$label = $item->$label_key;
			} elseif ( is_array( $item ) ) {
				$value = $item[ $value_key ];
				$label = $item[ $label_key ];
			} else {
				$value = $item;
				$label = $item;
			}

			$result[] = array(
				'value' => $value,
				'label' => $label,
			);
		}

		return $result;

	}

	/**
	 * Render new elementor icons
	 *
	 * @return [type] [description]
	 */
	public static function render_icon( $icon, $icon_class, $custom_atts = array() ) {

		$custom_atts_string = '';

		if ( ! empty( $custom_atts ) ) {
			foreach ( $custom_atts as $key => $value ) {
				$custom_atts_string .= sprintf( ' %1$s="%2$s"', $key, $value );
			}
		}

		if ( ! is_array( $icon ) && is_numeric( $icon ) ) {
			ob_start();

			echo '<div class="' . $icon_class . ' is-svg-icon"' . $custom_atts_string . '>';
			echo wp_get_attachment_image( $icon, 'full' );
			echo '</div>';

			return ob_get_clean();
		}

		if ( empty( $icon['value'] ) ) {
			return false;
		}

		$is_new = class_exists( 'Elementor\Icons_Manager' ) && Elementor\Icons_Manager::is_migration_allowed();

		if ( $is_new ) {
			ob_start();

			if ( 'svg' === $icon['library'] ) {
				echo '<div class="' . $icon_class . ' is-svg-icon"' . $custom_atts_string . '>';
			}

			$custom_atts['class'] = $icon_class;
			$custom_atts['aria-hidden'] = 'true';

			Elementor\Icons_Manager::render_icon( $icon, $custom_atts );

			if ( 'svg' === $icon['library'] ) {
				echo '</div>';
			}

			return ob_get_clean();

		} else {
			return false;
		}

	}

	/**
	 * Get html attributes string.
	 *
	 * @param  array $attrs
	 * @return string
	 */
	public static function get_attr_string( $attrs ) {
		$result_array = array();

		foreach ( $attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = join( ' ', $value );
			}

			$result_array[] = sprintf( '%1$s="%2$s"', $key, esc_attr( $value ) );
		}

		return join( ' ', $result_array );
	}

	/**
	 * Check if is valid timestamp
	 *
	 * @param  mixed $timestamp
	 * @return boolean
	 */
	public static function is_valid_timestamp( $timestamp ) {
		return ( ( string ) ( int ) $timestamp === $timestamp || ( int ) $timestamp === $timestamp )
			&& ( $timestamp <= PHP_INT_MAX )
			&& ( $timestamp >= ~PHP_INT_MAX );
	}

	/**
	 * Checks a value for being empty.
	 *
	 * @param  mixed $source
	 * @return bool
	 */
	public static function is_empty( $source ) {
		return empty( $source ) && '0' !== $source;
	}

}
