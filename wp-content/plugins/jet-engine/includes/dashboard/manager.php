<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Dashboard' ) ) {

	/**
	 * Define Jet_Engine_Dashboard class
	 */
	class Jet_Engine_Dashboard {

		public $builder       = null;
		public $skins_manager = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'register_main_menu_page' ), 10 );
			add_action( 'admin_init', array( $this, 'init_components' ), 99 );
		}

		/**
		 * Register menu page
		 *
		 * @return void
		 */
		public function register_main_menu_page() {

			add_menu_page(
				__( 'JetEngine', 'jet-engine' ),
				__( 'JetEngine', 'jet-engine' ),
				'manage_options',
				jet_engine()->admin_page,
				array( $this, 'render_page' ),
				'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCAyOTUuMzI5IDI5NS4zMjkiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTI5MS40MiAxNDIuMzhsLTMzLjE0LTI1LjE2Yy0yLjk5Ny0yLjI3NS03LjAxLTIuNjYxLTEwLjM4My0wLjk4LTMuMzYyIDEuNjY2LTUuNDkyIDUuMTAxLTUuNDkyIDguODYxdjE1LjM5NWgtOS44MDN2LTE1LjU1OWMwLTUuNDY1LTQuNDMtOS44OTYtOS44OTMtOS44OTZoLTE5LjY5NnYtMzYuNzQzYzAtNS40NjYtNC40MzEtOS44OTYtOS44OTQtOS44OTZoLTMwLjc3M3YtMTAuNTAyaDguMjg0YzUuNDY0IDAgOS44OTUtNC40MzEgOS44OTUtOS44OTVzLTQuNDMxLTkuODkzLTkuODk1LTkuODkzaC02My45MmMtNS40NjMgMC05Ljg5NCA0LjQyOS05Ljg5NCA5Ljg5M3M0LjQzMSA5Ljg5NSA5Ljg5NCA5Ljg5NWg4LjI5djEwLjUwMmgtMzguMjVjLTUuNDY0IDAtOS44OTUgNC40My05Ljg5NSA5Ljg5NnYxOS4zMTNoLTE5LjMyM2MtNS40NjUgMC05Ljg5NSA0LjQzLTkuODk1IDkuODk0djIyLjYzNGgtMTcuODQ2di0yOC4wNzNjMC01LjQ2NC00LjQzLTkuODk0LTkuODk0LTkuODk0LTUuNDY0LTFlLTMgLTkuODkzIDQuNDMtOS44OTMgOS44OTN2MTAzLjQ5YzAgNS40NjQgNC40MjkgOS44OTMgOS44OTQgOS44OTMgNS40NjQgMCA5Ljg5NC00LjQzIDkuODk0LTkuODkzdi0yOC4wNzRoMTcuODQ3djIzLjIwM2MwIDUuNDY1IDQuNDMgOS44OTQgOS44OTUgOS44OTRoMjQuODgxbDM0LjkwNyA0Mi45ODljMS44NzkgMi4zMTMgNC43MDEgMy42NTYgNy42OCAzLjY1NmgxMDcuNzFjNS40NjQgMCA5Ljg5My00LjQzMiA5Ljg5My05Ljg5NXYtMTMuMDczaDkuODAzdjEzLjA3M2MwIDMuODY1IDIuMjQ5IDcuMzcyIDUuNzU4IDguOTkgMS4zMjMgMC42MDcgMi43MzUgMC45MDQgNC4xMzUgMC45MDQgMi4zMTkgMCA0LjYwOS0wLjgxNiA2LjQ0MS0yLjM4M2wzMy4xNDEtMjguNDA0YzIuMTkzLTEuODgyIDMuNDUzLTQuNjI1IDMuNDUzLTcuNTE0di02OC42NjNjLTJlLTMgLTMuMDktMS40NTEtNi4wMDgtMy45MTUtNy44Nzh6IiBmaWxsPSJ3aGl0ZSIvPjwvc3ZnPg=='
			);

		}

		/**
		 * Initialize dashboard components
		 *
		 * @return [type] [description]
		 */
		public function init_components() {

			if ( ! $this->is_dashboard() && ! wp_doing_ajax() ) {
				return;
			}

			require jet_engine()->plugin_path( 'includes/dashboard/skins-import.php' );
			require jet_engine()->plugin_path( 'includes/dashboard/skins-export.php' );
			require jet_engine()->plugin_path( 'includes/dashboard/presets.php' );

			$this->import  = new Jet_Engine_Skins_Import();
			$this->export  = new Jet_Engine_Skins_Export();
			$this->presets = new Jet_Engine_Skins_Presets();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		}

		/**
		 * Initialize interface builder
		 *
		 * @return [type] [description]
		 */
		public function enqueue_assets() {

			$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );
			$ui          = new CX_Vue_UI( $module_data );

			$ui->enqueue_assets();

			wp_register_script(
				'jet-engine-dashboard-skins',
				jet_engine()->plugin_url( 'assets/js/admin/dashboard/skins.js' ),
				array( 'cx-vue-ui' ),
				jet_engine()->get_version(),
				true
			);

			wp_register_script(
				'jet-engine-dashboard-skins',
				jet_engine()->plugin_url( 'assets/js/admin/dashboard/skins.js' ),
				array( 'cx-vue-ui' ),
				jet_engine()->get_version(),
				true
			);

			wp_localize_script(
				'jet-engine-dashboard-skins',
				'JetEngineExportConfig',
				$this->export->export_config()
			);

			do_action( 'jet-engine/dashboard/assets' );

			wp_enqueue_script(
				'jet-engine-dashboard',
				jet_engine()->plugin_url( 'assets/js/admin/dashboard/main.js' ),
				array( 'cx-vue-ui', 'jet-engine-dashboard-skins' ),
				jet_engine()->get_version(),
				true
			);

			wp_localize_script(
				'jet-engine-dashboard',
				'JetEngineDashboardConfig',
				apply_filters(
					'jet-engine/dashboard/config',
					array(
						'available_modules' => jet_engine()->modules->get_all_modules_for_js(),
						'active_modules'    => jet_engine()->modules->get_active_modules(),
						'components_list'   => array(
							array(
								'value' => 'meta_field',
								'label' => __( 'Meta Field', 'jet-engine' ),
							),
							array(
								'value' => 'option',
								'label' => __( 'Option', 'jet-engine' ),
							),
						),
						'messages'          => array(
							'saved'            => __( 'Saved!', 'jet-engine' ),
							'saved_and_reload' => __( 'Saved! One of activated/deactivated modules requires page reloading. Page will be reloaded automatically in few seconds.', 'jet-engine' ),
						),
					)
				)
			);

			wp_enqueue_style(
				'jet-engine-dashboard',
				jet_engine()->plugin_url( 'assets/css/admin/dashboard.css' ),
				array(),
				jet_engine()->get_version()
			);

			do_action( 'jet-engine/dashboard/assets-after' );

		}

		/**
		 * Check if is dashboard page
		 *
		 * @return boolean [description]
		 */
		public function is_dashboard() {
			return ( isset( $_GET['page'] ) && jet_engine()->admin_page === $_GET['page'] );
		}

		/**
		 * Returns dashboard page URL
		 * @return [type] [description]
		 */
		public function dashboard_url() {
			return add_query_arg(
				array( 'page' => jet_engine()->admin_page ),
				esc_url( admin_url( 'admin.php' ) )
			);
		}

		/**
		 * Render main admin page
		 *
		 * @return void
		 */
		public function render_page() {
			include jet_engine()->get_template( 'admin/pages/dashboard/main.php' );
		}

		/**
		 * Get dashboard setting
		 *
		 * @return [type] [description]
		 */
		public function get_setting( $setting = null, $default = false ) {

		}

	}

}
