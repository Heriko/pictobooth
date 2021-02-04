<?php

namespace Aepro\Modules\PodsFields\Skins;
use Aepro\Modules\PodsFields;
use Aepro\Classes\PodsMaster;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;


class Skin_Radio extends Skin_Select {

	public function get_id() {
		return 'radio';
	}

	public function get_title() {
		return __( 'Radio', 'ae-pro' );
	}


	public function register_controls( Widget_Base $widget){

		$this->parent = $widget;

		parent::register_select_controls();

	}

}
