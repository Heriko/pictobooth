<?php
namespace Jet_Engine\Modules\Data_Stores;

class Blocks_Integration {

	public function __construct() {
		add_filter( 'jet-engine/blocks-views/dynamic-link-sources', array( $this, 'register_link_sources' ) );
		add_filter( 'jet-engine/blocks-views/block-types/attributes/dynamic-link', array( $this, 'register_store_atts' ) );
		add_filter( 'jet-engine/blocks-views/custom-blocks-controls', array( $this, 'register_link_controls' ) );
	}

	public function register_link_sources( $sources ) {
		
		$sources[0]['values'][] = array(
			'value' => 'add_to_store',
			'label' => __( 'Add to store', 'jet-engine' ),
		);

		$sources[0]['values'][] = array(
			'value' => 'remove_from_store',
			'label' => __( 'Remove from store', 'jet-engine' ),
		);

		return $sources;

	}

	public function register_store_atts( $atts ) {

		$atts['dynamic_link_store'] = array(
			'type'    => 'string',
			'default' => '',
		);

		$atts['added_to_store_text'] = array(
			'type'    => 'string',
			'default' => '',
		);

		$atts['added_to_store_url'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $atts;

	}

	public function register_link_controls( $controls = array() ) {
		
		$link_controls = ! empty( $controls['dynamic-link'] ) ? $controls['dynamic-link'] : array();

		$link_controls[] = array(
			'prop' => 'dynamic_link_store',
			'label' => __( 'Set store slug', 'jet-engine' ),
			'condition' => array(
				'prop' => 'dynamic_link_source',
				'val'  => array( 'add_to_store', 'remove_from_store' ),
			)
		);

		$link_controls[] = array(
			'prop' => 'added_to_store_text',
			'label' => __( 'Added to store link text', 'jet-engine' ),
			'condition' => array(
				'prop' => 'dynamic_link_source',
				'val'  => array( 'add_to_store' ),
			)
		);

		$link_controls[] = array(
			'prop' => 'added_to_store_url',
			'label' => __( 'Added to store link URL', 'jet-engine' ),
			'condition' => array(
				'prop' => 'dynamic_link_source',
				'val'  => array( 'add_to_store' ),
			)
		);
		
		$controls['dynamic-link'] = $link_controls;

		return $controls;
	}

}
