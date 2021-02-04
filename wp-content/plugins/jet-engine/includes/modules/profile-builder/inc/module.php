<?php
namespace Jet_Engine\Modules\Profile_Builder;

class Module {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public $slug = 'profile-builder';

	public $settings;
	public $rewrite;
	public $query;
	public $frontend;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'jet-engine/init', array( $this, 'init' ), 20 );
	}

	/**
	 * Init module components
	 *
	 * @return [type] [description]
	 */
	public function init() {

		require jet_engine()->modules->modules_path( 'profile-builder/inc/settings.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/rewrite.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/query.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/frontend.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/elementor-integration.php' );
		require jet_engine()->modules->modules_path( 'profile-builder/inc/blocks-integration.php' );

		$this->settings = new Settings();
		$this->rewrite  = new Rewrite();
		$this->query    = new Query();
		$this->frontend = new Frontend();

		new Elementor_Integration();
		new Blocks_Integration();

		$this->maybe_disable_admin_bar();

		if ( jet_engine()->modules->is_module_active( 'booking-forms' ) ) {
			require jet_engine()->modules->modules_path( 'profile-builder/inc/forms-integration.php' );
			new Forms_Integration();
		}

		add_action( 'jet-engine/modules/dynamic-visibility/conditions/register', function( $conditions_manager ) {
			require jet_engine()->modules->modules_path( 'profile-builder/inc/dynamic-visibility/can-add-posts.php' );
			$conditions_manager->register_condition( new Dynamic_Visibility\User_Can_Add_Posts() );
		} );

	}

	public function get_restrictions_handler() {
		require_once jet_engine()->modules->modules_path( 'profile-builder/inc/restrictions.php' );
		return Restrictions::instance();
	}

	/**
	 * Check settings and maybe disable admin bar for non-admins
	 *
	 * @return [type] [description]
	 */
	public function maybe_disable_admin_bar() {

		$disable_admin_bar = $this->settings->get( 'disable_admin_bar' );

		if ( $disable_admin_bar && ! current_user_can( 'manage_options' ) && ! is_admin() ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}

	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

}
