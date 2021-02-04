<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Frontend {

	private $template_id = null;

	/**
	 * Constructor for the class
	 */
	public function __construct() {

		$this->check_admin_area_access();

		add_action( 'jet-engine/profile-builder/query/setup-props', array( $this, 'add_template_filter' ) );
		add_filter( 'jet-engine/listings/dynamic-link/custom-url', array( $this, 'dynamic_link_url' ), 10, 2 );
		add_filter( 'jet-engine/listings/dynamic-image/custom-url', array( $this, 'dynamic_link_url' ), 10, 2 );

	}

	/**
	 * Check if is admin area request and its accessible by current user
	 */
	public function check_admin_area_access() {

		if ( ! is_admin() ) {
			return;
		}

		$restrict = Module::instance()->settings->get( 'restrict_admin_access' );

		if ( ! $restrict ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$accessible_roles = Module::instance()->settings->get( 'admin_access_roles' );

		if ( empty( $accessible_roles ) ) {
			$accessible_roles = array();
		}

		$accessible_roles[] = 'administrator';

		$user = wp_get_current_user();
		$user_roles = ( array ) $user->roles;

		$res = false;

		foreach ( $user_roles as $role ) {
			if ( in_array( $role, $accessible_roles ) ) {
				$res = true;
			}
		}

		if ( ! $res ) {

			$account_page = Module::instance()->settings->get( 'account_page' );

			if ( $account_page ) {
				wp_redirect( get_permalink( $account_page ) );
			} else {
				wp_redirect( home_url( '/' ) );
			}

			die();

		}

	}

	/**
	 * Enqueue page template CSS
	 *
	 * @return [type] [description]
	 */
	public function enqueue_template_css() {

		if ( ! $this->template_id ) {
			return;
		}

		\Elementor\Plugin::instance()->frontend->enqueue_styles();

		$css_file = new \Elementor\Core\Files\CSS\Post( $this->template_id );
		$css_file->enqueue();

	}

	/**
	 * Render profile page content
	 *
	 * @return [type] [description]
	 */
	public function render_page_content() {

		if ( ! $this->template_id ) {
			return;
		}

		$settings = Module::instance()->settings->get();
		$template_mode = Module::instance()->settings->get( 'template_mode' );

		if ( 'rewrite' === $template_mode && ! empty( $settings['force_template_rewrite'] ) ) {

			global $post;

			if ( $this->template_id !== get_the_ID() ) {
				$template = get_post( $this->template_id );
				$tmp      = $post;
				$post     = $template;
			} else {
				$template = $post;
			}

			echo apply_filters( 'the_content', $template->post_content );

			if ( $this->template_id !== get_the_ID() ) {
				$post = $tmp;
			}

		} else {
			echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $this->template_id );
		}

	}

	/**
	 * Replace default content
	 * @return [type] [description]
	 */
	public function add_template_filter() {

		$settings   = Module::instance()->settings->get();
		$add        = false;
		$structure  = false;
		$has_access = $this->check_user_access();
		$subapge    = Module::instance()->query->get_subpage_data();

		if ( ! $has_access['access'] && ! empty( $has_access['template'] ) ) {
			$this->template_id = $has_access['template'];
		} else {

			$this->template_id = ! empty( $subapge['template'] ) ? $subapge['template'][0] : false;

			if ( ! $this->template_id && ! empty( $settings['force_template_rewrite'] ) ) {
				$this->template_id = get_the_ID();
			}
		}

		if ( $this->template_id ) {
			add_filter( 'template_include', array( $this, 'set_page_template' ), 99999 );
			add_action( 'jet-engine/profile-builder/template/main-content', array( $this, 'render_page_content' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_css' ) );
		}

	}

	/**
	 * Check if current user hass access to current page
	 *
	 * @return [type] [description]
	 */
	public function check_user_access() {

		$result = array(
			'access'   => true,
			'template' => null,
		);

		if ( ! Module::instance()->query->is_account_page() ) {
			return $result;
		}

		if ( is_user_logged_in() ) {
			return $result;
		}

		$action = Module::instance()->settings->get( 'not_logged_in_action', 'login_redirect' );

		switch ( $action ) {

			case 'login_redirect':
				wp_redirect( wp_login_url( get_permalink() ), 303 );
				die();

			case 'page_redirect':

				$page_url = Module::instance()->settings->get( 'not_logged_in_redirect', home_url( '/' ) );
				$page_url = add_query_arg(
					apply_filters( 'jet-engine/profile-builder/not-logged-rediret-query-args', array(
						'redirect_to' => get_permalink(),
					) ),
					esc_url( $page_url )
				);

				wp_redirect( $page_url, 303 );
				die();

		}

		$template_id        = Module::instance()->settings->get( 'not_logged_in_template' );
		$result['access']   = false;
		$result['template'] = ! empty( $template_id ) ? $template_id[0] : false;

		return apply_filters( 'jet-engine/profile-builder/check-user-access', $result, Module::instance() );

	}

	/**
	 * Rewrite template
	 *
	 * @param [type] $template [description]
	 */
	public function set_page_template( $template ) {

		$template_mode = Module::instance()->settings->get( 'template_mode' );

		if ( 'rewrite' === $template_mode ) {
			$template = jet_engine()->get_template( 'profile-builder/page.php' );
		}

		return $template;
	}

	/**
	 * Dynamic link URL
	 *
	 * @param  boolean $url      [description]
	 * @param  array   $settings [description]
	 * @return [type]            [description]
	 */
	public function dynamic_link_url( $url = false, $settings = array() ) {

		$link_source = isset( $settings['dynamic_link_source'] ) ? $settings['dynamic_link_source'] : false;

		if ( ! $link_source ) {
			$link_source = isset( $settings['image_link_source'] ) ? $settings['image_link_source'] : false;
		}

		if ( $link_source && 'profile_page' === $link_source && ! empty( $settings['dynamic_link_profile_page'] ) ) {

			$profile_page = $settings['dynamic_link_profile_page'];
			$profile_page = explode( '::', $profile_page );

			if ( 1 < count( $profile_page ) ) {
				$url = Module::instance()->settings->get_subpage_url( $profile_page[1], $profile_page[0] );
			}

		}

		return $url;
	}

	/**
	 * Render profile menu
	 *
	 * @param  array  $settings [description]
	 * @return [type]           [description]
	 */
	public function profile_menu( $args = array(), $echo = true ) {

		$args = wp_parse_args( $args, array(
			'menu_context'       => 'account_page',
			'menu_layout'        => 'horizontal',
			'menu_layout_mobile' => 'vertical',
			'custom_class'       => '',
		) );

		$settings = Module::instance()->settings->get();

		switch ( $args['menu_context'] ) {
			case 'user_page':
				$page  = 'single_user_page';
				$items = ! empty( $settings['user_page_structure'] ) ? $settings['user_page_structure'] : array();
				break;

			default:
				$page  = 'account_page';
				$items = ! empty( $settings['account_page_structure'] ) ? $settings['account_page_structure'] : array();
				break;
		}

		$items = apply_filters( 'jet-engine/profile-builder/render/profile-menu-items', $items, $args );

		if ( empty( $items ) ) {
			return;
		}

		$base_class = 'jet-profile-menu';
		$classes    = array(
			$base_class,
			'layout--' . $args['menu_layout'],
			'layout-tablet--' . $args['menu_layout_tablet'],
			'layout-mobile--' . $args['menu_layout_mobile'],
			'context--' . $args['menu_context']
		);

		if ( ! empty( $args['custom_class'] ) ) {
			$classes[] = $args['custom_class'];
		}

		ob_start();

		do_action( 'jet-engine/profile-builder/render/before-profile-menu', $args );

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

		foreach ( $items as $index => $item ) {

			if ( ! Module::instance()->query->is_subpage_visible( $item ) ) {
				continue;
			}

			if ( ! empty( $item['hide'] ) ) {
				continue;
			}

			do_action( 'jet-engine/profile-builder/render/before-profile-menu-item', $item, $args );

			$slug = ( 0 < $index ) ? $item['slug'] : null;

			$item_html = sprintf(
				'<div class="%3$s__item %4$s"><a class="%3$s__item-link" href="%2$s">%1$s</a></div>',
				$item['title'],
				Module::instance()->settings->get_subpage_url( $slug, $page ),
				$base_class,
				( Module::instance()->query->is_subpage_now( $slug ) ? 'is-active' : '' )
			);

			echo apply_filters( 'jet-engine/profile-builder/render/profile-menu-item', $item_html, $item, $args );

			do_action( 'jet-engine/profile-builder/render/after-profile-menu-item', $item, $args );

		}

		echo '</div>';

		do_action( 'jet-engine/profile-builder/render/after-profile-menu', $args );

		$result = ob_get_clean();

		if ( $echo ) {
			echo $result;
		} else {
			return $result;
		}

	}

}
