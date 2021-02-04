<?php
namespace Jet_Engine\Modules\Data_Stores;

class Module {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public $slug = 'data-stores';
	public $stores;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Init module components
	 *
	 * @return [type] [description]
	 */
	public function init() {

		if ( ! class_exists( '\Jet_Engine_Base_Data' ) ) {
			require_once jet_engine()->plugin_path( 'includes/base/base-data.php' );
		}

		require_once jet_engine()->modules->modules_path( 'data-stores/inc/data.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/settings.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/elementor-integration.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/blocks-integration.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/query.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/render-links.php' );
		require_once jet_engine()->modules->modules_path( 'data-stores/inc/stores/manager.php' );

		$this->data                  = new Data( $this );
		$this->settings              = new Settings();
		$this->stores                = new Stores\Manager();
		$this->elementor_integration = new Elementor_Integration();
		$this->blocks_integration    = new Blocks_Integration();
		$this->render                = new Render_Links();

		new Query();

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
