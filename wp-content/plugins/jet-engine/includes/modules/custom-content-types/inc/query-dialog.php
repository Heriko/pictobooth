<?php
namespace Jet_Engine\Modules\Custom_Content_Types;

class Query_Dialog {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    object
	 */
	private static $instance = null;

	public function register_api_endpoint( $api_manager ) {
		require_once Module::instance()->module_path( 'rest-api/get-content-type-fields.php' );
		$api_manager->register_endpoint( new Rest\Get_Content_Type_Fields() );
	}

	public function api_path() {
		return jet_engine()->api->get_route( 'get-content-type-fields' );
	}

	public function assets() {

		wp_enqueue_script(
			'jet-engine-cct-query-dialog',
			Module::instance()->module_url( 'assets/js/admin/query-dialog.js' ),
			array( 'wp-api-fetch' ),
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_style(
			'jet-engine-cct-query-dialog',
			Module::instance()->module_url( 'assets/css/query-dialog.css' ),
			array(),
			jet_engine()->get_version()
		);

		wp_localize_script( 'jet-engine-cct-query-dialog', 'jetQueryDialogConfig', array(
			'dataTypes' => array(
				array(
					'value' => 'auto',
					'label' => __( 'Auto', 'jet-engine' ),
				),
				array(
					'value' => 'integer',
					'label' => __( 'Integer', 'jet-engine' ),
				),
				array(
					'value' => 'float',
					'label' => __( 'Float', 'jet-engine' ),
				),
				array(
					'value' => 'timestamp',
					'label' => __( 'Timestamp', 'jet-engine' ),
				),
				array(
					'value' => 'date',
					'label' => __( 'Date', 'jet-engine' ),
				),
				array(
					'value' => 'char',
					'label' => __( 'Char or string', 'jet-engine' ),
				),
			),
			'operators' => array(
				array(
					'value' => '=',
					'label' => __( 'Equal', 'jet-engine' ),
				),
				array(
					'value' => '!=',
					'label' => __( 'Not equal', 'jet-engine' ),
				),
				array(
					'value' => '>',
					'label' => __( 'Greater than', 'jet-engine' ),
				),
				array(
					'value' => '>=',
					'label' => __( 'Greater or equal', 'jet-engine' ),
				),
				array(
					'value' => '<',
					'label' => __( 'Less than', 'jet-engine' ),
				),
				array(
					'value' => '<=',
					'label' => __( 'Equal or less', 'jet-engine' ),
				),
				array(
					'value' => 'LIKE',
					'label' => __( 'Like', 'jet-engine' ),
				),
				array(
					'value' => 'NOT LIKE',
					'label' => __( 'Not like', 'jet-engine' ),
				),
				array(
					'value' => 'IN',
					'label' => __( 'In', 'jet-engine' ),
				),
				array(
					'value' => 'NOT IN',
					'label' => __( 'Not in', 'jet-engine' ),
				),
				array(
					'value' => 'BETWEEN',
					'label' => __( 'Between', 'jet-engine' ),
				),
				array(
					'value' => 'NOT BETWEEN',
					'label' => __( 'Not between', 'jet-engine' ),
				),
			),
		) );

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