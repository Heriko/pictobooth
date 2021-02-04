<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Listings;

use Jet_Engine\Modules\Custom_Content_Types\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Query {

	public $source;
	public $items = array();

	/**
	 * Constructor for the class
	 */
	public function __construct( $source ) {

		$this->source = $source;

		add_filter(
			'jet-engine/listing/grid/query/' . $this->source,
			array( $this, 'query_items' ), 10, 3
		);

		add_filter(
			'jet-engine/listings/data/object-vars',
			array( $this, 'prepare_object_vars' ), 10
		);

		add_action(
			'the_post',
			array( $this, 'maybe_add_item_to_post' )
		);

		add_action( 'jet-engine/listings/frontend/reset-data', function( $data ) {
			if ( $this->source === $data->get_listing_source() ) {
				wp_reset_postdata();
			}
		} );

		add_filter( 'jet-engine/listings/macros-list', function( $macros_list ) {
			$macros_list['current_field'] = array( $this, 'get_current_field' );
			return $macros_list;
		} );

	}

	public function get_current_field( $field_value = null, $field_name = '_ID' ) {
		
		$current_object = jet_engine()->listings->data->get_current_object();

		if ( ! isset( $current_object->cct_slug ) ) {
			return null;
		}

		if ( ! $field_name ) {
			$field_name = '_ID';
		}

		$prop = $current_object->cct_slug . '__' . $field_name;

		return isset( $current_object->$prop ) ? $current_object->$prop : false;

	}

	public function get_current_item( $post_id = null, $post_type = null ) {

		if ( ! isset( $this->items[ $post_id ] ) ) {

			$content_type = Module::instance()->manager->get_content_type_for_post_type( $post_type );

			if ( ! $content_type ) {
				return false;
			}

			$slug = $content_type->get_arg( 'slug' );
			$item = Module::instance()->manager->get_item_for_post( $post_id, $content_type );

			if ( ! $item ) {
				return false;
			}

			$prepared_item = array();

			foreach ( $item as $key => $value ) {
				
				if ( 'cct_slug' !== $key ) {
					$prop = $slug . '__' . $key;
				} else {
					$prop = $key;
				}

				$prepared_item[ $prop ] = $value;
			}

			$this->items[ $post_id ] = $prepared_item;

		}

		return $this->items[ $post_id ];
	}

	/**
	 * Prepare appintmnet variables
	 */
	public function prepare_object_vars( $vars ) {

		if ( isset( $vars['cct_slug'] ) ) {

			$new_vars = array();

			foreach ( $vars as $key => $value ) {
				$new_vars[ $vars['cct_slug'] . '__' . $key ] = $value;
			}

			$vars = array_merge( $vars, $new_vars );

		} elseif ( ! empty( $vars['ID'] ) && ! empty( $vars['post_type'] ) ) {

			$post_id   = $vars['ID'];
			$post_type = $vars['post_type'];
			$item      = $this->get_current_item( $post_id, $post_type );

			if ( ! $item ) {
				return $vars;
			}

			$vars = array_merge( $vars, $item );

		}

		return $vars;

	}

	public function maybe_add_item_to_post( &$post ) {

		$post_id   = $post->ID;
		$post_type = $post->post_type;
		$item      = $this->get_current_item( $post_id, $post_type );

		if ( ! $item ) {
			return;
		}

		foreach ( $item as $prop => $value ) {
			$post->$prop = $value;
		}

	}

	public function query_items( $query, $settings, $widget ) {

		$widget->query_vars['page']    = 1;
		$widget->query_vars['pages']   = 1;
		$widget->query_vars['request'] = false;

		$type = jet_engine()->listings->data->get_listing_post_type();

		if ( ! $type ) {
			return $query;
		}

		$content_type = Module::instance()->manager->get_content_types( $type );

		if ( ! $content_type ) {
			return $query;
		}

		$page  = 1;
		$query = isset( $settings['jet_cct_query'] ) ? $settings['jet_cct_query'] : '{}';
		$query = json_decode( wp_unslash( $query ), true );

		if ( ! empty( $_REQUEST['action'] ) && 'jet_engine_ajax' === $_REQUEST['action'] && isset( $_REQUEST['query'] ) ) {
			$query = $_REQUEST['query'];
			$page  = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		}

		if ( ! empty( $_REQUEST['action'] ) && 'jet_smart_filters' === $_REQUEST['action'] ) {
			$page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
		}

		$order  = ! empty( $query['order'] ) ? $query['order'] : array();
		$args   = ! empty( $query['args'] ) ? $query['args'] : array();
		$offset = ! empty( $query['offset'] ) ? absint( $query['offset'] ) : 0;
		$status = ! empty( $query['status'] ) ? $query['status'] : '';
		$limit  = $widget->get_posts_num( $settings );

		$flag = \OBJECT;
		$content_type->db->set_format_flag( $flag );

		$filtered_query = apply_filters(
			'jet-engine/listing/grid/posts-query-args',
			array(),
			$widget,
			$settings
		);

		$args = $this->do_macros_in_args( $args );

		if ( $status ) {
			$args[] = array(
				'field'    => 'cct_status',
				'operator' => '=',
				'value'    => $status,
			);
		}

		if ( ! empty( $filtered_query['jet_smart_filters'] ) && ! empty( $filtered_query['meta_query'] ) ) {
			foreach ( $filtered_query['meta_query'] as $row ) {
				$args = $this->add_filter_row( $row, $args );
			}
		}

		$query_args = apply_filters(
			'jet-engine/custom-content-types/listing/query-args',
			$content_type->prepare_query_args( $args ),
			$settings
		);

		if ( false === $query_args ) {
			return array();
		}

		if ( 0 < $limit ) {
			$total = $content_type->db->count( $query_args );
			$widget->query_vars['pages'] = ceil( $total / $limit );

			if ( function_exists( 'jet_smart_filters' ) ) {

				$query_id = ! empty( $settings['_element_id'] ) ? $settings['_element_id'] : false;

				jet_smart_filters()->query->set_props(
					'jet-engine',
					array(
						'found_posts'   => $total,
						'max_num_pages' => $widget->query_vars['pages'],
						'page'          => $page,
					),
					$query_id
				);

			}
		}

		$widget->query_vars['request'] = array(
			'order'  => $order,
			'args'   => $query_args,
			'offset' => $offset,
		);

		if ( 1 < $page ) {
			$offset = $offset + ( $page - 1 ) * $limit;
		}

		return $content_type->db->query( $query_args, $limit, $offset, $order );

	}

	public function do_macros_in_args( $args = array() ) {

		$prepared_args = array();

		foreach ( $args as $arg ) {
			$arg['value'] = jet_engine()->listings->macros->do_macros( $arg['value'] );
			$prepared_args[] = $arg;
		}

		return $prepared_args;
	}

	public function add_filter_row( $row, $query ) {

		$row['field']    = $row['key'];
		$row['operator'] = $row['compare'];
		$found           = false;

		unset( $row['key'] );
		unset( $row['compare'] );

		foreach ( $query as $index => $query_row ) {
			if ( $row['field'] === $query_row['field'] ) {
				$query[ $index ] = $row;
				$found = true;
			}
		}

		if ( ! $found ) {
			$query[] = $row;
		}

		return $query;

	}

}
