<?php
namespace Aepro\Modules\PodsFields\Widgets;

use Aepro\Modules\PodsFields\Skins;
use Elementor\Controls_Manager;
use Aepro\Base\Widget_Base;

class AePods extends Widget_Base {

	protected $_has_template_content = false;

	public function get_name() {
		return 'ae-pods';
	}

    public function is_enabled() {

        if(AE_PODS){
            return true;
        }

        return false;
    }

	public function get_title() {
		return __( 'AE - Pods', 'ae-pro' );
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
        //$this->add_skin( new Skins\Skin_Code( $this ) );
		$this->add_skin( new Skins\Skin_Number( $this ) );
		$this->add_skin( new Skins\Skin_Website( $this ) );
		$this->add_skin( new Skins\Skin_Select( $this ) );
		$this->add_skin( new Skins\Skin_Checkbox( $this ) );
		//$this->add_skin( new Skins\Skin_Radio( $this ) );
		//$this->add_skin( new Skins\Skin_Button_Group( $this ) );
		$this->add_skin( new Skins\Skin_Yes_No( $this ) );
		$this->add_skin( new Skins\Skin_File( $this ) );
        $this->add_skin( new Skins\Skin_File_Gallery( $this ) );
        $this->add_skin( new Skins\Skin_File_Image( $this ) );
		$this->add_skin( new Skins\Skin_Email( $this ) );
	}

	function _register_controls() {

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
            'pods_option_name',
            [
                'label'         => __('Pods Option Name', 'ae-pro'),
                'type'          => Controls_Manager::TEXT,
                'condition' => [
                    'field_type' => 'option'
                ]
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



		$this->end_controls_section();
	}

}