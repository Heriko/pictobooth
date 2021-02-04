<?php
namespace Jet_Engine\Modules\Custom_Content_Types;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Item_Handler class
 */
class Item_Handler {

	private $factory;
	private $item_id;
	private $update_status = false;

	/**
	 * Constructor for the class
	 *
	 * @param [type] $action_key   [description]
	 * @param array  $actions_list [description]
	 */
	public function __construct( $action_key = null, $actions_list = array(), $factory ) {

		$actions_list = array_merge( array(
			'save'   => false,
			'delete' => false,
		), $actions_list );

		$this->factory = $factory;

		if ( $this->factory->page ) {
			$this->item_id = $this->factory->page->get_item_id();
		}

		if ( ! $action_key || empty( $actions_list ) ) {
			return;
		}

		switch ( $_GET[ $action_key ] ) {

			case $actions_list['save']:
				$this->save_item();
				break;

			case $actions_list['delete']:
				$this->delete_item();
				break;

		}

	}

	/**
	 * Process item deletion
	 * @param  [type] $item_id [description]
	 * @return [type]          [description]
	 */
	public function delete_item( $item_id = false, $redirect = true ) {

		if ( ! $item_id ) {
			$item_id = $this->item_id;
		}

		if ( ! $item_id ) {
			wp_die( 'Item ID not found in the request', 'Error' );
		}

		if ( ! $this->factory->user_has_access() ) {
			wp_die( 'You don`t have permissions to fo this', 'Error' );
		}

		$item = $this->factory->db->get_item( $item_id );

		if ( ! empty( $item['cct_single_post_id'] ) ) {
			wp_delete_post( absint( $item['cct_single_post_id'] ), true );
		}

		$this->factory->db->delete( array( '_ID' => $item_id ) );

		if ( $redirect ) {
			if ( $this->factory->page ) {
				wp_redirect( $this->factory->page->page_url( false ) );
				die();
			}
		}

	}

	/**
	 * Process item saving
	 *
	 * @param  [type] $item_id [description]
	 * @return [type]          [description]
	 */
	public function save_item( $item_id = false ) {

		if ( ! $item_id ) {
			$item_id = $this->item_id;
		}

		if ( ! $this->factory->user_has_access() ) {
			wp_die( 'You don`t have permissions to fo this', 'Error' );
		}

		if ( empty( $_POST['cct_nonce'] ) || ! wp_verify_nonce( $_POST['cct_nonce'], 'jet-cct-nonce' ) ) {
			wp_die( 'Your link is expired, please return to the previous page and try again', 'Error' );
		}

		$itemarr = $_POST;

		if ( $item_id ) {
			$itemarr['_ID'] = $item_id;
		}

		$item_id = $this->update_item( $itemarr );

		if ( ! $item_id ) {
			if ( $this->factory->page ) {
				wp_redirect( $this->factory->page->page_url( 'add', false, 'error' ) );
				die();
			}
		} elseif ( is_wp_error( $item_id ) ) {
			if ( $this->factory->page ) {
				wp_die( $item_id->get_error_message(), 'Error' );
			}
		}

		if ( $this->factory->page ) {
			wp_redirect( $this->factory->page->page_url( 'edit', $item_id, $this->update_status ) );
			die();
		}

	}

