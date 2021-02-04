<?php
namespace Jet_Engine\Modules\Maps_Listings;

class Module {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public $slug = 'maps-listings';

	public $settings;

	/**
	 * Constructor for the class
	 */
	public function __construct() {
		add_action( 'jet-engine/init', array( $this, 'init' ), 20 );
		add_action( 'jet-engine/rest-api/init-endpoints', array( $this, 'init_rest' ) );
	}

	/**
	 * Init module components
	 *
	 * @return [type] [description]
	 */
	public function init() {

		require jet_engine()->modules->modules_path( 'maps-listings/inc/settings.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/elementor-integration.php' );
		require jet_engine()->modules->modules_path( 'maps-listings/inc/lat-lng.php' );

		$this->settings = new Settings();
		$this->lat_lng  = new Lat_Lng();

		new Elementor_Integration();

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		add_action( 'jet-smart-filters/providers/register', array( $this, 'register_filters_provider' ) );

	}

	/**
	 * Register custom provider for SmartFilters
	 *
	 * @return [type] [description]
	 */
	public function register_filters_provider( $providers_manager ) {
		$providers_manager->register_provider(
			'\Jet_Engine\Modules\Maps_Listings\Filters_Provider',
			jet_engine()->modules->modules_path( 'maps-listings/inc/filters-provider.php' )
		);
	}

	/**
	 * Initialize REST API endpoints
	 *
	 * @return void
	 */
	public function init_rest( $api_manager ) {

		require jet_engine()->modules->modules_path( 'maps-listings/inc/rest/get-map-marker-info.php' );
		$api_manager->register_endpoint( new Get_Map_Marker_Info() );

	}

	/**
	 * Register module scripts
	 *
	 * @return [type] [description]
	 */
	public function register_scripts() {

		$depends      = array( 'jquery' );
		$api_disabled = $this->settings->get( 'disable_api_file' );

		if ( ! $api_disabled ) {

			wp_register_script(
				'jet-engine-google-maps-api',
				add_query_arg(
					array( 'key' => $this->settings->get( 'api_key' ), ),
					'https://maps.googleapis.com/maps/api/js'
				),
				false,
				false,
				true
			);

			$depends[] = 'jet-engine-google-maps-api';

		}

		wp_register_script(
			'jet-markerclustererplus',
			jet_engine()->plugin_url( 'assets/lib/markerclustererplus/markerclustererplus.min.js' ),
			array(),
			jet_engine()->get_version(),
			true
		);

		wp_register_script(
			'jet-maps-listings',
			jet_engine()->plugin_url( 'assets/js/frontend-maps.js' ),
			$depends,
			jet_engine()->get_version(),
			true
		);

	}

	/**
	 * Get render instance.
	 *
	 * @param  array $settings
	 * @return Jet_Listing_Render_Calendar
	 */
	public function get_render_instance( $settings ) {

		if ( ! class_exists( '\Jet_Engine_Render_Base' ) ) {
			require jet_engine()->plugin_path( 'includes/components/listings/render/base.php' );
		}

		if ( ! class_exists( '\Jet_Engine_Render_Listing_Grid' ) ) {
			require jet_engine()->plugin_path( 'includes/components/listings/render/listing-grid.php' );
		}

		if ( ! class_exists( '\Jet_Engine\Modules\Maps_Listings\Render' ) ) {
			require jet_engine()->modules->modules_path( 'maps-listings/inc/render.php' );
		}

		return new Render( $settings );
	}

	/**
	 * Get action url for open map popup
	 *
	 * @param  null $specific_post_id
	 * @param  null $event
	 * @return string
	 */
	public function get_action_url( $specific_post_id = null, $event = null ) {
		$post_id = ! empty( $specific_post_id ) ? $specific_post_id : get_the_ID();
		$event   = ! empty( $event ) ? $event : 'click';

		$args = array(
			'id'    => $post_id,
			'event' => $event,
		);

		return jet_engine()->frontend->get_custom_action_url( 'open_map_listing_popup', $args );
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
