<?php
namespace Jet_Engine\Modules\Custom_Content_Types;

class Module {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public $slug = 'custom-content-types';
	public $manager = null;
	public $listings = null;
	public $export = null;

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

		require_once $this->module_path( 'db.php' );
		require_once $this->module_path( 'manager.php' );
		require_once $this->module_path( 'listings/manager.php' );

		$this->manager  = new Manager();
		$this->listings = new Listings\Manager();

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			require_once $this->module_path( 'elementor/manager.php' );
			new Elementor\Manager();
		}

		add_action(
			'jet-engine/rest-api/init-endpoints',
			array( $this->query_dialog(), 'register_api_endpoint' )
		);

		if ( jet_engine()->modules->is_module_active( 'booking-forms' ) ) {
			require_once $this->module_path( 'forms/notification.php' );
			require_once $this->module_path( 'forms/preset.php' );
			new Forms\Notification();
			new Forms\Preset();
		}

		if ( jet_engine()->modules->is_module_active( 'data-stores' ) ) {
			require_once $this->module_path( 'data-stores/manager.php' );
			new Data_Stores\Manager();
		}

		if ( is_admin() ) {
			require_once $this->module_path( 'export.php' );
			$this->export = new Export();
		}

	}

	public function query_dialog() {

		if ( ! class_exists( '\Jet_Engine\Modules\Custom_Content_Types\Query_Dialog' ) ) {
			require_once $this->module_path( 'query-dialog.php' );
		}

		return Query_Dialog::instance();

	}

	/**
	 * Return path inside module
	 *
	 * @param  string $relative_path [description]
	 * @return [type]                [description]
	 */
	public function module_path( $relative_path = '' ) {
		return jet_engine()->modules->modules_path( 'custom-content-types/inc/' . $relative_path );
	}

	/**
	 * Return url inside module
	 *
	 * @param  string $relative_path [description]
	 * @return [type]                [description]
	 */
	public function module_url( $relative_path = '' ) {
		return jet_engine()->plugin_url( 'includes/modules/custom-content-types/inc/' . $relative_path );
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
