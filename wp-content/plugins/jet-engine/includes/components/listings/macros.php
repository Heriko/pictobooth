<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Listings_Macros' ) ) {

	/**
	 * Define Jet_Engine_Listings_Macros class
	 */
	class Jet_Engine_Listings_Macros {

		/**
		 * Return available macros list
		 *
		 * @return [type] [description]
		 */
		public function get_all() {
			return apply_filters( 'jet-engine/listings/macros-list', array(
				'title'                    => array( $this, 'get_title' ),
				'field_value'              => array( $this, 'get_field_value' ),
				'current_id'               => array( $this, 'get_current_id' ),
				'current_tags'             => array( $this, 'get_current_tags' ),
				'current_terms'            => array( $this, 'get_current_terms' ),
				'current_categories'       => array( $this, 'get_current_categories' ),
				'current_meta'             => array( $this, 'get_current_meta' ),
				'current_meta_string'      => array( $this, 'get_current_meta_string' ),
				'related_parents_from'     => array( $this, 'get_related_parents' ),
				'related_children_from'    => array( $this, 'get_related_children' ),
				'related_children_between' => array( $this, 'get_related_children_between' ),
				'queried_term'             => array( $this, 'get_queried_term' ),
				'author_id'                => array( $this, 'get_post_author_id' ),
				'queried_user_id'          => array( $this, 'get_queried_user_id' ),
			) );
		}

		/**
		 * Return verbosed macros list
		 *
		 * @return [type] [description]
		 */
		public function verbose_macros_list() {

			$macros = $this->get_all();
			$result = '';
			$sep    = '';

			foreach ( $macros as $key => $data ) {
				$result .= $sep . '%' . $key . '%';
				$sep     = ', ';
			}

			return $result;

		}

		/**
		 * Returns queried term
		 *
		 * @return [type] [description]
		 */
		public function get_queried_term() {

			$current_object = jet_engine()->listings->data->get_current_object();

			if ( $current_object && 'WP_Term' === get_class( $current_object ) ) {
				return $current_object->term_id;
			} else {
				$queried_object = get_queried_object();

				if ( $queried_object && 'WP_Term' === get_class( $queried_object ) ) {
					return $queried_object->term_id;
				} else {
					return null;
				}

			}

			return null;

		}

		/**
		 * Returns ID of current post author
		 *
		 * @return [type] [description]
		 */
		public function get_post_author_id() {
			return get_the_author_meta( 'ID' );
		}

		/**
		 * Returns ID of the queried user
		 */
		public function get_queried_user_id() {
			
			$user = jet_engine()->listings->data->get_queried_user_object();

			if ( ! $user ) {
				return false;
			} else {
				return $user->ID;
			}

		}

		/**
		 * Can be used for meta query. Returns values of passed mata key for current post/term.
		 *
		 * @param  mixed  $field_value Field value.
		 * @param  string $meta_key    Metafield to get value from.
		 * @return mixed
		 */
		public function get_current_meta( $field_value = null, $meta_key = null ) {

			if ( ! $meta_key && ! empty( $field_value ) ) {
				$meta_key = $field_value;
			}

			if ( ! $meta_key ) {
				return '';
			}

			$object = jet_engine()->listings->data->get_current_object();

			if ( ! $object ) {
				return '';
			}

			$class  = get_class( $object );
			$result = '';

			switch ( $class ) {

				case 'WP_Post':
					return get_post_meta( $object->ID, $meta_key, true );

				case 'WP_Term':
					return get_term_meta( $object->term_id, $meta_key, true );

			}

		}

		/**
		 * Returns current meta value. For arrays implode it to coma separated string
		 *
		 * @return [type] [description]
		 */
		public function get_current_meta_string( $field_value = null, $meta_key = null ) {
			$meta = $this->get_current_meta( $field_value, $meta_key );
			return is_array( $meta ) ? implode( ', ', $meta ) : $meta;
		}

		/**
		 * Get current object ID
		 *
		 * @param  mixed  $field_value Field value.
		 * @return string
		 */
		public function get_current_id( $field_value = null ) {

			$object = jet_engine()->listings->data->get_current_object();

			if ( ! $object ) {
				return $field_value;
			}

			$class  = get_class( $object );
			$result = '';

			switch ( $class ) {
				case 'WP_Post':
					$result = $object->ID;
					break;

				case 'WP_Term':
					$result = $object->term_id;
					break;

				default:
					$result = apply_filters( 'jet-engine/listings/macros/current-id', $result, $object );
					break;
			}

			return $result;

		}

		/**
		 * Get current object title
		 *
		 * @return string
		 */
		public function get_title( $field_value = null ) {

			$object = jet_engine()->listings->data->get_current_object();

			if ( ! $object ) {
				return '';
			}

			$class  = get_class( $object );
			$result = '';

			switch ( $class ) {
				case 'WP_Post':
					$result = $object->post_title;
					break;

				case 'WP_Term':
					$result = $object->name;
					break;
			}

			return $result;

		}

		/**
		 * Returns comma-separated terms list of passed taxonomy assosiated with current post.
		 *
		 * @param  mixed  $field_value Field value.
		 * @param  string $taxonomy    Taxonomy name.
		 * @return string
		 */
		public function get_current_terms( $field_value, $taxonomy = null ) {

			if ( ! $taxonomy && ! empty( $field_value ) ) {
				$taxonomy = $field_value;
			}

			if ( ! $taxonomy ) {
				return '';
			}

			$object = jet_engine()->listings->data->get_current_object();
			$class  = get_class( $object );

			if ( 'WP_Post' !== $class ) {
				return '';
			}

			$terms = wp_get_post_terms( $object->ID, $taxonomy, array( 'fields' => 'ids' ) );

			if ( empty( $terms ) ) {
				return '';
			}

			return implode( ',', $terms );

		}

		/**
		 * Returns comma-separated tags list assosiated with current post.
		 *
		 * @return string
		 */
		public function get_current_tags() {

			$object = jet_engine()->listings->data->get_current_object();
			$class  = get_class( $object );

			if ( 'WP_Post' !== $class ) {
				return '';
			}

			$tags = wp_get_post_tags( $object->ID, array( 'fields' => 'ids' ) );

			if ( empty( $tags ) ) {
				return '';
			}

			return implode( ',', $tags );

		}

		/**
		 * Returns related post IDs
		 * @return [type] [description]
		 */
		public function get_related_parents( $value, $post_type ) {

			$posts = jet_engine()->relations->get_related_posts( array(
				'post_type_1' => $post_type,
				'post_type_2' => get_post_type(),
				'from'        => $post_type,
			) );

			if ( empty( $posts ) ) {
				return 'not-found';
			}

			if ( is_array( $posts ) ) {
				return implode( ',', $posts );
			} else {
				return $posts;
			}

		}

		/**
		 * Returns related post IDs
		 * @return [type] [description]
		 */
		public function get_related_children( $value, $post_type ) {

			$posts = jet_engine()->relations->get_related_posts( array(
				'post_type_1' => get_post_type(),
				'post_type_2' => $post_type,
				'from'        => $post_type,
			) );

			if ( empty( $posts ) ) {
				return 'not-found';
			}

			if ( is_array( $posts ) ) {
				return implode( ',', $posts );
			} else {
				return $posts;
			}

		}

		/**
		 * Returns related post IDs
		 * @return [type] [description]
		 */
		public function get_related_children_between( $value, $post_types ) {

			$post_types = explode( '|', $post_types );

			$posts = jet_engine()->relations->get_related_posts( array(
				'post_type_1' => $post_types[0],
				'post_type_2' => $post_types[1],
				'from'        => $post_types[1],
			) );

			if ( empty( $posts ) ) {
				return 'not-found';
			}

			if ( is_array( $posts ) ) {
				return implode( ',', $posts );
			} else {
				return $posts;
			}

		}

		/**
		 * Returns comma-separated categories list assosiated with current post.
		 *
		 * @return string
		 */
		public function get_current_categories() {

			$object = jet_engine()->listings->data->get_current_object();
			$class  = get_class( $object );

			if ( 'WP_Post' !== $class ) {
				return '';
			}

			$cats = wp_get_post_categories( $object->ID, array( 'fields' => 'ids' ) );

			if ( empty( $cats ) ) {
				return '';
			}

			return implode( ',', $cats );

		}

		/**
		 * Returns current field value
		 *
		 * @param  [type] $field_value [description]
		 * @return [type]              [description]
		 */
		public function get_field_value( $field_value = null ) {
			return $field_value;
		}

		/**
		 * Do macros inside string
		 *
		 * @param  [type] $string      [description]
		 * @param  [type] $field_value [description]
		 * @return [type]              [description]
		 */
		public function do_macros( $string, $field_value = null ) {

			$macros = $this->get_all();

			return preg_replace_callback(
				'/%([a-z_-]+)(\|[a-zA-Z0-9_\-|]+)?%/',
				function( $matches ) use ( $macros, $field_value ) {

					$found = $matches[1];

					if ( ! isset( $macros[ $found ] ) ) {
						return $matches[0];
					}

					$cb = $macros[ $found ];

					if ( ! is_callable( $cb ) ) {
						return $matches[0];
					}

					$args = isset( $matches[2] ) ? ltrim( $matches[2], '|' ) : false;

					return call_user_func( $cb, $field_value, $args );

				}, $string
			);

		}

	}

}
