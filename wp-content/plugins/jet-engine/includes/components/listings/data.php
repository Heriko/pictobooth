<?php
/**
 * Listing items data manager
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Listings_Data' ) ) {

	/**
	 * Define Jet_Engine_Listings_Data class
	 */
	class Jet_Engine_Listings_Data {

		/**
		 * Current listing object
		 *
		 * @var object
		 */
		private $current_object = null;

		/**
		 * Current listing document
		 *
		 * @var array
		 */
		private $current_listing = false;

		/**
		 * <ain listing document for current page
		 * @var null
		 */
		private $main_listing = null;

		/**
		 * Default main object holder
		 *
		 * @var mixed
		 */
		private $default_object = null;

		/**
		 * Default user object holder
		 *
		 * @var WP_User
		 */
		private $current_user = null;

		/**
		 * Processed post object
		 *
		 * @var WP_Post
		 */
		private $current_post = null;

		/**
		 * Processed term object
		 *
		 * @var WP_Term
		 */
		private $current_term = null;

		/**
		 * Listing settings defaults
		 * @var array
		 */
		private $defaults = false;

		/**
		 * Repeater iteration index
		 *
		 * @var integer
		 */
		public $repeater_index = 0;

		public $user_fields = array();

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'the_post', array( $this, 'maybe_set_current_object' ), 10, 2 );
		}

		/**
		 * Automatically setup current object for post loops started inside the page
		 *
		 * @param  [type] $post  [description]
		 * @param  [type] $query [description]
		 * @return [type]        [description]
		 */
		public function maybe_set_current_object( $post = false, $query = false ) {

			if ( ! $post ) {
				return;
			}

			if ( ! $query ) {
				$this->set_current_object( $post );
				return;
			}

			if ( ! $query->is_main_query() ) {
				$this->set_current_object( $post );
			} else {
				$current_object = $this->get_current_object();

				if ( $current_object && 'WP_Post' === get_class( $current_object ) ) {
					$this->reset_current_object();
				}

			}

		}

		/**
		 * Set current listing from outside
		 *
		 * @param void
		 */
		public function set_listing( $listing_doc = null ) {

			if ( ! $listing_doc ) {
				return;
			}

			if ( $listing_doc->get_settings( 'is_main' ) ) {
				$this->main_listing = $listing_doc;
			}

			$this->current_listing = $listing_doc;
		}

		/**
		 * Reset current listing object
		 *
		 * @return void
		 */
		public function reset_listing() {
			$this->current_listing = $this->main_listing;
			$this->reset_current_object();
		}

		/**
		 * Returns current listing object
		 *
		 * @return [type] [description]
		 */
		public function get_listing() {

			if ( ! $this->current_listing ) {
				$default_settings      = $this->setup_default_listing();
				$this->current_listing = jet_engine()->listings->get_new_doc( $default_settings );
			}

			return $this->current_listing;
		}

		/**
		 * Retuns current object fields array
		 * @return [type] [description]
		 */
		public function get_object_fields( $where = 'elementor' ) {

			switch ( $this->get_listing_source() ) {

				case 'posts':

					$fields = array(
						'post_id'      => __( 'Post ID', 'jet-engine' ),
						'post_title'   => __( 'Title', 'jet-engine' ),
						'post_date'    => __( 'Date', 'jet-engine' ),
						'post_content' => __( 'Content', 'jet-engine' ),
						'post_excerpt' => __( 'Excerpt', 'jet-engine' ),
						'post_status'  => __( 'Post Status', 'jet-engine' ),
					);

					break;

				case 'terms':

					$fields = array(
						'name'        => __( 'Term name', 'jet-engine' ),
						'description' => __( 'Term description', 'jet-engine' ),
						'count'       => __( 'Posts count', 'jet-engine' ),
					);

					break;

				case 'users':

					$fields = array(
						'ID'              => __( 'ID', 'jet-engine' ),
						'user_login'      => __( 'Login', 'jet-engine' ),
						'user_nicename'   => __( 'Nickname', 'jet-engine' ),
						'user_email'      => __( 'E-mail', 'jet-engine' ),
						'user_url'        => __( 'URL', 'jet-engine' ),
						'user_registered' => __( 'Registration Date', 'jet-engine' ),
						'display_name'    => __( 'Display Name', 'jet-engine' ),
					);

					break;
			}

			$groups = apply_filters( 'jet-engine/listing/data/object-fields-groups', array(
				array(
					'label'  => __( 'Post', 'jet-engine' ),
					'options' => array(
						'post_id'      => __( 'Post ID', 'jet-engine' ),
						'post_title'   => __( 'Title', 'jet-engine' ),
						'post_date'    => __( 'Date', 'jet-engine' ),
						'post_content' => __( 'Content', 'jet-engine' ),
						'post_excerpt' => __( 'Excerpt', 'jet-engine' ),
						'post_status'  => __( 'Post Status', 'jet-engine' ),
					)
				),
				array(
					'label'  => __( 'Term', 'jet-engine' ),
					'options' => array(
						'name'        => __( 'Term name', 'jet-engine' ),
						'description' => __( 'Term description', 'jet-engine' ),
						'count'       => __( 'Posts count', 'jet-engine' ),
					)
				),
				array(
					'label'  => __( 'User', 'jet-engine' ),
					'options' => array(
						'ID'              => __( 'ID', 'jet-engine' ),
						'user_login'      => __( 'Login', 'jet-engine' ),
						'user_nicename'   => __( 'Nickname', 'jet-engine' ),
						'user_email'      => __( 'E-mail', 'jet-engine' ),
						'user_url'        => __( 'URL', 'jet-engine' ),
						'user_registered' => __( 'Registration Date', 'jet-engine' ),
						'display_name'    => __( 'Display Name', 'jet-engine' ),
					)
				),
			) );

			if ( 'blocks' === $where ) {

				$result = array();

				foreach ( $groups as $group ) {

					$values = array();

					foreach ( $group['options'] as $key => $value ) {
						$values[] = array(
							'value' => $key,
							'label' => $value,
						);
					}

					$result[] = array(
						'label'  => $group['label'],
						'values' => $values,
					);

				}

				return $result;

			} else {
				return $groups;
			}

		}

		/**
		 * Checkl if requested property is property of user object
		 *
		 * @param  [type]  $prop [description]
		 * @return boolean       [description]
		 */
		public function is_user_prop( $prop ) {
			return in_array(
				$prop,
				array(
					'ID',
					'user_login',
					'user_nicename',
					'user_email',
					'user_url',
					'user_registered',
					'display_name',
				)
			);
		}

		/**
		 * Get listing default property
		 *
		 * @param  string $prop [description]
		 * @return [type]       [description]
		 */
		public function listing_defaults( $prop = 'listing_source' ) {

			if ( ! empty( $this->defaults ) ) {
				return isset( $this->defaults[ $prop ] ) ? $this->defaults[ $prop ] : false;
			}

			$listing = $this->get_listing();

			return isset( $listing[ $prop ] ) ? $listing[ $prop ] : false;

		}

		/**
		 * Setup default listing settings
		 *
		 * @return [type] [description]
		 */
		public function setup_default_listing() {

			$default = array(
				'listing_source'    => 'posts',
				'listing_post_type' => 'post',
				'listing_tax'       => 'category',
			);

			$default_object = $this->get_default_object();

			if ( ! $default_object ) {
				$this->defaults = $default;
				return $this->defaults;
			}

			$listing = apply_filters( 'jet-engine/listing/data/custom-listing', false, $this, $default_object );

			if ( ! $listing ) {

				if ( isset( $default_object->post_type ) ) {
					$this->defaults = array(
						'listing_source'    => 'posts',
						'listing_post_type' => $default_object->post_type,
						'listing_tax'       => 'category',
					);
				} else {
					$this->defaults = array(
						'listing_source'    => 'terms',
						'listing_post_type' => 'post',
						'listing_tax'       => $default_object->taxonomy,
					);
				}

			} else {
				$this->defaults = $listing;
			}

			return $this->defaults;

		}

		/**
		 * Returns listing source
		 *
		 * @return string
		 */
		public function get_listing_source() {
			$listing = $this->get_listing();
			return $listing->get_settings( 'listing_source' );
		}

		/**
		 * Returns post type for query
		 *
		 * @return string
		 */
		public function get_listing_post_type() {

			$listing = $this->get_listing();

			if ( ! $listing ) {

				$post_type = get_post_type();

				$blacklisted = array(
					'elementor_library',
					'jet-theme-core',
				);

				if ( $post_type && ! in_array( $post_type, $blacklisted ) ) {
					return $post_type;
				} else {
					return $this->listing_defaults( 'listing_post_type' );
				}

			} else {
				return $listing->get_settings( 'listing_post_type' );
			}
		}

		/**
		 * Returns taxonomy for query
		 *
		 * @return string
		 */
		public function get_listing_tax() {
			$listing = $this->get_listing();
			return $listing->get_settings( 'listing_tax' );
		}

		/**
		 * Set $current_object property
		 *
		 * @param object $object
		 */
		public function set_current_object( $object = null, $clear_hook = false ) {

			if ( $clear_hook ) {
				remove_action( 'the_post', array( $this, 'maybe_set_current_object' ), 10, 2 );
			}

			if ( ! $object ) {
				return;
			}

			if ( $object === $this->current_object ) {
				return;
			}

			$class = get_class( $object );

			switch ( $class ) {
				case 'WP_Post':
					$this->current_post = $object;
					break;

				case 'WP_Term':
					$this->current_term = $object;
					break;

				case 'WP_User':
					$this->current_user = $object;
					break;

			}

			$this->current_object = $object;
		}

		/**
		 * Set $current_object property
		 *
		 * @param object $object
		 */
		public function reset_current_object() {
			$this->current_object = null;
		}

		/**
		 * Returns current user object
		 *
		 * @return [type] [description]
		 */
		public function get_current_user_object() {

			if ( ! $this->current_user ) {
				$this->current_user = wp_get_current_user();
			}

			return $this->current_user;

		}

		/**
		 * Returns queried user object
		 *
		 * @return [type] [description]
		 */
		public function get_queried_user_object() {

			$user_object = false;

			if ( jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
				$profile_builder = jet_engine()->modules->get_module( 'profile-builder' );
				$user_object     = $profile_builder->instance->query->get_queried_user();
			}

			if ( ! $user_object ) {
				if ( is_author() ) {
					$user_object = get_queried_object();
				} else {
					$user_object = $this->get_current_user_object();
				}
			}

			$user_object = apply_filters( 'jet-engine/listings/data/queried-user', $user_object );

			return $user_object;

		}

		/**
		 * Returns $current_object property
		 *
		 * @return object
		 */
		public function get_current_object() {

			if ( null === $this->current_object ) {
				$this->current_object = $this->get_default_object();
			}

			return $this->current_object;

		}

		/**
		 * Returns default object
		 *
		 * @return [type] [description]
		 */
		public function get_default_object() {

			if ( null !== $this->default_object ) {
				return $this->default_object;
			}

			$default_object     = false;
			$this->current_user = wp_get_current_user();

			global $post;

			if ( is_singular() ) {
				$default_object = $this->current_post = $post;
			} elseif ( is_tax() || is_category() || is_tag() || is_author() ) {
				$default_object     = $this->current_term = get_queried_object();
				$this->current_post = $post;
			} elseif ( wp_doing_ajax() ) {
				if ( isset( $_REQUEST['editor_post_id'] ) ) {
					$post_id = $_REQUEST['editor_post_id'];
				} elseif ( isset( $_REQUEST['post_id'] ) ) {
					$post_id = $_REQUEST['post_id'];
				} else {
					$post_id = false;
				}

				if ( ! $post_id ) {
					$default_object = $this->current_post = false;
				} else {
					$default_object = $this->current_post = get_post( $post_id );
				}

			} elseif ( is_archive() || is_home() || is_post_type_archive() ) {
				$default_object = $this->current_post = $post;
			}

			$this->default_object = apply_filters( 'jet-engine/listings/data/default-object', $default_object, $this );

			return $this->default_object;

		}

		/**
		 * Returns requested property from current object
		 *
		 * @param  [type] $property [description]
		 * @return [type]           [description]
		 */
		public function get_prop( $property = null, $object = null ) {

			if ( $this->is_user_prop( $property ) ) {

				if ( $object ) {
					$current_user = $object;
				} else {
					$current_user = $this->current_user;
				}

				if ( ! $current_user ) {
					return false;
				}

				$vars = get_object_vars( $current_user );
				$vars = ! empty( $vars['data'] ) ? (array) $vars['data'] : array();

				if ( 'user_nicename' === $property ) {
					$vars['user_nicename'] = get_user_meta( $current_user->ID, 'nickname', true );
				}

			} else {

				if ( ! $object ) {
					$object = $this->get_current_object();
				}

				if ( ! $object ) {
					return false;
				}

				$vars = get_object_vars( $object );
				$vars = apply_filters( 'jet-engine/listings/data/object-vars', $vars, $object );

				if ( 'post_id' === $property ) {
					$vars['post_id'] = $vars['ID'];
				}

			}

			return isset( $vars[ $property ] ) ? $vars[ $property ] : false;

		}

		/**
		 * Remove tabs and accordions from allowed fields list
		 *
		 * @param  [type] $fields [description]
		 * @return [type]         [description]
		 */
		public function sanitize_meta_fields( $fields ) {
			return array_filter( $fields, function( $field ) {
				if ( ! empty( $field['object_type'] ) && 'field' !== $field['object_type'] ) {
					return false;
				} else {
					return true;
				}
			} );
		}

		/**
		 * Returns option value by combined key
		 *
		 * @param  [type] $key [description]
		 * @return [type]      [description]
		 */
		public function get_option( $key = null ) {

			if ( ! jet_engine()->options_pages || ! $key ) {
				return null;
			}

			$data = explode( '::', $key );

			if ( 2 !== count( $data ) ) {
				return null;
			}

			$page_slug = $data[0];
			$option    = $data[1];

			if ( ! $page_slug || ! $option ) {
				return null;
			}

			$page = isset( jet_engine()->options_pages->registered_pages[ $page_slug ] ) ? jet_engine()->options_pages->registered_pages[ $page_slug ] : false;

			if ( ! $page ) {
				return;
			}

			return $page->get( $option );

		}

		/**
		 * Returns current meta
		 *
		 * @param  [type] $key [description]
		 * @return [type]      [description]
		 */
		public function get_meta( $key ) {

			$object = $this->get_current_object();

			if ( in_array( $key, $this->user_fields ) ) {

				$user = $this->get_queried_user_object();

				if ( ! $user ) {
					return false;
				} else {
					return get_user_meta( $user->ID, $key, true );
				}

			}

			if ( ! $object ) {
				return false;
			}

			$class  = get_class( $object );
			$result = '';

			switch ( $class ) {
				case 'WP_Post':

					if ( jet_engine()->relations->is_relation_key( $key ) ) {
						$single = false;
					} else {
						$single = true;
					}

					$source = false;

					if ( $this->current_listing ) {
						$source = $this->current_listing->get_settings( 'listing_source' );
					}

					if ( 'repeater' === $source ) {
						return $this->get_repeater_value( $key );
					} else {
						return get_post_meta( $object->ID, $key, $single );
					}

				case 'WP_Term':
					return get_term_meta( $object->term_id, $key, true );

				case 'WP_User':
					return get_user_meta( $object->ID, $key, true );

			}

		}

		/**
		 * Increase repeater index
		 *
		 * @return [type] [description]
		 */
		public function increase_index() {
			$this->repeater_index++;
		}

		/**
		 * Reset repeater index
		 *
		 * @return [type] [description]
		 */
		public function reset_index() {
			$this->repeater_index = 0;
		}

		/**
		 * Get repeater index
		 *
		 * @return int
		 */
		public function get_index() {
			return $this->repeater_index;
		}

		/**
		 * Set repeater index
		 *
		 * @param int $index
		 * @return void
		 */
		public function set_index( $index ) {
			$this->repeater_index = $index;
		}

		/**
		 * Returns repeater value
		 *
		 * @return [type] [description]
		 */
		public function get_repeater_value( $field ) {

			$source_field    = $this->current_listing->get_settings( 'repeater_field' );
			$repeater_source = $this->current_listing->get_settings( 'repeater_source' );
			$index           = $this->repeater_index;
			$object          = $this->get_current_object();

			switch ( $repeater_source ) {
				case 'jet_engine':
					$meta_value = get_post_meta( $object->ID, $source_field, true );

					if ( empty( $meta_value ) ) {
						return false;
					}

					$meta_value = array_values( $meta_value );

					if ( empty( $meta_value[ $index ] ) ) {
						return false;
					} else {
						return isset( $meta_value[ $index ][ $field ] ) ? $meta_value[ $index ][ $field ] : false;
					}

				case 'jet_engine_options':

					$source_option = $this->current_listing->get_settings( 'repeater_option' );

					if ( ! $source_option ) {
						return false;
					}

					$meta_value = $this->get_option( $source_option );

					if ( empty( $meta_value ) ) {
						return false;
					}

					$meta_value = array_values( $meta_value );

					if ( empty( $meta_value[ $index ] ) ) {
						return false;
					} else {
						return isset( $meta_value[ $index ][ $field ] ) ? $meta_value[ $index ][ $field ] : false;
					}

				case 'acf':
					return $this->get_acf_repeater_value( $object, $source_field, $field );

				default:
					return apply_filters(
						'jet-engine/listings/data/repeater-value/' . $repeater_source,
						false,
						$object,
						$source_field,
						$field,
						$index,
						$this
					);
			}

		}

		/**
		 * Returns value of ACF repeater field
		 *
		 * @param  [type] $parent_field [description]
		 * @param  [type] $child_field  [description]
		 * @return [type]               [description]
		 */
		public function get_acf_repeater_value( $object, $parent_field, $child_field ) {
			$field_key = $parent_field . '_' . $this->repeater_index . '_' . $child_field;
			return get_post_meta( $object->ID, $field_key, true );
		}

		/**
		 * Get permalink to current post/term
		 *
		 * @return string
		 */
		public function get_current_object_permalink() {

			$object = $this->get_current_object();
			$class  = get_class( $object );
			$result = '';

			switch ( $class ) {
				case 'WP_Post':
					return get_permalink( $object->ID );

				case 'WP_Term':
					return get_term_link( $object->term_id );

				case 'WP_User':
					return apply_filters( 'jet-engine/listings/data/user-permalink', false, $object );
			}

			return null;

		}

		/**
		 * Returns available list sources
		 *
		 * @return [type] [description]
		 */
		public function get_field_sources() {

			$sources = array(
				'object' => __( 'Post/Term/User/Object Data', 'jet-engine' ),
				'meta'   => __( 'Meta Data', 'jet-engine' ),
			);

			if ( jet_engine()->options_pages ) {
				$sources['options_page'] = __( 'Options', 'jet-engine' );
			}

			if ( jet_engine()->relations ) {
				$sources['relations_hierarchy'] = __( 'Relations Hierarchy', 'jet-engine' );
			}

			$source = false;

			if ( $this->current_listing ) {
				$source = $this->current_listing->get_settings( 'listing_source' );
			}

			if ( 'repeater' === $source ) {
				$sources['repeater_field'] = __( 'Repeater Field', 'jet-engine' );
			}

			return apply_filters( 'jet-engine/listings/data/sources', $sources );
		}

	}

}
