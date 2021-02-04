<?php
namespace Jet_Engine\Modules\Custom_Content_Types;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Factory class
 */
class Factory {

	public $args   = array();
	public $fields = array();
	public $db     = null;
	public $page   = null;

	public $_admin_columns = null;
	private $_formatted_fields = null;

	public function __construct( $args = array(), $fields = array() ) {

		$fields = array_merge(
			$fields,
			Module::instance()->manager->data->get_service_fields( $args )
		);

		$sql_fields = Module::instance()->manager->data->get_sql_columns_from_fields( $fields );

		$this->db     = new DB( $args['slug'], $sql_fields );
		$this->args   = $args;
		$this->fields = $fields;

		if ( is_admin() ) {
			$this->page = new Type_Pages( $this );
			$this->page->init();
		}

	}

	/**
	 * Check if user is enabled to perform actions with current content type
	 *
	 * @return [type] [description]
	 */
	public function user_has_access() {
		return apply_filters(
			'jet-engine/custom-content-types/user-has-access',
			current_user_can( $this->get_arg( 'capability', 'manage_options' ) ),
			$this
		);
	}

	/**
	 * Prepare query arguments
	 *
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function prepare_query_args( $args ) {

		$prepared   = array();
		$all_fields = $this->get_formatted_fields();

		foreach ( $args as $key => $arg ) {

			if ( is_array( $arg ) && ! empty( $arg['field'] ) ) {

				$field_data = isset( $all_fields[ $arg['field'] ] ) ? $all_fields[ $arg['field'] ] : false;

				if ( $field_data ) {

					$type = isset( $arg['type'] ) ? $arg['type'] : 'auto';

					// Adjust default WP meta types to CCT
					switch ( $type ) {
						case 'NUMERIC':
						case 'DECIMAL':
							$type = 'integer';
							break;

						case 'DATETIME':
						case 'DATET':
							$type = 'timestamp';
							break;
					}

					if ( ! $type || 'auto' === $type ) {
						$type = $field_data['sql_type'];
					}

					$arg['type'] = $type;

				}

				$operator = ! empty( $arg['operator'] ) ? $arg['operator'] : '=';
				$value    = ! empty( $arg['value'] ) ? $arg['value'] : '';

				if ( ! is_array( $value ) ) {
					$value = preg_split( '/(?<=[^,]),[\s]?/', $value );
				}

				array_walk( $value, function( &$item ) {
					$item = str_replace( ',,', ',', $item );
				});

				if ( 1 === count( $value ) ) {
					$value = $value[0];
				}

				$arg['value'] = $value;

			}

			$prepared[ $key ] = $arg;

		}

		return $prepared;

	}

	/**
	 * Returns handler instance
	 *
	 * @param  [type] $action_key   [description]
	 * @param  array  $actions_list [description]
	 * @return [type]               [description]
	 */
	public function get_item_handler( $action_key = false, $actions_list = array() ) {

		if ( ! class_exists( '\Jet_Engine\Modules\Custom_Content_Types\Item_Handler' ) ) {
			require Module::instance()->module_path( 'item-handler.php' );
		}

		return new Item_Handler( $action_key, $actions_list, $this );

	}

	/**
	 * Returns formatted fields list
	 * @return [type] [description]
	 */
	public function get_formatted_fields() {

		if ( null === $this->_formatted_fields ) {

			$formatted_fields = array();
			$default          = array( array(
				'title'       => __( 'Item ID', 'jet-engine' ),
				'name'        => '_ID',
				'object_type' => 'field',
				'width'       => '100%',
				'type'        => 'number',
				'isNested'    => false,
				'is_required' => true,
			) );

			$all_fields = array_merge( $default, $this->fields );

			foreach ( $all_fields as $field ) {

				switch ( $field['type'] ) {

					case 'datetime':
					case 'datetime-local':

						if ( ! empty( $field['is_timestamp'] ) ) {
							$field['sql_type'] = 'timestamp';
						} else {
							$field['sql_type'] = 'date';
						}

						break;

					case 'date':
					case 'time':

						if ( ! empty( $field['is_timestamp'] ) ) {
							$field['sql_type'] = 'timestamp';
						} else {
							$field['sql_type'] = false;
						}

						break;

					case 'number':
					case 'media':
						$field['sql_type'] = 'integer';
						break;

					default:
						$field['sql_type'] = false;
						break;
				}

				$formatted_fields[ $field['name'] ] = $field;
			}

			$this->_formatted_fields = $formatted_fields;

		}

		return $this->_formatted_fields;
	}

