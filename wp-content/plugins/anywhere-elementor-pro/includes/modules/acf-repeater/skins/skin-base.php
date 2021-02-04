<?php
namespace Aepro\Modules\AcfRepeater\Skins;

use Aepro\Frontend;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Aepro\Base\Widget_Base;
use Aepro\Aepro;
use Elementor\Core\Schemes;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class Skin_Base extends Elementor_Skin_Base
{

    protected function _register_controls_actions()
    {
        add_action('elementor/element/ae-acf-repeater/general/before_section_end', [$this, 'register_controls']);

    }

    public function register_controls(Widget_Base $widget)
    {

        $this->parent = $widget;

    }

    function generate_tabs_output($settings){
        $settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );

        $with_css = false;
        if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
            $with_css = true;
        }

        $post_data = Aepro::$_helper->get_demo_post_data();

        $this->parent->add_render_attribute( 'ae-acf-repeater-tabs', [
            'class' => 'ae-acf-repeater-tabs',
            'role' => 'tablist'
        ] );

        $tabs_content_space = $this->get_instance_value('tabs_content_space');
        if($tabs_content_space['size'] != 0){
            $this->parent->add_render_attribute( 'ae-acf-repeater-tabs', [
                'class' => 'space-between-tab-content',
            ] );
        };

        if($this->get_instance_value('advance_style') == 'yes'){
            $this->parent->add_render_attribute( 'ae-acf-repeater-tabs', [
                'class' => 'advance-style',
            ] );
        }

        $this->parent->add_render_attribute( 'ae-acf-repeater-tabs-wrapper', [
            'class' => 'ae-acf-repeater-tabs-wrapper',
            'role' => 'tab'
        ] );

        $this->parent->add_render_attribute( 'ae-acf-repeater-tabs-content-wrapper', [
            'class' => 'ae-acf-repeater-tabs-content-wrapper',
            'role' => 'tabpanel'
        ]);

        $repeater_data = Aepro::$_helper->get_repeater_data($settings, $post_data->ID);

        if( have_rows($repeater_data['repeater_name'], $repeater_data['repeater_type']) ){
            Frontend::$_in_repeater_block = true; ?>
            <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tabs'); ?>>
                <?php
                $counter = 1; ?>
                <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tabs-wrapper'); ?>>
                    <?php while( have_rows($repeater_data['repeater_name'], $repeater_data['repeater_type']) ) {
                    the_row();
                    $this->parent->set_render_attribute( 'ae-acf-repeater-tab-desktop-title', [
                        'class' => 'ae-acf-repeater-tab-title ae-acf-repeater-tab-desktop-title',
                        'data-tab' => $counter,
                        'data-hashtag' => 'tab_' . $counter
                    ] );
                    ?>
                    <<?php echo $settings['title_html_tag']; ?> <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tab-desktop-title'); ?>>
                    <?php echo get_sub_field($settings['tab_title'], $repeater_data['repeater_type']); ?>
                </<?php echo $settings['title_html_tag']; ?>>
            <?php
            $counter++;
            } ?>
            </div>

            <?php
            $counter = 1; ?>
            <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tabs-content-wrapper'); ?>>
                <?php while( have_rows($repeater_data['repeater_name'], $repeater_data['repeater_type']) ) {
                    the_row();
                    $this->parent->set_render_attribute( 'ae-acf-repeater-tab-mobile-title', [
                        'class' => 'ae-acf-repeater-tab-title ae-acf-repeater-tab-mobile-title',
                        'data-tab' => $counter,
                        'data-hashtag' => 'tab_' . $counter
                    ] );
                    $this->parent->set_render_attribute( 'ae-acf-repeater-tab-content', [
                        'class' => 'ae-acf-repeater-tab-content elementor-clearfix',
                        'data-tab' => $counter,
                        'data-hashtag' => 'tab_' . $counter
                    ])
                    ?>
                    <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tab-mobile-title'); ?>>
                        <?php echo get_sub_field($settings['tab_title'], $repeater_data['repeater_type']); ?>
                    </div>
                    <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-tab-content'); ?>>
                        <?php echo Plugin::instance()->frontend->get_builder_content( $settings['template'],$with_css ); ?>
                    </div>
                    <?php
                    $counter++;
                } ?>
            </div>
            </div>
            <?php
            Frontend::$_in_repeater_block = false; }
    }

    function generate_accordion_output($settings){

        $post_data = Aepro::$_helper->get_demo_post_data();
        $index = rand();
        $tab_count = 0;
        $has_icon = (! empty( $settings['selected_icon']['value'] ) );

        $settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );
        $with_css = false;
        if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
            $with_css = true;
        }
        $transition_speed = $this->get_instance_value('accordion_transition_speed');
        $this->parent->add_render_attribute( 'ae-acf-repeater-accordion', [
            'class' => 'elementor-accordion ae-accordion',
            'role' => 'tablist',
            'data-transition-speed' => $transition_speed['size']
        ] );

        $repeater_data = Aepro::$_helper->get_repeater_data($settings, $post_data->ID);

        if( have_rows($repeater_data['repeater_name'], $repeater_data['repeater_type']) ) {
            Frontend::$_in_repeater_block = true;
            ?>
        <div <?php echo $this->parent->get_render_attribute_string('ae-acf-repeater-accordion'); ?>>
            <?php while( have_rows($repeater_data['repeater_name'], $repeater_data['repeater_type']) ) {
                the_row();

                $tab_no =   $index + 1;
                $tab_count  = $tab_count + 1;

                $title_class = 'elementor-tab-title ae-tab-title';
                $content_class = 'elementor-tab-content elementor-clearfix ae-tab-content';

                if($tab_count == 1){
                    $title_class = $title_class . ' elementor-active';
                    $content_class = $content_class . ' elementor-active';
                }

                $this->parent->set_render_attribute( 'ae-acf-repeater-accordion-title', [
                    'id' => 'elementor-tab-title-' . $tab_no . $tab_count,
                    't_id' => $tab_no . $tab_count,
                    'class' => $title_class,
                    'data-tab' => $tab_count,
                    'role' => 'tab',
                    'aria-controls' => 'elementor-tab-content-' . $tab_no . $tab_count,
                ] );

                $this->parent->set_render_attribute( 'ae-acf-repeater-accordion-content', [
                    'id' => 'elementor-tab-content-' . $tab_no . $tab_count,
                    't_id' => $tab_no . $tab_count,
                    'class' => $content_class,
                    'data-tab' => $tab_count,
                    'role' => 'tabpanel',
                    'aria-labelledby' => 'elementor-tab-title-' . $tab_no . $tab_count,
                ] );
                ?>
                <div class="elementor-accordion-item ae-accordion-item">
                <<?php echo $settings['title_html_tag']; ?> <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-accordion-title' ); ?>>
                <?php if ( $has_icon ) : ?>
                    <span class="elementor-accordion-icon elementor-accordion-icon-<?php echo esc_attr( $this->get_instance_value('icon_align') ); ?>" aria-hidden="true">
                                <span class="elementor-accordion-icon-closed ae-accordion-icon-closed"><?php Icons_Manager::render_icon( $settings['selected_icon'] ); ?></span>
                                <span class="elementor-accordion-icon-opened ae-accordion-icon-opened"><?php Icons_Manager::render_icon( $settings['selected_active_icon'] ); ?></span>
                            </span>
                <?php endif; ?>
                <a href="#"><?php echo get_sub_field($settings['tab_title'], $repeater_data['repeater_type']); ?></a>
                </<?php echo $settings['title_html_tag']; ?>>
                <div <?php echo $this->parent->get_render_attribute_string( 'ae-acf-repeater-accordion-content' ); ?>>
                    <?php echo Plugin::instance()->frontend->get_builder_content( $settings['template'],$with_css ); ?>
                </div>
                </div>
            <?php } ?>
            </div>
            <?php
            Frontend::$_in_repeater_block = false;
        }
    }


}