<?php

namespace Jet_Elementor_Extension;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Query_Control extends \Elementor\Control_Select2 {

	public function get_type() {
		return 'jet-query';
	}

	protected function get_default_settings() {
		return array_merge(
			parent::get_default_settings(), array(
				'query_type' => 'post',
				'query'      => array(),
			)
		);
	}
}
