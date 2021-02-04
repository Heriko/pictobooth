<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Pages;

use Jet_Engine\Modules\Custom_Content_Types\Module;
use Jet_Engine\Modules\Custom_Content_Types\DB;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Edit extends \Jet_Engine_CPT_Page_Base {

	/**
	 * Page slug
	 *
	 * @return string
	 */
	public function get_slug() {
		if ( $this->item_id() ) {
			return 'edit';
		} else {
			return 'add';
		}
	}

	/**
	 * Page name
	 *
	 * @return string
	 */
	public function get_name() {
		if ( $this->item_id() ) {
			return esc_html__( 'Edit Content Type', 'jet-engine' );
		} else {
			return esc_html__( 'Add New Content Type', 'jet-engine' );
		}
	}

	/**
	 * Returns currently requested items ID.
	 * If this funciton returns an empty result - this is add new item page
	 *
	 * @return [type] [description]
	 */
	public function item_id() {
		return isset( $_GET['id'] ) ? esc_attr( $_GET['id'] ) : false;
	}

	/**
	 * Include meta fields component related assets and templates
	 *
	 * @return [type] [description]
	 */
	public static function enqueue_meta_fields( $args = array() ) {

		wp_enqueue_script(
			'jet-engine-meta-fields',
			jet_engine()->plugin_url( 'assets/js/admin/meta-boxes/fields.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch', ),
			jet_engine()->get_version(),
			true
		);

		$title    = ! empty( $args['title'] ) ? $args['title'] : __( 'Fields', 'jet-engine' );
		$button   = ! empty( $args['button'] ) ? $args['button'] : __( 'New Field', 'jet-engine' );
		$disabled = ! empty( $args['disabled'] ) ? $args['disabled'] : array();

		wp_localize_script( 'jet-engine-meta-fields', 'JetEngineFieldsConfig', array(
			'title'       => $title,
			'button'      => $button,
			'post_types'  => \Jet_Engine_Tools::get_post_types_for_js(),
			'disabled'    => $disabled,
			'i18n'        => array(
				'select_field' => __( 'Select field...', 'jet-engine' ),
			),
			'field_types' => array(
				array(
					'value' => 'text',
					'label' => __( 'Text', 'jet-engine' ),
				),
				array(
					'value' => 'date',
					'label' => __( 'Date', 'jet-engine' ),
				),
				array(
					'value' => 'time',
					'label' => __( 'Time', 'jet-engine' ),
				),
				array(
					'value' => 'datetime-local',
					'label' => __( 'Datetime', 'jet-engine' ),
				),
				array(
					'value' => 'textarea',
					'label' => __( 'Textarea', 'jet-engine' ),
				),
				array(
					'value' => 'wysiwyg',
					'label' => __( 'WYSIWYG', 'jet-engine' ),
				),
				array(
					'value' => 'switcher',
					'label' => __( 'Switcher', 'jet-engine' ),
				),
				array(
					'value' => 'checkbox',
					'label' => __( 'Checkbox', 'jet-engine' ),
				),
				array(
					'value' => 'iconpicker',
					'label' => __( 'Iconpicker', 'jet-engine' ),
				),
				array(
					'value' => 'media',
					'label' => __( 'Media', 'jet-engine' ),
				),
				array(
					'value' => 'gallery',
					'label' => __( 'Gallery', 'jet-engine' ),
				),
				array(
					'value' => 'radio',
					'label' => __( 'Radio', 'jet-engine' ),
				),
				array(
					'value' => 'repeater',
					'label' => __( 'Repeater', 'jet-engine' ),
				),
				array(
					'value' => 'select',
					'label' => __( 'Select', 'jet-engine' ),
				),
				array(
					'value' => 'number',
					'label' => __( 'Number', 'jet-engine' ),
				),
				array(
					'value' => 'colorpicker',
					'label' => __( 'Colorpicker', 'jet-engine' ),
				),
				array(
					'value' => 'posts',
					'label' => __( 'Posts', 'jet-engine' ),
				),
				array(
					'value' => 'html',
					'label' => __( 'HTML', 'jet-engine' ),
				),
			)
		) );

		add_action( 'admin_footer', array( __CLASS__, 'add_meta_fields_template' ) );

	}

	/**
	 * Register add controls
	 * @return [type] [description]
	 */
	public function page_specific_assets() {

		$module_data = jet_engine()->framework->get_included_module_data( 'cherry-x-vue-ui.php' );

		$ui = new \CX_Vue_UI( $module_data );

		$ui->enqueue_assets();

		self::enqueue_meta_fields();

		wp_enqueue_script(
			'jet-engine-cct-delete-dialog',
			Module::instance()->module_url( 'assets/js/admin/delete-dialog.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch', ),
			jet_engine()->get_version(),
			true
		);

		wp_localize_script(
			'jet-engine-cct-delete-dialog',
			'JetEngineCCTDeleteDialog',
			array(
				'api_path' => jet_engine()->api->get_route( 'delete-content-type' ),
				'redirect' => $this->manager->get_page_link( 'list' ),
			)
		);

		wp_enqueue_script(
			'jet-engine-cct-edit',
			Module::instance()->module_url( 'assets/js/admin/edit.js' ),
			array( 'cx-vue-ui', 'wp-api-fetch' ),
			jet_engine()->get_version(),
			true
		);

		$id = $this->item_id();

		if ( $id ) {
			$button_label = __( 'Update Content Type', 'jet-engine' );
			$redirect     = false;
		} else {
			$button_label = __( 'Add Content Type', 'jet-engine' );
			$redirect     = $this->manager->get_edit_item_link( '%id%' );
		}

		wp_localize_script(
			'jet-engine-cct-edit',
			'JetEngineCCTConfig',
			$this->manager->get_admin_page_config( array(
				'api_path_edit'     => jet_engine()->api->get_route( $this->get_slug() . '-content-type' ),
				'item_id'           => $id,
				'edit_button_label' => $button_label,
				'redirect'          => $redirect,
				'post_types'        => \Jet_Engine_Tools::get_post_types_for_js(),
				'db_prefix'         => DB::table_prefix(),
				'positions'         => $this->get_positions(),
				'service_fields'    => Module::instance()->manager->get_service_fields( array(
					'add_id_field' => true,
					'has_single'   => true,
				) ),
				'help_links'        => array(
					array(
						'url'   => 'https://crocoblock.com/knowledge-base/articles/jetengine-how-to-create-a-custom-content-type/?utm_source=jetengine&utm_medium=custom-content-type&utm_campaign=need-help',
						'label' => __( 'How to Create a Custom Content Type', 'jet-engine' ),
					),
				),
			) )
		);

		wp_add_inline_style( 'common', 'input.cx-vui-input[disabled="disabled"] {opacity:.5;}' );

		add_action( 'admin_footer', array( $this, 'add_page_template' ) );

	}

	/**
	 * Returns available positions list
	 *
	 * @return [type] [description]
	 */
	public function get_positions() {
		return apply_filters( 'jet-engine/options-pages/available-positions', array(
			array(
				'value' => 3,
				'label' => __( 'Dashboard', 'jet-engine' ),
			),
			array(
				'value' => 4,
				'label' => __( 'First Separator', 'jet-engine' ),
			),
			array(
				'value' => 6,
				'label' => __( 'Posts', 'jet-engine' ),
			),
			array(
				'value' => 11,
				'label' => __( 'Media', 'jet-engine' ),
			),
			array(
				'value' => 16,
				'label' => __( 'Links', 'jet-engine' ),
			),
			array(
				'value' => 21,
				'label' => __( 'Pages', 'jet-engine' ),
			),
			array(
				'value' => 26,
				'label' => __( 'Comments', 'jet-engine' ),
			),
			array(
				'value' => 59,
				'label' => __( 'Second Separator', 'jet-engine' ),
			),
			array(
				'value' => 61,
				'label' => __( 'Appearance', 'jet-engine' ),
			),
			array(
				'value' => 66,
				'label' => __( 'Plugins', 'jet-engine' ),
			),
			array(
				'value' => 71,
				'label' => __( 'Users', 'jet-engine' ),
			),
			array(
				'value' => 76,
				'label' => __( 'Tools', 'jet-engine' ),
			),
			array(
				'value' => 81,
				'label' => __( 'Settings', 'jet-engine' ),
			),
			array(
				'value' => 100,
				'label' => __( 'Third Separator', 'jet-engine' ),
			),
		) );
	}

	/**
	 * Print add/edit page template
	 */
	public function add_page_template() {

		ob_start();
		include Module::instance()->module_path( 'templates/admin/edit.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-cct-form">%s</script>', $content );

		ob_start();
		include Module::instance()->module_path( 'templates/admin/delete-dialog.php' );
		$content = ob_get_clean();
		printf( '<script type="text/x-template" id="jet-cct-delete-dialog">%s</script>', $content );

	}

	/**
	 * Adds template for meta fields component
	 */
	public static function add_meta_fields_template() {

		ob_start();
		include jet_engine()->get_template( 'admin/pages/meta-boxes/fields.php' );
		$content = ob_get_clean();

		printf( '<script type="text/x-template" id="jet-meta-fields">%s</script>', $content );

	}

	/**
	 * Renderer callback
	 *
	 * @return void
	 */
	public function render_page() {
		?>
		<br>
		<div id="jet_cct_form"></div>
		<?php
	}

}