	/**
	 * Insert or update item
	 *
	 * @param  [type] $item [description]
	 * @return [type]       [description]
	 */
	public function update_item( $itemarr = array() ) {

		if ( empty( $itemarr ) ) {
			return false;
		}

		$fields  = $this->factory->get_formatted_fields();
		$item_id = ! empty( $itemarr['_ID'] ) ? absint( $itemarr['_ID'] ) : false;
		$item    = array();
		$prev_item = false;

		if ( $item_id ) {
			$prev_item = $this->factory->db->get_item( $item_id );
		}

		if ( $prev_item ) {
			$itemarr = wp_parse_args( $itemarr, $prev_item );
		}

		foreach ( $fields as $field_name => $field_data ) {

			if ( isset( $itemarr[ $field_name ] ) ) {
				$value = $itemarr[ $field_name ];
			} else {
				$value = ! empty( $field['default_val'] ) ? $field['default_val'] : '';
			}

			$value = $this->factory->maybe_to_timestamp( $value, $field_data );
			$type  = isset( $field_data['type'] ) ? $field_data['type'] : false;

			switch ( $type ) {
				case 'checkbox':

					if ( ! empty( $field_data['is_array'] ) ) {

						$raw    = ! empty( $value ) ? $value : array();
						$result = array();

						if ( ! is_array( $raw ) ) {
							$raw = array( $raw => 'true' );
						}

						foreach ( $raw as $raw_key => $raw_value ) {
							$bool_value = filter_var( $raw_value, FILTER_VALIDATE_BOOLEAN );
							if ( $bool_value ) {
								$result[] = $raw_key;
							}
						}

						$value = $result;

					} else {
						if ( ! is_array( $value ) ) {
							$value = array( $value => 'true' );
						}
					}

					break;

				case 'media':

					if ( empty( $value ) ) {
						$value = null;
					}

					break;
			}

			$item[ $field_name ] = $value;
		}

		if ( ! empty( $itemarr['cct_status'] ) ) {
			$status           = esc_attr( $itemarr['cct_status'] );
			$allowed_statuses = $this->factory->get_statuses();
			$status           = isset( $allowed_statuses[ $status ] ) ? $status : 'publish';
		} else {
			$status = 'publish';
		}

		$item['cct_status'] = $status;

		$has_single     = $this->factory->get_arg( 'has_single' );
		$single_post_id = false;

		if ( $item_id ) {

			if ( empty( $prev_item['cct_author_id'] ) ) {
				$item['cct_author_id'] = get_current_user_id();
			}

		}

		if ( $has_single ) {

			if ( $item_id && $prev_item ) {
				$single_post_id = isset( $prev_item['cct_single_post_id'] ) ? $prev_item['cct_single_post_id'] : false;
			}

			if ( ! $single_post_id ) {
				$single_post_id = $this->process_single_post( $item );
			}

			if ( $single_post_id ) {
				$item['cct_single_post_id'] = $single_post_id;
			}

		}

		if ( $item_id ) {

			$item['cct_modified'] = current_time( 'mysql' );

			if ( empty( $item['cct_created'] ) ) {
				unset( $item['cct_created'] );
			}

			$this->factory->db->update( $item, array( '_ID' => $item_id ) );

			$error               = $this->factory->db->get_errors();
			$this->update_status = 'updated';

			if ( $error ) {
				return new \WP_Error( 400, 'Database error. ' . $error . '. Please go to Content Type settings page and try to update current Content Type. If error still exists - please contact Crocoblock support' );
			}

		} else {

			$item['cct_author_id'] = get_current_user_id();
			$item['cct_created']   = current_time( 'mysql' );
			$item['cct_modified']  = $item['cct_created'];

			$item_id = $this->factory->db->insert( $item );
			$error   = $this->factory->db->get_errors();

			if ( ! $item_id ) {
				if ( ! $error ) {
					return false;
				} else {
					return new \WP_Error( 400, 'Database error. ' . $error . '. Please go to Content Type settings page and try to update current Content Type. If error still exists - please contact Crocoblock support' );
				}
			} elseif ( $error ) {
				return new \WP_Error( 400, 'Item was inserted, but Database error triggered. ' . $error . '. Please go to Content Type settings page and try to update current Content Type. If error still exists - please contact Crocoblock support' );
			}

			$this->update_status = 'added';

		}

		return $item_id;

	}

	/**
	 * Process single post
	 *
	 * @param  array  $item [description]
	 * @return [type]       [description]
	 */
	public function process_single_post( $item = array() ) {

		$post_id = ! empty( $item['cct_single_post_id'] ) ? absint( $item['cct_single_post_id'] ) : false;

		$post_type     = $this->factory->get_arg( 'related_post_type' );
		$title_field   = $this->factory->get_arg( 'related_post_type_title' );
		$content_field = $this->factory->get_arg( 'related_post_type_content' );

		if ( ! $post_type ) {
			return false;
		}

		$postarr = array(
			'post_type'   => $post_type,
			'post_status' => $item['cct_status'],
		);

		if ( $title_field ) {
			$postarr['post_title'] = isset( $item[ $title_field ] ) ? $item[ $title_field ] : '';
		}

		if ( $content_field ) {
			$postarr['post_content'] = isset( $item[ $content_field ] ) ? $item[ $content_field ] : '';
		}

		if ( $post_id ) {

			$post = get_post( $post_id );

			if ( ! $post || is_wp_error( $post ) ) {
				$post_id = wp_insert_post( $postarr );
			} else {
				$postarr['ID'] = $post_id;
				wp_update_post( $postarr );
			}

		} else {
			$post_id = wp_insert_post( $postarr );
		}

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		return $post_id;

	}

}
