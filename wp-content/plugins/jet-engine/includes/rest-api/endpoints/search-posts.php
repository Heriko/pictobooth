<?php
/**
 * Add/Update post type endpoint
 */

class Jet_Engine_Rest_Search_Posts extends Jet_Engine_Base_API_Endpoint {

	/**
	 * Returns route name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'search-posts';
	}

	/**
	 * API callback
	 *
	 * @return void
	 */
	public function callback( $request ) {

		$params    = $request->get_params();
		$query     = $params['query'];
		$ids       = $params['ids'];
		$post_type = $params['post_type'];

		if ( ! empty( $ids ) ) {
			$ids = explode( ',', $ids );
		}

		if ( ! empty( $post_type ) ) {
			$post_type = explode( ',', $post_type );
		}

		add_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10, 2 );

		$args = array(
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		);

		if ( $query ) {
			$args['s']       = $query;
			$args['s_title'] = $query;
		}

		if ( ! empty( $ids ) ) {
			$args['post__in'] = $ids;
		}

		if ( ! empty( $post_type ) ) {
			$args['post_type'] = $post_type;
		}

		$posts = get_posts( $args );

		remove_filter( 'posts_where', array( $this, 'force_search_by_title' ), 10, 2 );

		$result = array();

		foreach ( $posts as $post ) {
			$result[] = array(
				'value' => (string) $post->ID,
				'label' => $post->post_title,
			);
		}

		return rest_ensure_response( $result );

	}

	/**
	 * Force query to look in post title while searching
	 *
	 * @return [type] [description]
	 */
	public function force_search_by_title( $where, $query ) {

		$args = $query->query;

		if ( ! isset( $args['s_title'] ) ) {
			return $where;
		} else {
			global $wpdb;

			$searh = esc_sql( $wpdb->esc_like( $args['s_title'] ) );
			$where .= " AND {$wpdb->posts}.post_title LIKE '%$searh%'";

		}

		return $where;
	}

	/**
	 * Returns endpoint request method - GET/POST/PUT/DELTE
	 *
	 * @return string
	 */
	public function get_method() {
		return 'GET';
	}

	/**
	 * Check user access to current end-popint
	 *
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns arguments config
	 *
	 * @return array
	 */
	public function get_args() {
		return array(
			'query' => array(
				'default'  => '',
				'required' => false,
			),
			'ids' => array(
				'default'  => '',
				'required' => false,
			),
			'post_type' => array(
				'default'  => 'any',
				'required' => false,
			),
		);
	}

}
