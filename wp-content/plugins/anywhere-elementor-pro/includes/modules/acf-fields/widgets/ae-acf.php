<?php
namespace Aepro\Modules\AcfFields\Widgets;

use Aepro\Modules\AcfFields\Skins;
use Aepro\Aepro;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;

class AeAcf extends Widget_Base {

	protected $_has_template_content = false;

	public function get_name() {
		return 'ae-acf';
	}

	public function is_enabled() {

		if(AE_ACF){
			return true;
		}

		return false;
	}


	public function get_title() {
		return __( 'AE - ACF Fields', 'ae-pro' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	protected function _register_skins() {
		$this->add_skin( new Skins\Skin_Text( $this ) );
		$this->add_skin( new Skins\Skin_Text_Area( $this ) );
		$this->add_skin( new Skins\Skin_Wysiwyg( $this ) );
		$this->add_skin( new Skins\Skin_Number( $this ) );
		$this->add_skin( new Skins\Skin_Url( $this ) );
		$this->add_skin( new Skins\Skin_Select( $this ) );
		$this->add_skin( new Skins\Skin_Checkbox( $this ) );
		$this->add_skin( new Skins\Skin_Radio( $this ) );
		$this->add_skin( new Skins\Skin_Button_Group( $this ) );
		$this->add_skin( new Skins\Skin_True_False( $this ) );
		$this->add_skin( new Skins\Skin_File( $this ) );
		$this->add_skin( new Skins\Skin_Email( $this ) );
        $this->add_skin( new Skins\Skin_Image( $this ) );
        $this->add_skin( new Skins\Skin_Taxonomy( $this ) );
	}

	function _register_controls() {


        $repeater_arr = Aepro::$_helper->is_repeater_block_layout();
        $repeater = '';
        $is_repeater = '';
        if(isset($repeater_arr['field'])) {
            $repeater = $repeater_arr['field'];
            if($repeater_arr['is_repeater']){
                $is_repeater = 'repeater';
            }
        }


		$this->start_controls_section('general', [
			'label' => __('General', 'ae-pro')
		]);

		$this->add_control(
			'field_type',
			[
				'label' => __('Source','ae-pro'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'post'  => __('Post Field', 'ae-pro'),
					'term' => __('Term Field', 'ae-pro'),
                    'user' => __('User', 'ae-pro'),
                    'option' => __('Option', 'ae-pro')
				],
				'default' => 'post'
			]
		);

		$this->add_control(
			'field_name',
			[
				'label'         => __('Field', 'ae-pro'),
				'type'          => Controls_Manager::TEXT,
				'placeholder'   => 'Enter your acf field name',
			]
		);

		$this->add_control(
			'is_sub_field',
			[
				'label' => __('Is Sub Field', 'ae-pro'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    ''         => __('No', 'ae-pro'),
                    'repeater' => __('Repeater Field', 'ae-pro'),
                    'group' => __('Group Field', 'ae-pro')
                ],
                //'default' => $is_repeater,
				'condition' => [
					'field_type'   => 'post'
				]
			]
		);

		$this->add_control(
			'parent_field',
			[
				'label' => __('Parent Field', 'ae-pro'),
				'type'  => Controls_Manager::TEXT,
				//'default' => $repeater,
				'condition' => [
					'is_sub_field'   => ['repeater', 'group']
				]
			]
		);

		$this->end_controls_section();
	}

}