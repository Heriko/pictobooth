<?php
namespace Aepro;

class AeFacetWP_Integration {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( "elementor/element/ae-post-blocks/section_layout/after_section_end", [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/widget/before_render_content', [ $this, 'add_facetwp_class' ] );
	}

	function register_controls( $element, $args ) {
		$element->start_controls_section(
			'facetwp_section', [
				'label' => __( 'FacetWP', 'ae-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' =>  array(
					'ae_post_type'  =>  'current_loop',
				),
			]
		);

		$element->add_control(
			'enable_facetwp', [
				'label' => __( 'Enable FacetWP', 'ae-pro' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'ae-pro' ),
				'label_off' => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'default' => '',
				'condition' =>  array(
					'ae_post_type'  =>  'current_loop',
				),
			]
		);

		$element->end_controls_section();
	}

	function add_facetwp_class($widget){
		if('ae-post-blocks' != $widget->get_name()){
			return;
		}
		$settings = $widget->get_settings();
		//echo '<pre>'; print_r($settings); echo '</pre>';
		if ($settings['enable_facetwp']  == "yes")  {
			$widget->add_render_attribute( '_wrapper', 'class', [ 'facetwp-template' ] );
		}
	}
}
AeFacetWP_Integration::instance();