	public function format_value_by_type( $field = null, $value = null ) {

		if ( ! $field ) {
			return $value;
		}

		$all_fields = $this->get_formatted_fields();

		if ( empty( $all_fields[ $field ] ) ) {
			return $value;
		}

		$data = $all_fields[ $field ];

		switch ( $data['type'] ) {

			case 'date':
				if ( ! empty( $data['is_timestamp'] ) ) {
					$format = get_option( 'date_format' );
					$value  = date_i18n( $format, $value );
				}
				break;

			case 'time':
				if ( ! empty( $data['is_timestamp'] ) ) {
					$format = get_option( 'time_format' );
					$value  = date_i18n( $format, $value );
				}
				break;

			case 'datetime':
			case 'datetime-local':
				if ( ! empty( $data['is_timestamp'] ) ) {
					$format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
					$value  = date_i18n( $format, $value );
				}
				break;

			case 'checkbox':

				if ( ! empty( $data['is_array'] )  ) {
					$value = implode( ', ', $value );
				} else {

					$value = array_filter( $value, function( $value_part ) {
						return filter_var( $value_part, FILTER_VALIDATE_BOOLEAN );
					} );

					if ( $value ) {
						$value = implode( ', ', array_keys( $value ) );
					}

				}

				break;

			case 'media':
				$value = wp_get_attachment_url( $value );
				break;

		}

		return $value;

	}

	/**
	 * Returns registered fields list
	 * @return [type] [description]
	 */
	public function get_fields_list( $context = 'plain', $where = 'elementor' ) {

		$fields        = $this->get_formatted_fields();
		$result        = array();
		$blocks_result = array();

		foreach ( $fields as $name => $field_data ) {

			$title = ! empty( $field_data['title'] ) ? $field_data['title'] : $name;

			if ( 'html' === $field_data['type'] ) {
				continue;
			}

			switch ( $context ) {

				case 'all':

					$result[ $name ] = $title;
					$blocks_result[] = array(
						'value' => $name,
						'label' => $title,
					);

					break;

				case 'plain':

					if ( 'repeater' !== $field_data['type'] ) {
						$result[ $name ] = $title;
						$blocks_result[] = array(
							'value' => $name,
							'label' => $title,
						);

					}

					break;

				case 'repeater':

					if ( 'repeater' === $field_data['type'] ) {
						$result[ $name ] = $title;
						$blocks_result[] = array(
							'value' => $name,
							'label' => $title,
						);
					}

					break;

				case 'media':

					if ( 'media' === $field_data['type'] ) {
						$result[ $name ] = $title;
						$blocks_result[] = array(
							'value' => $name,
							'label' => $title,
						);

					}

					break;

				case 'gallery':

					if ( 'gallery' === $field_data['type'] ) {
						$result[ $name ] = $title;
						$blocks_result[] = array(
							'value' => $name,
							'label' => $title,
						);
					}

					break;

			}
		}

		if ( 'blocks' === $where ) {
			return $blocks_result;
		} else {
			return $result;
		}

	}

	/**
	 * Returns DB instatnce
	 * @return [type] [description]
	 */
	public function get_db() {
		return $this->db;
	}

