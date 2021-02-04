<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Elementor;

use Jet_Engine\Modules\Custom_Content_Types\Module;

class Manager {

	public function __construct() {

		add_action( 'jet-engine/elementor-views/dynamic-tags/register', array( $this, 'register_dynamic_tags' ) );
		add_action( 'elementor/controls/controls_registered', array( $this, 'add_controls' ), 10 );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'editor_scripts' ), 0 );
		add_action( 'jet-engine/listing/custom-query-settings', array( $this, 'register_query_settings' ) );
	}

	public function register_query_settings( $widget ) {

		$widget->start_controls_section(
			'section_jet_cct_query',
			array(
				'label' => __( 'Content Types Query', 'jet-appointments-booking' ),
			)
		);

		$widget->add_control(
			'jet_cct_query',
			array(
				'label'        => __( 'Set up query', 'jet-engine' ),
				'button_label' => __( 'Query Settings', 'jet-engine' ),
				'type'         => 'jet_query_dialog',
			)
		);

		do_action( 'jet-engine/custom-content-types/elementor/after-query-control', $widget );

		$widget->end_controls_section();

	}

	public function register_dynamic_tags( $tags_module ) {

		require_once Module::instance()->module_path( 'elementor/dynamic-tags/field-tag.php' );
		require_once Module::instance()->module_path( 'elementor/dynamic-tags/image-tag.php' );
		require_once Module::instance()->module_path( 'elementor/dynamic-tags/gallery-tag.php' );

		$tags_module->register_tag( new Dynamic_Tags\Field_Tag() );
		$tags_module->register_tag( new Dynamic_Tags\Image_Tag() );
		$tags_module->register_tag( new Dynamic_Tags\Gallery_Tag() );

	}

	public function add_controls( $controls_manager ) {

		require_once Module::instance()->module_path( 'elementor/controls/query-dialog.php' );
		$controls_manager->register_control( 'jet_query_dialog', new Controls\Query_Dialog_Control() );

	}

	public function editor_scripts() {
		Module::instance()->query_dialog()->assets();
	}

}
