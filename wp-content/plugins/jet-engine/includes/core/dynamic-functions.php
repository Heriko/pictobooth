<?php
/**
 * Dynamic functions class
 */

// If this file is called directly, abort.x
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Dynamic_Functions' ) ) {

	class Jet_Engine_Dynamic_Functions {

		private $functions_list = array();
		private $cache_group = 'jet_engine_dynamic_functions';

		public function __construct() {

			$this->functions_list = apply_filters( 'jet-engine/dynamic-functions/functions-list', array(
				'sum' => array(
					'label' => __( 'Summed value', 'jet-engine' ),
					'type'  => 'sql', // sql or raw
					'query' => 'SUM( CAST( meta_value AS DECIMAL( 10, %decimal_count% ) ) )',
					'cb'    => false,
					'custom_settings' => array(
						'decimal_count' => array(
							'label'   => __( 'Decimals Count', 'jet-engine' ),
							'type'    => 'number',
							'default' => 0,
							'min'     => 0,
							'max'     => 3,
						),
					)
				),
				'avg' => array(
					'label'           => __( 'Average value', 'jet-engine' ),
					'type'            => 'sql',
					'query'           => 'ROUND( AVG( CAST( meta_value AS DECIMAL( 10, %decimal_count% ) ) ), %decimal_count% )',
					'cb'              => false,
					'custom_settings' => array(
						'decimal_count' => array(
							'label'   => __( 'Decimals Count', 'jet-engine' ),
							'type'    => 'number',
							'default' => 0,
							'min'     => 0,
							'max'     => 3,
						),
					)
				),
				'count' => array(
					'label' => __( 'Count', 'jet-engine' ),
					'type'  => 'sql',
					'query' => 'COUNT(meta_value)',
					'cb'    => false,
				),
				'max' => array(
					'label' => __( 'Maximum value', 'jet-engine' ),
					'type'  => 'sql',
					'query' => 'MAX( CAST( meta_value AS DECIMAL( 10, %decimal_count% ) ) )',
					'cb'    => false,
					'custom_settings' => array(
						'decimal_count' => array(
							'label'   => __( 'Decimals Count', 'jet-engine' ),
							'type'    => 'number',
							'default' => 0,
							'min'     => 0,
							'max'     => 3,
						),
					)
				),
				'min' => array(
					'label' => __( 'Minimum value', 'jet-engine' ),
					'type'  => 'sql',
					'query' => 'MIN( CAST( meta_value AS DECIMAL( 10, %decimal_count% ) ) )',
					'cb'    => false,
					'custom_settings' => array(
						'decimal_count' => array(
							'label'   => __( 'Decimals Count', 'jet-engine' ),
							'type'    => 'number',
							'default' => 0,
							'min'     => 0,
							'max'     => 3,
						),
					)
				),
				 
			) );

		}

		/**
		 * Returns functions list for options
		 */
		public function functions_list() {
			
			$result = array();

			foreach ( $this->functions_list as $func_key => $func_data ) {
				$result[ $func_key ] = ! empty( $func_data['label'] ) ? $func_data['label'] : $func_key;
			}

			return $result;

		}

		/**
		 * Allow to register custom settings to each function
		 */
		public function register_custom_settings( $tag ) {

			$controls = array();

			foreach ( $this->functions_list as $func_name => $data ) {
				
				if ( empty( $data['custom_settings'] ) ) {
					continue;
				}

				foreach ( $data['custom_settings'] as $setting_key => $setting_data ) {

					if ( empty( $controls[ $setting_key ] ) ) {
						
						$setting_data['condition'] = array(
							'function_name' => array( $func_name ),
						);
					
						$controls[ $setting_key ] = $setting_data;
					} else {
						$controls[ $setting_key ]['condition']['function_name'][] = $func_name;
					}

				}

			}

			if ( ! empty( $controls ) ) {
				foreach ( $controls as $control_key => $control_data ) {
					$tag->add_control( $control_key, $control_data );
				}
			}

		}

		/**
		 * Allow to get custom settings to each function
		 */
		public function get_custom_settings( $function, $tag ) {

			$settings = array();

			if ( empty( $this->functions_list[ $function ] ) || empty( $this->functions_list[ $function ]['custom_settings'] ) ) {
				return $settings;
			}

			foreach ( $this->functions_list[ $function ]['custom_settings'] as $setting_key => $setting_data ) {
				$settings[ $setting_key ] = $tag->get_settings( $setting_key );
			}

			return $settings;

		}

		/**
		 * Call function by function name and arguments
		 */
		public function call_function( $function_name = null, $data_source = array(), $field_name = null, $custom_settings = array() ) {

			if ( empty( $this->functions_list[ $function_name ] ) ) {
				return null;
			}

			$func_data = $this->functions_list[ $function_name ];

			$func_data['function']        = $function_name;
			$func_data['data_source']     = $data_source;
			$func_data['field_name']      = $field_name;
			$func_data['custom_settings'] = $custom_settings;

			if ( ! empty( $func_data['type'] ) && 'sql' === $func_data['type'] ) {
				return $this->call_sql_function( $func_data );
			} else {
				return $this->call_raw_function( $func_data );
			}

		}

		/**
		 * Call plain PHP function
		 */
		public function call_raw_function( $func_data ) {
			
			$function = ! empty( $func_data['cb'] ) ? $func_data['cb'] : false;

			if ( ! $function || ! is_callable( $function ) ) {
				return null;
			}

			unset( $func_data['cb'] );

			return call_user_func( $function, $func_data );

		}

		/**
		 * Call sql-query function
		 */
		public function call_sql_function( $data ) {

			$query = ! empty( $data['query'] ) ? $data['query'] : false;
			$where = ! empty( $data['where'] ) ? $data['where'] : false;
			$data_source = ! empty( $data['data_source'] ) ? $data['data_source'] : false;
			$field_name = ! empty( $data['field_name'] ) ? $data['field_name'] : false;

			if ( ! $field_name || ! $query ) {
				return null;
			}

			$table = false;

			global $wpdb;

			$source = ! empty( $data_source['source'] ) ? $data_source['source'] : 'post_meta';

			switch ( $source ) {
				
				case 'post_meta':
					
					$table = $wpdb->postmeta;
					break;

				case 'term_meta':
					$table = $wpdb->termmeta;
					break;

				case 'user_meta':
					$table = $wpdb->usermeta;
					break;
				
				default:
					$table = apply_filters( 'jet-engine/dynamic-functions/custom-sql-table', $table, $data );
					break;
			}

			if ( ! $table ) {
				return null;
			}

			if ( ! $where ) {
				$where = $wpdb->prepare( "WHERE $table.meta_key ='%s'", $field_name );
			}

			if ( ! empty( $data['custom_settings'] ) ) {
				foreach ( $data['custom_settings'] as $key => $value ) {
					$query = str_replace( '%' . $key . '%', $value, $query );
					$where = str_replace( '%' . $key . '%', $value, $where );
				}
			}

			$posts_table = $wpdb->posts;
			$posts_query_join = " INNER JOIN $posts_table ON $table.post_id = $posts_table.ID ";

			$posts_join = "";
			$posts_where = "";

			if ( ! empty( $data_source['post_status'] ) ) {

				$posts_join = $posts_query_join;
				$posts_where .= " AND $posts_table.post_status";

				if ( 1 === count( $data_source['post_status'] ) ) {
					$status = $data_source['post_status'][0];
					$posts_where .= " = '$status'";
				} else {
					
					$statuses = array();
					
					foreach ( $data_source['post_status'] as $status ) {
						$statuses[] = sprintf( "'%s'", $status );
					}

					$statuses = implode( ', ', $statuses );
					$posts_where .= " IN ($statuses)";
				}

			}

			if ( ! empty( $data_source['post_types'] ) ) {
				
				if ( ! $posts_join ) {
					$posts_join = $posts_query_join;
				}

				$posts_where .= " AND $posts_table.post_type";

				if ( 1 === count( $data_source['post_types'] ) ) {
					$type = $data_source['post_types'][0];
					$posts_where .= " = '$type'";
				} else {
					
					$types = array();
					
					foreach ( $data_source['post_types'] as $type ) {
						$types[] = sprintf( "'%s'", $type );
					}

					$types = implode( ', ', $types );
					$posts_where .= " IN ($types)";
				}

			}

			$final_query = "SELECT $query FROM $table $posts_join $where $posts_where;";

			if ( 'post_meta' === $source ) {

				switch ( $data_source['context'] ) {
				
					case 'current_term':
						
						$term_id = $this->get_current_term_id( $data_source );

						if ( $term_id ) {
							$term_relationships = $wpdb->term_relationships;
							$final_query = "SELECT $query FROM $table $posts_join INNER JOIN $term_relationships ON $table.post_id = $term_relationships.object_id $where AND $term_relationships.term_taxonomy_id = $term_id $posts_where";
						}

						break;
					
					case 'current_user':
						
						$user_id = get_current_user_id();

						if ( $user_id ) {
							$posts = $wpdb->posts;
							$final_query = "SELECT $query FROM $table INNER JOIN $posts ON $table.post_id = $posts.ID $where AND $posts.post_author = $user_id $posts_where";
						}

						break;

					case 'queried_user':
						
						$user_id = false;

						if ( ! empty( $data_source['context_user_id'] ) ) {
				
							$user = $data_source['context_user_id'];

							if ( false === strpos( $user, '::' ) ) {
								$user_id = absint( $user );
							} else {
								
								$user_data = explode( '::', $user );

								if ( ! empty( $user_data[0] ) && ! empty( $user_data[1] ) ) {
									$user_obj = get_user_by( $user_data[0], $user_data[1] );
								}

								if ( $user_obj ) {
									$user_id = $user_obj->ID;
								}

							}

						} else {
							if ( is_author() ) {
								$user_id = get_queried_object_id();
							} elseif ( jet_engine()->modules->is_module_active( 'profile-builder' ) ) {
								$user_id = \Jet_Engine\Modules\Profile_Builder\Module::instance()->query->get_queried_user_id();
							}
						}

						if ( ! $user_id ) {
							$user_id = get_current_user_id();
						}

						if ( $user_id ) {
							$posts = $wpdb->posts;
							$final_query = "SELECT $query FROM $table INNER JOIN $posts ON $table.post_id = $posts.ID $where AND $posts.post_author = $user_id $posts_where";
						}

						break;
				}

			}

			$hash = md5( $final_query );

			$result = wp_cache_get( $hash, $this->cache_group );

			if ( ! $result ) {
				$result = $wpdb->get_var( $final_query );
				wp_cache_set( $hash, $result, $this->cache_group );
			}

			return $result;

		}

		/**
		 * Get term ID for current context and settings
		 */
		public function get_current_term_id( $data_source ) {

			if ( ! empty( $data_source['context_tax_term'] ) ) {
				
				$term = $data_source['context_tax_term'];
				$term_id = false;

				if ( false === strpos( $term, 'slug::' ) ) {
					$term_id = absint( $term );
				} elseif ( ! empty( $data_source['context_tax'] ) ) {
					$term = str_replace( 'slug::', '', $term );
					$term_obj = get_term_by( 'slug', $term, $data_source['context_tax'] );

					if ( $term_obj ) {
						$term_id = $term_obj->term_id;
					}

				}

				if ( $term_id ) {
					return $term_id;
				}

			}

			$listing_object = jet_engine()->listings->data->get_current_object();
			$term_id = false;

			if ( $listing_object && 'WP_Term' === get_class( $listing_object ) ) {
				$term_id = $listing_object->term_id;
			} else {
				$queried_object = get_queried_object();

				if ( $queried_object && isset( $queried_object->term_id ) ) {
					$term_id = $queried_object->term_id;
				} elseif ( $listing_object && 'WP_Post' === get_class( $listing_object ) && ! empty( $data_source['context_tax'] ) ) {
					$post_id = $listing_object->ID;
					$terms = wp_get_post_terms( $post_id, $data_source['context_tax'], array( 'fields' => 'ids' ) );

					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
						$term_id = $terms[0];
					}

				}

			}

			return $term_id;

		}

	}

}
