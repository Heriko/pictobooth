<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Render_Listing_Grid' ) ) {

	class Jet_Engine_Render_Listing_Grid extends Jet_Engine_Render_Base {

		public $is_first    = false;
		public $data        = false;
		public $query_vars  = array();
		public $posts_query = false;

		public function get_name() {
			return 'jet-listing-grid';
		}

		public function default_settings() {
			return apply_filters( 'jet-engine/listing/render/default-settings', array(
				'lisitng_id'               => '',
				'columns'                  => 3,
				'columns_tablet'           => 3,
				'columns_mobile'           => 3,
				'is_archive_template'      => '',
				'post_status'              => array( 'publish' ),
				'use_random_posts_num'     => '',
				'posts_num'                => 6,
				'max_posts_num'            => 9,
				'not_found_message'        => __( 'No data was found', 'jet-engine' ),
				'is_masonry'               => '',
				'equal_columns_height'     => '',
				'use_load_more'            => '',
				'load_more_id'             => '',
				'load_more_type'           => 'click',
				'use_custom_post_types'    => '',
				'custom_post_types'        => array(),
				'hide_widget_if'           => '',
				'carousel_enabled'         => '',
				'slides_to_scroll'         => '1',
				'arrows'                   => 'true',
				'arrow_icon'               => 'fa fa-angle-left',
				'dots'                     => '',
				'autoplay'                 => 'true',
				'autoplay_speed'           => 5000,
				'infinite'                 => 'true',
				'effect'                   => 'slide',
				'speed'                    => 500,
				'inject_alternative_items' => '',
				'injection_items'          => array(),
				//'repeater_items_query'     => false,
			) );
		}

		public function render() {
			$this->render_posts();
		}

		/**
		 * Build query arguments array based on settings
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function build_posts_query_args_array( $settings = array() ) {

			$post_type   = jet_engine()->listings->data->get_listing_post_type();
			$per_page    = $this->get_posts_num( $settings );
			$post_status = ! empty( $settings['post_status'] ) ? $settings['post_status'] : 'publish';

			$args = array(
				'post_status'    => $post_status,
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => ! empty( $settings['current_page'] ) ? $settings['current_page'] : 1,
			);

			if ( ! empty( $settings['use_custom_post_types'] ) && ! empty( $settings['custom_post_types'] ) ) {
				$args['post_type'] = $settings['custom_post_types'];
			}

			if ( ! empty( $settings['posts_query'] ) ) {

				foreach ( $settings['posts_query'] as $query_item ) {

					if ( empty( $query_item['type'] ) ) {
						continue;
					}

					$meta_index = 0;
					$tax_index  = 0;

					switch ( $query_item['type'] ) {

						case 'posts_params':
							$args = $this->add_posts_params_to_args( $args, $query_item );
							break;

						case 'order_offset':
							$args = $this->add_order_offset_to_args( $args, $query_item );
							break;

						case 'tax_query':
							$args = $this->add_tax_query_to_args( $args, $query_item );
							break;

						case 'meta_query':
							$args = $this->add_meta_query_to_args( $args, $query_item );
							break;

						case 'date_query':
							$args = $this->add_date_query_to_args( $args, $query_item );
							break;

					}

				}
			}

			// Custom query arguments passed in JSON format
			if ( ! empty( $settings['custom_posts_query'] ) ) {
				$custom_args = json_decode( $settings['custom_posts_query'], true );
				$args        = wp_parse_args( $custom_args, $args );
			}

			if ( ! empty( $args['tax_query'] ) && ( 1 < count( $args['tax_query'] ) ) ) {
				$relation = ! empty( $settings['tax_query_relation'] ) ? $settings['tax_query_relation'] : 'AND';
				$args['tax_query']['relation'] = $relation;
			}

			if ( ! empty( $args['meta_query'] ) && ( 1 < count( $args['meta_query'] ) ) ) {
				$relation = ! empty( $settings['meta_query_relation'] ) ? $settings['meta_query_relation'] : 'AND';
				$args['meta_query']['relation'] = $relation;
			}

			array_walk( $args, array( $this, 'apply_macros_in_query' ) );

			return apply_filters( 'jet-engine/listing/grid/posts-query-args', $args, $this, $settings );

		}

		/**
		 * Apply macros in query callback
		 *
		 * @param  mixed &$item
		 * @return void
		 */
		public function apply_macros_in_query( &$item ) {
			if ( ! is_array( $item ) ) {
				$item = jet_engine()->listings->macros->do_macros( $item );
			}
		}

		/**
		 * Build terms query arguments array based on settings
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function build_terms_query_args_array( $settings = array() ) {

			$tax    = jet_engine()->listings->data->get_listing_tax();
			$number = $this->get_posts_num( $settings );

			$args = array(
				'taxonomy' => $tax,
				'number'   => $number,
			);

			$keys = array(
				'terms_orderby',
				'terms_order',
				'terms_offset',
				'terms_child_of',
			);

			foreach ( $keys as $key ) {

				if ( empty( $settings[ $key ] ) ) {
					continue;
				}

				$args[ str_replace( 'terms_', '', $key ) ] = esc_attr( $settings[ $key ] );

			}

			if ( ! empty( $settings['terms_object_ids'] ) ) {

				$ids = jet_engine()->listings->macros->do_macros( $settings['terms_object_ids'], $tax );
				$ids = $this->explode_string( $ids );

				if ( 1 === count( $ids ) ) {
					$args['object_ids'] = $ids[0];
				} else {
					$args['object_ids'] = $ids;
				}

			}

			if ( ! empty( $settings['terms_hide_empty'] ) && 'true' === $settings['terms_hide_empty'] ) {
				$args['hide_empty'] = true;
			} else {
				$args['hide_empty'] = false;
			}

			if ( ! empty( $settings['terms_meta_query'] ) ) {
				foreach ( $settings['terms_meta_query'] as $query_item ) {
					$args = $this->add_meta_query_to_args( $args, $query_item );
				}
			}

			if ( ! empty( $args['meta_query'] ) && ( 1 < count( $args['meta_query'] ) ) ) {
				$rel = ! empty( $settings['term_meta_query_relation'] ) ? $settings['term_meta_query_relation'] : 'AND';
				$args['meta_query']['relation'] = $rel;
			}

			array_walk( $args, array( $this, 'apply_macros_in_query' ) );

			foreach ( array( 'terms_include', 'terms_exclude' ) as $key ) {

				$ids = jet_engine()->listings->macros->do_macros( $settings[ $key ], $tax );
				$ids = $this->explode_string( $ids );
				$arg = str_replace( 'terms_', '', $key );

				if ( 1 === count( $ids ) ) {
					$args[ $arg ] = $ids[0];
				} else {
					$args[ $arg ] = $ids;
				}
			}

			return apply_filters( 'jet-engine/listing/grid/terms-query-args', $args, $this, $settings );

		}

		/**
		 * Builder users query arguments array by widget settings
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function build_users_query_args_array( $settings ) {

			$number = $this->get_posts_num( $settings );

			$args = array(
				'number' => $number,
			);

			if ( ! empty( $settings['users_meta_query'] ) ) {
				foreach ( $settings['users_meta_query'] as $query_item ) {
					$args = $this->add_meta_query_to_args( $args, $query_item );
				}
			}

			if ( ! empty( $args['meta_query'] ) && ( 1 < count( $args['meta_query'] ) ) ) {
				$rel = ! empty( $settings['users_meta_query_relation'] ) ? $settings['users_meta_query_relation'] : 'AND';
				$args['meta_query']['relation'] = $rel;
			}

			foreach ( array( 'users_role__in', 'users_role__not_in' ) as $key ) {
				$roles = ! empty( $settings[ $key ] ) ? $settings[ $key ] : array();
				$arg   = str_replace( 'users_', '', $key );

				if ( ! empty( $roles ) ) {
					$args[ $arg ] = $roles;
				}
			}

			foreach ( array( 'users_include', 'users_exclude' ) as $key ) {

				$ids = ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
				$ids = jet_engine()->listings->macros->do_macros( $ids );
				$ids = $this->explode_string( $ids );
				$arg = str_replace( 'users_', '', $key );

				if ( 1 === count( $ids ) ) {
					$args[ $arg ] = $ids[0];
				} else {
					$args[ $arg ] = $ids;
				}
			}

			return apply_filters( 'jet-engine/listing/grid/users-query-args', $args, $this );

		}

		/**
		 * Add post parameters to arguments
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function add_posts_params_to_args( $args, $settings ) {

			$post_args = array(
				'posts_in'     => $settings['posts_in'],
				'posts_not_in' => $settings['posts_not_in'],
				'posts_parent' => $settings['posts_parent'],
			);

			array_walk( $post_args, array( $this, 'apply_macros_in_query' ) );

			if ( isset( $post_args['posts_in'] ) && '' !== $post_args['posts_in'] ) {
				$args['post__in'] = $this->explode_string( $post_args['posts_in'], true );
			}

			if ( ! empty( $post_args['posts_not_in'] ) ) {
				$args['post__not_in'] = $this->explode_string( $post_args['posts_not_in'] );
			}

			if ( ! empty( $post_args['posts_parent'] ) ) {
				$parent = $this->explode_string( $post_args['posts_parent'] );

				if ( 1 === count( $parent ) ) {
					$args['post_parent'] = $parent[0];
				} else {
					$args['post_parent__in'] = $parent;
				}

			}

			if ( ! empty( $settings['posts_status'] ) ) {
				$args['post_status'] = esc_attr( $settings['posts_status'] );
			}

			if ( ! empty( $settings['posts_author'] ) && 'any' !== $settings['posts_author'] ) {
				if ( 'current' === $settings['posts_author'] && is_user_logged_in() ) {
					$args['author'] = get_current_user_id();
				} elseif ( 'id' === $settings['posts_author'] && ! empty( $settings['posts_author_id'] ) ) {
					$args['author'] = $settings['posts_author_id'];
				} elseif( 'queried' === $settings['posts_author'] ) {

					$u_id = false;

					if ( is_author() ) {
						$u_id = get_queried_object_id();
					} elseif ( jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
						$u_id = \Jet_Engine\Modules\Profile_Builder\Module::instance()->query->get_queried_user_id();
					}

					if ( ! $u_id ) {
						$u_id = get_current_user_id();
					}

					$args['author'] = $u_id;
				}
			}

			return $args;

		}

		/**
		 * Process multiple orderby parameters
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function process_multiple_orderby( $args, $settings ) {

			if ( ! is_array( $args['orderby'] ) ) {

				$initial_orderby = $args['orderby'];
				$initial_order = ! empty( $args['order'] ) ? $args['order'] : 'DESC';

				if ( ! empty( $args['order'] ) ) {
					unset( $args['order'] );
				}

				if ( in_array( $initial_orderby, array( 'meta_value', 'meta_value_num' ) ) ) {
					$initial_orderby = $args['meta_key'];
				}

				$args['orderby'] = array(
					$initial_orderby => $initial_order,
				);

			}

			$order_by = ! empty( $settings['order_by'] ) ? esc_attr( $settings['order_by'] ) : 'date';
			$order    = ! empty( $settings['order'] ) ? esc_attr( $settings['order'] ) : 'DESC';

			if ( 'meta_value' === $order_by ) {
				$order_by  = ! empty( $settings['meta_key'] ) ? esc_attr( $settings['meta_key'] ) : $order_by;
			} elseif ( 'meta_clause' === $order_by ) {
				$order_by = ! empty( $settings['meta_clause_key'] ) ? esc_attr( $settings['meta_clause_key'] ) : '';
			} elseif ( 'rand' === $order_by ) {
				$order_by = sprintf( 'RAND(%s)', rand() );
			}

			if ( $order_by ) {
				$args['orderby'][ $order_by ] = $order;
			}

			return $args;

		}

		/**
		 * Add order and offset parameters to arguments
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function add_order_offset_to_args( $args, $settings ) {

			if ( ! empty( $settings['offset'] ) ) {
				$args['offset'] = absint( $settings['offset'] );
			}

			if ( ! empty( $args['orderby'] ) ) {
				return $this->process_multiple_orderby( $args, $settings );
			}

			if ( ! empty( $settings['order'] ) ) {
				$args['order'] = esc_attr( $settings['order'] );
			}

			$order_by = ! empty( $settings['order_by'] ) ? esc_attr( $settings['order_by'] ) : 'date';

			if ( 'meta_value' === $order_by ) {

				$meta_key  = ! empty( $settings['meta_key'] ) ? esc_attr( $settings['meta_key'] ) : 'CHAR';
				$meta_type = ! empty( $settings['meta_type'] ) ? esc_attr( $settings['meta_type'] ) : 'CHAR';

				if ( 'CHAR' === $meta_type ) {
					$args['orderby']  = $order_by;
					$args['meta_key'] = $meta_key;
				} else {
					$args['orderby']   = 'meta_value_num';
					$args['meta_key']  = $meta_key;
					$args['meta_type'] = $meta_type;
				}

			} elseif ( 'meta_clause' === $order_by ) {

				$clause = ! empty( $settings['meta_clause_key'] ) ? esc_attr( $settings['meta_clause_key'] ) : '';

				if ( $clause ) {
					$args['orderby'] = $clause;
				}

			} elseif ( 'rand' === $order_by ) {
				$args['orderby'] = sprintf( 'RAND(%s)', rand() );
			} else {
				$args['orderby'] = $order_by;
			}

			return $args;

		}

		/**
		 * Add tax query parameters to arguments
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function add_tax_query_to_args( $args, $settings ) {

			$taxonomy = '';

			if ( ! empty( $settings['tax_query_taxonomy_meta'] ) ) {
				$taxonomy = get_post_meta( get_the_ID(), esc_attr( $settings['tax_query_taxonomy_meta'] ), true );
			} else {
				$taxonomy = ! empty( $settings['tax_query_taxonomy'] ) ? esc_attr( $settings['tax_query_taxonomy'] ) : '';
			}

			$settings = apply_filters( 'jet-engine/listing/grid/tax-query-item-settings', $settings, $args, $this );

			if ( ! $taxonomy ) {
				return $args;
			}

			if ( empty( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}

			$compare = ! empty( $settings['tax_query_compare'] ) ? esc_attr( $settings['tax_query_compare'] ) : 'IN';
			$field   = ! empty( $settings['tax_query_field'] ) ? esc_attr( $settings['tax_query_field'] ) : 'IN';

			$terms = '';

			if ( ! empty( $settings['tax_query_terms_meta'] ) ) {
				$terms = get_post_meta( get_the_ID(), esc_attr( $settings['tax_query_terms_meta'] ), true );
			} else {

				$terms = ! empty( $settings['tax_query_terms'] ) ? esc_attr( $settings['tax_query_terms'] ) : '';
				$terms = jet_engine()->listings->macros->do_macros( $terms, $taxonomy );
				$terms = $this->explode_string( $terms );

			}

			if ( ! empty( $terms ) && ! in_array( $compare, array( 'NOT EXISTS', 'EXISTS' ) ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => $field,
					'terms'    => $terms,
					'operator' => $compare,
				);
			} elseif ( in_array( $compare, array( 'NOT EXISTS', 'EXISTS' ) ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'operator' => $compare,
				);
			}

			return $args;

		}

		/**
		 * Add meta query parameters to arguments
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function add_meta_query_to_args( $args, $settings ) {

			$key = ! empty( $settings['meta_query_key'] ) ? esc_attr( $settings['meta_query_key'] ) : '';

			if ( ! $key ) {
				return $args;
			}

			$type    = ! empty( $settings['meta_query_type'] ) ? esc_attr( $settings['meta_query_type'] ) : 'CHAR';
			$compare = ! empty( $settings['meta_query_compare'] ) ? $settings['meta_query_compare'] : '=';
			$value   = isset( $settings['meta_query_val'] ) ? $settings['meta_query_val'] : '';

			if ( ! empty( $settings['meta_query_request_val'] ) ) {

				$query_var = $settings['meta_query_request_val'];

				if ( isset( $_GET[ $query_var ] ) ) {
					$request_val = $_GET[ $query_var ];
				} else {
					$request_val = get_query_var( $query_var );
				}

				if ( $request_val ) {
					$value = $request_val;
				}

			}

			$value = jet_engine()->listings->macros->do_macros( $value, $key );

			if ( in_array( $compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) {
				$value = $this->explode_string( $value );
			}

			if ( in_array( $type, array( 'DATE', 'DATETIME' ) ) ) {

				if ( is_array( $value ) ) {
					$value = array_map( 'strtotime', $value );
				} else {
					$value = strtotime( $value );
				}

				$type = 'NUMERIC';

			}

			$row = array(
				'key'     => $key,
				'value'   => $value,
				'compare' => $compare,
				'type'    => $type,
			);

			if ( in_array( $compare, array( 'EXISTS', 'NOT EXISTS' ) ) ) {
				unset( $row['value'] );
			}

			if ( ! empty( $settings['meta_query_clause'] ) ) {
				$clause = esc_attr( $settings['meta_query_clause'] );
				$args['meta_query'][ $clause ] = $row;
			} else {
				$args['meta_query'][] = $row;
			}

			return $args;

		}

		/**
		 * Add date query parameters to args.
		 *
		 * @param  array $args
		 * @param  array $settings
		 * @return array
		 */
		public function add_date_query_to_args( $args, $settings ) {

			$column    = isset( $settings['date_query_column'] ) ? $settings['date_query_column'] : 'post_date';
			$after     = isset( $settings['date_query_after'] ) ? $settings['date_query_after'] : '';
			$before    = isset( $settings['date_query_before'] ) ? $settings['date_query_before'] : '';
			$after     = jet_engine()->listings->macros->do_macros( $after );
			$before    = jet_engine()->listings->macros->do_macros( $before );

			$args['date_query'][] = array(
				'column'    => $column,
				'after'     => $after,
				'before'    => $before,
			);

			return $args;

		}

		/**
		 * Explode string to array
		 *
		 * @param  string $string
		 * @return mixed
		 */
		public function explode_string( $string, $unfiltered = false ) {

			if ( is_array( $string ) ) {
				return $string;
			}

			$array = explode( ',', $string );

			if ( empty( $array ) ) {
				return array();
			}

			if ( $unfiltered ) {
				return array_map( 'trim', $array );
			} else {
				return array_filter( array_map( 'trim', $array ) );
			}

		}

		public function get_default_query( $wp_query ) {

			// Ensure jet-engine/listing/grid/posts-query-args hook correctly fires even for archive (For filters compat)
			$default_query = array(
				'post_status'    => 'publish',
				'found_posts'    => $wp_query->found_posts,
				'max_num_pages'  => $wp_query->max_num_pages,
				'post_type'      => $wp_query->get( 'post_type' ),
				'tax_query'      => $wp_query->get( 'tax_query' ),
				'orderby'        => $wp_query->get( 'orderby' ),
				'paged'          => $wp_query->get( 'paged' ),
				'posts_per_page' => $wp_query->get( 'posts_per_page' ),
			);

			if( is_object( $wp_query->tax_query ) ){
				$default_query['tax_query'] = $wp_query->tax_query->queries;
			}

			$author = $wp_query->get( 'author' );

			if ( $author ) {
				$default_query['author'] = $author;
			}

			if ( $wp_query->get( 'taxonomy' ) ) {
				$default_query['taxonomy'] = $wp_query->get( 'taxonomy' );
				$default_query['term']     = $wp_query->get( 'term' );
			}

			if ( $wp_query->get( 's' ) ) {
				$default_query['s'] = $wp_query->get( 's' );
			}

			return $default_query;

		}

		/**
		 * Get posts
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function get_posts( $settings ) {

			if ( isset( $settings['is_archive_template'] ) && 'yes' === $settings['is_archive_template'] ) {

				global $wp_query;

				$default_query = $this->get_default_query( $wp_query );
				$default_query = apply_filters( 'jet-engine/listing/grid/posts-query-args', $default_query, $this, $settings );

				$this->query_vars['page']    = $wp_query->get( 'paged' ) ? $wp_query->get( 'paged' ) : 1;
				$this->query_vars['pages']   = $wp_query->max_num_pages;
				$this->query_vars['request'] = $default_query;

				$this->posts_query = $wp_query;

				return $wp_query->posts;

			} else {

				$args  = $this->build_posts_query_args_array( $settings );
				$query = new \WP_Query( $args );

				$this->posts_query = $query;

				$this->query_vars['page']    = $query->get( 'paged' ) ? $query->get( 'paged' ) : 1;
				$this->query_vars['pages']   = $query->max_num_pages;
				$this->query_vars['request'] = $args;

				return $query->posts;
			}

		}

		/**
		 * Get terms list
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function get_terms( $settings ) {

			$args = $this->build_terms_query_args_array( $settings );

			$this->query_vars['request'] = $args;

			if ( ! empty( $settings['use_load_more'] ) ) {
				$taxonomy                  = jet_engine()->listings->data->get_listing_tax();
				$total                     = wp_count_terms( $taxonomy, $args );
				$per_page                  = $this->get_posts_num( $settings );
				$pages                     = ceil( $total / $per_page );
				$page                      = 1;
				$this->query_vars['page']  = $page;
				$this->query_vars['pages'] = $pages;
			} else {
				$this->query_vars['page']  = 1;
				$this->query_vars['pages'] = 1;
			}

			$terms = get_terms( $args );

			return $terms;

		}

		/**
		 * Check widget visibility settings and hide if false
		 *
		 * @param  array  $query    Query array.
		 * @param  array  $settings Settings array.
		 * @return boolean
		 */
		public function is_widget_visible( $query, $settings ) {

			if ( ! empty( $settings['hide_widget_if'] ) ) {

				switch ( $settings['hide_widget_if'] ) {

					case 'empty_query':

						return empty( $query ) ? false : true;

						break;

					default:

						if ( is_callable( $settings['hide_widget_if'] ) ) {
							return call_user_func( $settings['hide_widget_if'], $query, $settings );
						} else {
							return apply_filters( 'jet-engine/listing/grid/widget-visibility', true, $query, $settings );
						}

						break;
				}

			}

			return true;

		}

		/**
		 * Render grid posts
		 *
		 * @return void
		 */
		public function render_posts() {

			$settings = $this->get_settings();

			if ( empty( $settings['lisitng_id'] ) ) {
				printf( '<div class="jet-listing-notice">%s</div>', __( 'Please select listing to show.', 'jet-engine' ) );
				return;
			}

			if ( $this->is_lazy_load_enabled( $settings ) ) {
				$this->print_lazy_load_wrapper( $settings );
				return;
			}

			$current_listing = jet_engine()->listings->data->get_listing();

			if ( jet_engine()->has_elementor() ) {
				$doc = Elementor\Plugin::$instance->documents->get_doc_for_frontend( $settings['lisitng_id'] );
			} else {
				$listing_settings = get_post_meta( $settings['lisitng_id'], '_elementor_page_settings', true );

				if ( empty( $listing_settings ) ) {
					$listing_settings = array();
				}

				$source          = ! empty( $listing_settings['listing_source'] ) ? $listing_settings['listing_source'] : 'posts';
				$post_type       = ! empty( $listing_settings['listing_post_type'] ) ? $listing_settings['listing_post_type'] : 'post';
				$tax             = ! empty( $listing_settings['listing_tax'] ) ? $listing_settings['listing_tax'] : 'category';
				$repeater_source = ! empty( $listing_settings['repeater_source'] ) ? $listing_settings['repeater_source'] : '';
				$repeater_field  = ! empty( $listing_settings['repeater_field'] ) ? $listing_settings['repeater_field'] : '';

				$doc = jet_engine()->listings->get_new_doc( array(
					'listing_source'    => $source,
					'listing_post_type' => $post_type,
					'listing_tax'       => $tax,
					'is_main'           => true,
					'repeater_source'   => $repeater_source,
					'repeater_field'    => $repeater_field,
				) );
			}

			jet_engine()->listings->data->set_listing( $doc );

			$listing_source = jet_engine()->listings->data->get_listing_source();

			switch ( $listing_source ) {

				case 'posts':
					$query = $this->get_posts( $settings );
					break;

				case 'terms':
					$query = $this->get_terms( $settings );
					break;

				case 'users':
					$query = $this->get_users( $settings );
					break;

				case 'repeater':
					$query = $this->get_repeater_items( $settings );
					break;

				default:
					$query = apply_filters(
						'jet-engine/listing/grid/query/' . $listing_source,
						array(),
						$settings,
						$this
					);

					break;
			}

			if ( ! $this->is_widget_visible( $query, $settings ) ) {
				return;
			}

			$current_object = jet_engine()->listings->data->get_current_object();

			$this->posts_template( $query, $settings );

			//jet_engine()->listings->data->reset_listing();

			// Need when several listings into a listing item
			jet_engine()->listings->data->set_current_object( $current_object );
			jet_engine()->listings->data->set_listing( $current_listing );

		}

		/**
		 * Is the Lazy Load enabled.
		 *
		 * @param  array $settings
		 * @return bool
		 */
		public function is_lazy_load_enabled( $settings ) {

			$result = ! empty( $settings['lazy_load'] ) ? filter_var( $settings['lazy_load'], FILTER_VALIDATE_BOOLEAN ) : false;

			return apply_filters( 'jet-engine/listing/grid/is_lazy_load', $result, $settings );
		}

		/**
		 * Print Lazy Load wrapper.
		 *
		 * @param  array $settings Settings array
		 * @return void
		 */
		public function print_lazy_load_wrapper( $settings ) {
			$base_class = $this->get_name();

			$this->enqueue_assets( $settings );

			if ( ! empty( $settings['lazy_load_offset'] ) && is_array( $settings['lazy_load_offset'] ) ) {
				$size = ! empty( $settings['lazy_load_offset']['size'] ) ? $settings['lazy_load_offset']['size'] : '0';
				$unit = ! empty( $settings['lazy_load_offset']['unit'] ) ? $settings['lazy_load_offset']['unit'] : 'px';

				$offset = $size . $unit;
			} else {
				$offset = '0px';
			}

			$options = array(
				'offset'  => $offset,
				'post_id' => isset( Elementor\Plugin::$instance->documents ) ? Elementor\Plugin::$instance->documents->get_current()->get_main_id() : get_the_ID(),
			);

			if ( is_singular() ) {
				$options['queried_id'] = get_the_ID();
			}

			if ( is_archive() && ! empty( $settings['is_archive_template'] ) ) {
				global $wp_query;
				$default_query = $this->get_default_query( $wp_query );
				$default_query = apply_filters( 'jet-engine/listing/grid/posts-query-args', $default_query, $this, $settings );
				$options['query'] = $default_query;
			}

			printf( '<div class="%1$s %1$s--lazy-load jet-listing" data-lazy-load="%2$s"></div>', $base_class, htmlspecialchars( json_encode( $options ) ) );
		}

		/**
		 * Ensure current object is properly set in the edit context of blocks editor
		 *
		 * @return [type] [description]
		 */
		public function ensure_current_object_for_block_editor() {

			if ( empty( $_GET['context'] ) || 'edit' !== $_GET['context'] ) {
				return;
			}

			if ( empty( $_GET['post_id'] ) ) {
				return;
			}

			jet_engine()->listings->data->set_current_object( get_post( absint( $_GET['post_id'] ) ) );

		}

		/**
		 * Returns repeater items
		 *
		 * @param  [type] $settings [description]
		 * @return [type]           [description]
		 */
		public function get_repeater_items( $settings ) {

			$this->ensure_current_object_for_block_editor();

			$query           = array();
			$listing         = jet_engine()->listings->data->get_listing();
			$repeater_source = $listing->get_settings( 'repeater_source' );
			$repeater_field  = $listing->get_settings( 'repeater_field' );
			$repeater_option = $listing->get_settings( 'repeater_option' );
			$current_object  = jet_engine()->listings->data->get_current_object();
			$meta_value      = false;

			if ( 'jet_engine_options' !== $repeater_source && ( ! $current_object || ! $current_object->ID ) ) {
				return $query;
			}

			switch ( $repeater_source ) {

				case 'jet_engine_options':

					if ( ! $repeater_option ) {
						return $query;
					} else {
						$meta_value = jet_engine()->listings->data->get_option( $repeater_option );
					}
					break;

				default:
					$meta_value = get_post_meta( $current_object->ID, $repeater_field, true );
					break;

			}

			if ( empty( $meta_value ) ) {
				return $query;
			}

			if ( 'acf' === $repeater_source ) {
				$count = $meta_value;
			} else {
				$count = count( $meta_value );
			}

			$query = array_fill( 0, $count, $current_object );

			$this->query_vars['page']    = 1;
			$this->query_vars['pages']   = 1;
			$this->query_vars['request'] = false;

			return $query;

		}

		/**
		 * Query users
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function get_users( $settings ) {

			$args = $this->build_users_query_args_array( $settings );

			$args['count_total'] = ! empty( $settings['use_load_more'] ) ? true : false;

			$args = apply_filters( 'jet-engine/listing/grid/posts-query-args', $args, $this, $settings );

			$user_query = new \WP_User_Query( $args );

			if ( $args['count_total'] ) {

				$total    = $user_query->get_total();
				$per_page = $this->get_posts_num( $settings );
				$offset   = ! empty( $settings['users_offset'] ) ? absint( $settings['users_offset'] ) : 0;
				$pages    = ceil( $total / $per_page );
				$page     = floor( $offset / $per_page ) + 1;

				$this->query_vars['page']    = $page;
				$this->query_vars['pages']   = $pages;
				$this->query_vars['request'] = $args;
			} else {
				$this->query_vars['page']    = 1;
				$this->query_vars['pages']   = 1;
				$this->query_vars['request'] = $args;
			}

			$users = (array) $user_query->get_results();

			return $users;
		}

		/**
		 * Returns navigation data settings string
		 *
		 * @param  array $settings
		 * @return string
		 */
		public function get_nav_settings( $settings ) {

			$columns = $this->get_columns_settings( $settings );

			$result = array(
				'enabled'         => false,
				'type'            => null,
				'more_el'         => null,
				'query'           => array(),
				'widget_settings' => array(
					'lisitng_id'               => ! empty( $settings['lisitng_id'] ) ? $settings['lisitng_id'] : '',
					'posts_num'                => $this->get_posts_num( $settings ),
					'columns'                  => $columns['desktop'],
					'columns_tablet'           => $columns['tablet'],
					'columns_mobile'           => $columns['mobile'],
					'is_archive_template'      => ! empty( $settings['is_archive_template'] ) ? $settings['is_archive_template'] : '',
					'post_status'              => ! empty( $settings['post_status'] ) ? $settings['post_status'] : array( 'publish' ),
					'use_random_posts_num'     => ! empty( $settings['use_random_posts_num'] ) ? $settings['use_random_posts_num'] : '',
					'max_posts_num'            => ! empty( $settings['max_posts_num'] ) ? $settings['max_posts_num'] : 9,
					'not_found_message'        => ! empty( $settings['not_found_message'] ) ? $settings['not_found_message'] : __( 'No data was found', 'jet-engine' ),
					'is_masonry'               => ! empty( $settings['is_masonry'] ) ? $settings['is_masonry'] : '',
					'equal_columns_height'     => ! empty( $settings['equal_columns_height'] ) ? $settings['equal_columns_height'] : '',
					'use_load_more'            => ! empty( $settings['use_load_more'] ) ? $settings['use_load_more'] : '',
					'load_more_id'             => ! empty( $settings['load_more_id'] ) ? $settings['load_more_id'] : '',
					'load_more_type'           => ! empty( $settings['load_more_type'] ) ? $settings['load_more_type'] : 'click',
					'use_custom_post_types'    => ! empty( $settings['use_custom_post_types'] ) ? $settings['use_custom_post_types'] : '',
					'custom_post_types'        => ! empty( $settings['custom_post_types'] ) ? $settings['custom_post_types'] : array(),
					'hide_widget_if'           => ! empty( $settings['hide_widget_if'] ) ? $settings['hide_widget_if'] : '',
					'carousel_enabled'         => ! empty( $settings['carousel_enabled'] ) ? $settings['carousel_enabled'] : '',
					'slides_to_scroll'         => ! empty( $settings['slides_to_scroll'] ) ? $settings['slides_to_scroll'] : '1',
					'arrows'                   => ! empty( $settings['arrows'] ) ? $settings['arrows'] : 'true',
					'arrow_icon'               => ! empty( $settings['arrow_icon'] ) ? $settings['arrow_icon'] : 'fa fa-angle-left',
					'dots'                     => ! empty( $settings['dots'] ) ? $settings['dots'] : '',
					'autoplay'                 => ! empty( $settings['autoplay'] ) ? $settings['autoplay'] : 'true',
					'autoplay_speed'           => ! empty( $settings['autoplay_speed'] ) ? $settings['autoplay_speed'] : 5000,
					'infinite'                 => ! empty( $settings['infinite'] ) ? $settings['infinite'] : 'true',
					'effect'                   => ! empty( $settings['effect'] ) ? $settings['effect'] : 'slide',
					'speed'                    => ! empty( $settings['speed'] ) ? $settings['speed'] : 500,
					'inject_alternative_items' => ! empty( $settings['inject_alternative_items'] ) ? $settings['inject_alternative_items'] : '',
					'injection_items'          => ! empty( $settings['injection_items'] ) ? $settings['injection_items'] : array(),
					'_element_id'              => ! empty( $settings['_element_id'] ) ? $settings['_element_id'] : '',
				),
			);

			$has_load_more  = ! empty( $settings['use_load_more'] );
			$add_query_data = apply_filters( 'jet-engine/listing/grid/add-query-data', $has_load_more, $this );

			if ( $add_query_data ) {
				$result['query']           = $this->query_vars['request'];
				$result['widget_settings'] = apply_filters(
					'jet-engine/listing/grid/nav-widget-settings',
					$result['widget_settings'],
					$settings
				);
			}

			if ( $has_load_more ) {
				$result['enabled'] = true;
				$result['type']    = ! empty( $settings['load_more_type'] ) ? $settings['load_more_type'] : 'click';
				$result['more_el'] = ! empty( $settings['load_more_id'] ) ? '#' . trim( $settings['load_more_id'], '#' ) : null;
			}

			return htmlspecialchars( json_encode( $result ) );

		}

		/**
		 * Render posts template.
		 * Moved to separate function to be rewritten by other layouts
		 *
		 * @param  array  $query    Query array.
		 * @param  array  $settings Settings array.
		 * @return void
		 */
		public function posts_template( $query, $settings ) {

			$base_class  = $this->get_name();
			$columns     = $this->get_columns_settings( $settings );
			$desktop_col = $columns['desktop'];
			$tablet_col  = $columns['tablet'];
			$mobile_col  = $columns['mobile'];
			$base_col    = 'grid-col-';

			$container_classes = array(
				$base_class . '__items',
				$base_col . 'desk-' . $desktop_col,
				$base_col . 'tablet-' . $tablet_col,
				$base_col . 'mobile-' . $mobile_col,
				$base_class . '--' . $settings['lisitng_id'],
			);

			$this->enqueue_assets( $settings );

			$carousel_enabled = $this->is_carousel_enabled( $settings );

			$container_attrs = array();

			if ( ! empty( $settings['is_masonry'] ) ) {
				$container_classes[] = $base_class . '__masonry';
				$container_attrs[]   = $this->get_masonry_options( $settings );
			}

			printf( '<div class="%1$s jet-listing">', $base_class );

			$container_attrs = apply_filters(
				'jet-engine/listing/container-atts',
				$container_attrs,
				$settings,
				$this
			);

			if ( ! empty( $query ) ) {

				do_action( 'jet-engine/listing/grid/before', $this );

				if ( $carousel_enabled ) {

					$is_rtl                  = is_rtl();
					$dir                     = $is_rtl ? 'rtl' : 'ltr';
					$settings['items_count'] = count( $query );

					printf(
						'<div class="%1$s__slider" data-slider_options="%2$s" dir="%3$s">',
						$base_class,
						$this->get_slider_options( $settings, $is_rtl ),
						$dir
					);

				}

				$equal_cols_class = '';

				if ( ! empty( $settings['equal_columns_height'] ) ) {
					$equal_cols_class    = 'jet-equal-columns';
					$container_classes[] = 'jet-equal-columns__wrapper';
				}

				printf(
					'<div class="%1$s" %2$s data-nav="%3$s" data-page="%4$d" data-pages="%5$d" data-listing-source="%6$s">',
					implode( ' ', $container_classes ),
					implode( ' ', $container_attrs ),
					$this->get_nav_settings( $settings ),
					$this->query_vars['page'],
					$this->query_vars['pages'],
					jet_engine()->listings->data->get_listing_source()
				);

				$this->posts_loop( $query, $settings, $base_class, $equal_cols_class );

				echo '</div>';

				if ( $carousel_enabled ) {
					echo '</div>';
				}

				do_action( 'jet-engine/listing/grid/after', $this );

			} else {
				printf(
					'<div class="jet-listing-not-found %3$s" data-nav="%2$s" %4$s>%1$s</div>',
					do_shortcode( wp_unslash( $settings['not_found_message'] ) ),
					$this->get_nav_settings( $settings ),
					$base_class . '__items',
					implode( ' ', $container_attrs )
				);
			}

			echo '</div>';

		}

		/**
		 * Output posts loop
		 *
		 * @param array  $query
		 * @param array  $settings
		 * @param string $base_class
		 * @param string $equal_cols_class
		 * @param bool $start_from
		 */
		public function posts_loop( $query, $settings, $base_class, $equal_cols_class, $start_from = false ) {

			if ( ! empty( $start_from ) ) {
				$i = absint( $start_from );
			} else {
				$i = 1;
			}

			global $wp_query, $post;
			$default_object = $wp_query->queried_object;

			$initial_index = jet_engine()->listings->data->get_index();
			jet_engine()->listings->data->reset_index();

			$temp_query = false;

			if ( $this->posts_query ) {

				$is_singular = is_singular();

				$temp_query = $wp_query;
				$wp_query   = $this->posts_query;

				// For compatibility with ACF Dynamic Tags(Elementor Pro)
				$wp_query->is_singular = $is_singular;

				$temp_query->post = $post;
			}

			foreach ( $query as $post_obj ) {

				if ( empty( $post_obj ) ) {
					continue;
				}

				$wp_query->queried_object = $post_obj;

				ob_start();

				$content = apply_filters( 'jet-engine/listing/pre-get-item-content', false, $post_obj, $i, $this );

				$static_inject = ob_get_clean();

				if ( ! $content ) {
					jet_engine()->frontend->set_listing( $settings['lisitng_id'] );
					$content = jet_engine()->frontend->get_listing_item( $post_obj );
				}

				$class = get_class( $post_obj );

				switch ( $class ) {
					case 'WP_Post':
					case 'WP_User':
						$post_id = $post_obj->ID;
						break;

					case 'WP_Term':
						$post_id = $post_obj->term_id;
						break;

					default:
						$post_id = apply_filters( 'jet-engine/listing/custom-post-id', get_the_ID(), $post_obj );
				}

				$classes = array(
					$base_class . '__item',
					'jet-listing-dynamic-post-' . $post_id,
					$equal_cols_class
				);

				if ( $static_inject ) {

					$static_classes = apply_filters( 'jet-engine/listing/item-classes', $classes, $post_obj, $i, $this );

					printf(
						'<div class="%1$s" data-post-id="%3$s">%2$s</div>',
						implode( ' ', array_filter( $static_classes ) ),
						$static_inject,
						$post_id
					);

					$i++;

				}

				$classes = apply_filters( 'jet-engine/listing/item-classes', $classes, $post_obj, $i, $this );

				do_action( 'jet-engine/listing/before-grid-item', $post_obj, $this );

				printf(
					'<div class="%1$s" data-post-id="%3$s">%2$s</div>',
					implode( ' ', array_filter( $classes ) ),
					$content,
					$post_id
				);

				do_action( 'jet-engine/listing/after-grid-item', $post_obj, $this );

				$i++;
				jet_engine()->listings->data->increase_index();

			}

			if ( $this->posts_query && $temp_query ) {
				$wp_query = $temp_query;
			}

			$wp_query->queried_object = $default_object;
			jet_engine()->frontend->reset_listing();

			jet_engine()->listings->data->set_index( $initial_index );

		}

		/**
		 * Enqueue depends assets.
		 *
		 * @param  array $settings Settings array
		 * @return void
		 */
		public function enqueue_assets( $settings ) {
			$carousel_enabled = $this->is_carousel_enabled( $settings );
			$masonry_enabled  = ! empty( $settings['is_masonry'] ) ? $settings['is_masonry'] : false;

			if ( $masonry_enabled ) {
				jet_engine()->frontend->enqueue_masonry_assets();
			}

			if ( $carousel_enabled ) {
				wp_enqueue_script( 'jquery-slick' );
			}
		}

		/**
		 * Is carousel enabled.
		 *
		 * @param  array $settings
		 * @return bool
		 */
		public function is_carousel_enabled( $settings ) {
			$carousel_enabled = ! empty( $settings['carousel_enabled'] ) ? $settings['carousel_enabled'] : false;
			$masonry_enabled  = ! empty( $settings['is_masonry'] ) ? $settings['is_masonry'] : false;

			if ( $masonry_enabled ) {

				// Force carousel disabling if masonry layout is active to avoid scripts duplicating
				$carousel_enabled = false;
			}

			return filter_var( $carousel_enabled, FILTER_VALIDATE_BOOLEAN );
		}

		/**
		 * Returns formatted data-attribute with masonry options
		 *
		 * @param  array $settings
		 * @return string
		 */
		public function get_masonry_options( $settings = array() ) {

			$options = apply_filters( 'jet-engine/listing/grid/masonry-options', array(
				'columns' => $this->get_columns_settings( $settings ),
			) );

			return sprintf( 'data-masonry-grid-options="%s"', htmlspecialchars( json_encode( $options ) ) );

		}

		/**
		 * Returns formatted slider options
		 *
		 * @param  array $settings
		 * @param  bool  $is_rtl
		 * @return string
		 */
		public function get_slider_options( $settings = array(), $is_rtl = false ) {

			$prev_arrow_icon = sprintf(
				'<i class="%1$s__slider-icon prev-arrow %2$s"></i>',
				$this->get_name(),
				$settings['arrow_icon']
			);

			$next_arrow_icon = sprintf(
				'<i class="%1$s__slider-icon next-arrow %2$s"></i>',
				$this->get_name(),
				$settings['arrow_icon']
			);

			$fade   = false;
			$effect = isset( $settings['effect'] ) ? $settings['effect'] : 'slide';
			if ( 1 === absint( $settings['columns'] ) && 'fade' === $effect ) {
				$fade = true;
			}

			$options = apply_filters( 'jet-engine/listing/grid/slider-options', array(
				'slidesToShow'   => $this->get_columns_settings( $settings ),
				'autoplaySpeed'  => absint( $settings['autoplay_speed'] ),
				'autoplay'       => filter_var( $settings['autoplay'], FILTER_VALIDATE_BOOLEAN ),
				'infinite'       => filter_var( $settings['infinite'], FILTER_VALIDATE_BOOLEAN ),
				'speed'          => absint( $settings['speed'] ),
				'arrows'         => filter_var( $settings['arrows'], FILTER_VALIDATE_BOOLEAN ),
				'dots'           => filter_var( $settings['dots'], FILTER_VALIDATE_BOOLEAN ),
				'slidesToScroll' => absint( $settings['slides_to_scroll'] ),
				'prevArrow'      => $prev_arrow_icon,
				'nextArrow'      => $next_arrow_icon,
				'rtl'            => $is_rtl,
				'itemsCount'     => $settings['items_count'],
				'fade'           => $fade,
			) );

			return htmlspecialchars( json_encode( $options ) );

		}

		/**
		 * Get posts number
		 *
		 * @param  array $settings
		 * @return int
		 */
		public function get_posts_num( $settings = array() ) {
			$posts_num = ! empty( $settings['posts_num'] ) ? absint( $settings['posts_num'] ) : 6;
			$is_random = isset( $settings['use_random_posts_num'] ) && filter_var( $settings['use_random_posts_num'], FILTER_VALIDATE_BOOLEAN );

			if ( $is_random ) {
				$max_posts_num = ! empty( $settings['max_posts_num'] ) ? absint( $settings['max_posts_num'] ) : 9;
				$posts_num     = rand( $posts_num, $max_posts_num );
			}

			return $posts_num;
		}

		/**
		 * Get columns settings.
		 *
		 * @param  array $settings
		 * @return array
		 */
		public function get_columns_settings( $settings = array() ) {
			$desktop_col = ! empty( $settings['columns'] ) ? absint( $settings['columns'] ) : 3;
			$tablet_col  = ! empty( $settings['columns_tablet'] ) ? absint( $settings['columns_tablet'] ) : $desktop_col;
			$mobile_col  = ! empty( $settings['columns_mobile'] ) ? absint( $settings['columns_mobile'] ) : $tablet_col;

			return array(
				'desktop' => $desktop_col,
				'tablet'  => $tablet_col,
				'mobile'  => $mobile_col,
			);
		}

	}

}