	/**
	 * Returns argument by key
	 *
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	public function get_arg( $key, $default = false ) {
		return isset( $this->args[ $key ] ) ? $this->args[ $key ] : $default;
	}

	public function get_admin_columns() {

		if ( null === $this->_admin_columns ) {

			$this->_admin_columns = array();

			$columns = isset( $this->args['admin_columns'] ) ? $this->args['admin_columns'] : array();

			if ( empty( $columns ) ) {
				return $this->_admin_columns;
			}

			$service_columns = Module::instance()->manager->get_service_fields( array(
				'add_id_field' => true,
				'has_single'   => $this->get_arg( 'has_single' ),
			) );

			$service_fields = array();

			foreach ( $service_columns as $index => $s_column ) {
				$s_column['order'] = absint( $index );
				$service_fields[ $s_column['name'] ] = $s_column;
			}

			// Ensure _ID column exists for the backward compatibility
			if ( ! isset( $columns['_ID'] ) ) {
				$columns['_ID'] = array(
					'enabled' => true,
					'prefix' => '#',
					'is_sortable' => true,
					'is_num' => true,
				);
			}

			foreach ( $columns as $name => $column ) {

				if ( empty( $column['enabled'] ) ) {
					continue;
				}

				$field = Module::instance()->manager->data->get_field_by_name( $name, $this->fields );

				if ( ! $field ) {
					
					$field = ! empty( $service_fields[ $name ] ) ? $service_fields[ $name ] : false;

					if ( $field ) {
						if ( 0 === $field['order'] ) {
							$field['order'] = -1;
						}
					}

				}

				if ( ! $field ) {
					continue;
				}

				$column['title'] = $field['title'];
				$column['order'] = $field['order'];

				switch ( $field['type'] ) {

					case 'date':
						if ( ! empty( $field['is_timestamp'] ) ) {
							$column['_cb'] = 'date_i18n';
							$column['date_format'] = get_option( 'date_format' );
						}
						break;

					case 'time':
						if ( ! empty( $field['is_timestamp'] ) ) {
							$column['_cb'] = 'date_i18n';
							$column['date_format'] = get_option( 'time_format' );
						}
						break;

					case 'datetime':
					case 'datetime-local':
						if ( ! empty( $field['is_timestamp'] ) ) {
							$column['_cb'] = 'date_i18n';
							$column['date_format'] = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
						}
						break;

					case 'media':
						$column['_cb']        = 'wp_get_attachment_image';
						$column['image_size'] = array( 50, 50 );
						break;

				}

				// Add service columns callbacks
				switch ( $name ) {
					case 'cct_author_id':
						$column['_cb'] = array( $this, 'get_item_author_link' );
						break;
					
					case 'cct_single_post_id':
						$column['_cb'] = array( $this, 'get_item_post_link' );
						break;
				}

				$this->_admin_columns[ $name ] = $column;

			}

			$this->_admin_columns = apply_filters(
				'jet-engine/custom-content-types/admin-columns',
				$this->_admin_columns,
				$this
			);

			$default_order = count( $this->_admin_columns );

			uasort( $this->_admin_columns, function( $a, $b ) use ( $default_order ) {

				$a_order = isset( $a['order'] ) ? intval( $a['order'] ) : $default_order;
				$b_order = isset( $b['order'] ) ? intval( $b['order'] ) : $default_order;

				if ( $a_order == $b_order ) {
					return 0;
				}
				
				return ( $a_order < $b_order ) ? -1 : 1;

			} );

		}

		return $this->_admin_columns;

	}

	public function get_item_post_link( $post_id ) {
		
		$title = get_the_title( $post_id );

		if ( current_user_can( 'edit_post', $post_id ) ) {
			$post_link = get_edit_post_link( absint( $post_id ), 'url' );
			return sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $post_link, $title );
		} else {
			return $title;
		}

	}

	public function get_item_author_link( $user_id ) {
		
		$user = get_userdata( $user_id );

		if ( current_user_can( 'edit_users' ) ) {
			return sprintf( '<a href="%1$s" target="_blank">%2$s</a>', get_edit_user_link( $user_id ), $user->data->user_login );
		} else {
			return $user->data->user_login;
		}

	}

	/**
	 * Return available statuses list
	 *
	 * @return [type] [description]
	 */
	public function get_statuses() {
		return array(
			'publish' => __( 'Publish', 'jet-engine' ),
			'draft'   => __( 'Draft', 'jet-engine' ),
		);
	}

	/**
	 * Maybe convert date value to timestamp
	 *
	 * @param  [type] $value [description]
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	public function maybe_to_timestamp( $value, $field ) {

		$input_type = ! empty( $field['input_type'] ) ? $field['input_type'] : false;

		if ( ! $input_type ) {
			$input_type = ! empty( $field['type'] ) ? $field['type'] : false;
		}

		switch ( $input_type ) {

			case 'date':
			case 'datetime':
			case 'datetime-local':

				if ( ! empty( $field['is_timestamp'] ) ) {
					$value = strtotime( $value );
				}
				break;

		}

		return $value;

	}

	/**
	 * Maybe convert date value from timestamp
	 *
	 * @param  [type] $value [description]
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	public function maybe_from_timestamp( $value, $field ) {

		$input_type = ! empty( $field['input_type'] ) ? $field['input_type'] : false;

		if ( ! $input_type ) {
			$input_type = ! empty( $field['type'] ) ? $field['type'] : $input_type;
		}

		switch ( $input_type ) {

			case 'date':
				if ( ! empty( $field['is_timestamp'] ) ) {
					$value = date( 'Y-m-d', $value );
				}
				break;

			case 'datetime':
			case 'datetime-local':
				if ( ! empty( $field['is_timestamp'] ) ) {
					$value = date( 'Y-m-d\TH:i', $value );
				}
				break;

		}

		return $value;

	}

}
