<?php

namespace Aepro\Admin;

use Aepro\Aepro;

class Admin{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [ $this, 'load_admin_style']);

        add_action('admin_init', [ $this, 'admin_init']);

        add_action('save_post',[ $this, 'save_ae_post_template']);

        add_action('wp_loaded', [ $this, 'term_meta_boxes']);

        add_action('wp_ajax_aep_save_modules', [ $this, 'aep_save_modules']);

    }

    function admin_init(){

        $post_types = Aepro::$_helper->get_rule_post_types('names');
        
        add_action('add_meta_boxes', [ $this, 'add_meta_box' ], 10, 2);

        add_meta_box(
            'ae-shortcode-box',
            'Anywhere Elementor Usage', 
            [ $this, 'ae_pro_shortcode_box' ],
            'ae_global_templates',
            'side',
            'high'
        );
    }



    function add_meta_box($post_type, $post){
       
        add_meta_box(
            'ae_post_template_meta_box',
            __( 'AE Post Template', 'ae-pro' ),
            [ $this, 'post_template_list' ],
            $post_type,
            'side',
            'high'
        );
    }

    function term_meta_boxes(){

        if(!is_admin()){
            return;
        }
    
        // Add Term Meta
        $args = array(
            'public'   => true
        );
        $taxonomies = get_taxonomies($args,'objects');
    
        global $ae_term_templates;
        global $ae_post_templates;
        $helper = Aepro::$_helper;
        $ae_term_templates = $helper->get_taxonomy_templates();
        $ae_post_templates = $helper->get_ae_post_templates();
    
        foreach($taxonomies as $taxonomy){
    
            // Add Form Field
            add_action($taxonomy->name.'_add_form_fields', function ($taxonomy){
                global $ae_term_templates;
                global $ae_post_templates;
    
                ?>
                <div class="form-field term-group">
                <label for="ae_term_template"><?php _e('AE Pro Term Template', 'ae-pro'); ?></label>
                <select class="postform" id="equipment-group" name="ae_term_template">
                    <option value="global"><?php _e('Global', 'ae-pro'); ?></option>
                    <option value="none"><?php _e('None', 'ae-pro'); ?></option>
                    <?php if(count($ae_term_templates)) {
                        foreach ($ae_term_templates[$taxonomy] as $template_id => $title) : ?>
                            <option value="<?php echo $template_id; ?>" class=""><?php echo $title; ?></option>
                    <?php endforeach;
                    }
                    ?>
                </select>
                </div>
                <div class="form-field term-group">
                <label for="ae_term_post_template"><?php _e('AE Pro Singular Template', 'ae-pro'); ?></label>
                <select class="postform" id="equipment-group" name="ae_term_post_template">
                    <?php if(count($ae_post_templates)){
                        foreach ($ae_post_templates as $key => $value) : ?>
                            <option value="<?php echo $key; ?>" class=""><?php echo $value; ?></option>
                    <?php endforeach;
                    }
                    ?>
                </select>
                <br/><p><em><?php echo __('It will be applied on singular layout of all posts/cpt\'s of this term', 'ae-pro'); ?></em></p>
                </div><?php
            },10,2);
    
            // Edit Form Field
            add_action($taxonomy->name.'_edit_form_fields', function ($term, $taxonomy){
                global $ae_term_templates;
                global $ae_post_templates;
    
                $ae_term_templates_list['global'] = __('Global', 'ae-pro');
                $ae_term_templates_list['none'] =  __('None', 'ae-pro');
    
                if(isset($ae_term_templates[$taxonomy]) && is_array($ae_term_templates[$taxonomy]) && count($ae_term_templates[$taxonomy])){
                    $ae_term_templates_list = array_replace($ae_term_templates_list,$ae_term_templates[$taxonomy]);
                }
    
    
                // get current template
                $ae_term_template = get_term_meta($term->term_id, 'ae_term_template', true);
                ?>
                <tr class="form-field term-group-wrap">
                    <th scope="row"><label for="ae_term_template"><?php _e('AE Pro Term Template', 'ae-pro'); ?></label></th>
                    <td><select class="postform" id="feature-group" name="ae_term_template">
                            <?php if(count($ae_term_templates_list)){
                                foreach( $ae_term_templates_list as $template_id => $title ) : ?>
                                    <option value="<?php echo $template_id; ?>" <?php selected( $ae_term_template, $template_id ); ?>><?php echo $title; ?></option>
                                <?php endforeach;
                            } ?>
                        </select></td>
                </tr>
                <?php $ae_current_post_template = get_term_meta($term->term_id, 'ae_term_post_template', true); ?>
                <tr class="form-field term-group-wrap">
                <th scope="row"><label for="ae_term_post_template"><?php _e('AE Pro Singular Template', 'ae-pro'); ?></label></th>
                <td><select class="postform" id="feature-group1" name="ae_term_post_template">
                        <?php if(count($ae_post_templates)){
                            foreach ($ae_post_templates as $key => $value) : ?>
                                <option value="<?php echo $key; ?>" <?php selected( $ae_current_post_template, $key) ?>><?php echo $value; ?></option>
                            <?php endforeach;
                        } ?>
                    </select>
                    <br/><p><em><?php echo __('It will be applied on singular layout of all posts/cpt\'s of this term', 'ae-pro'); ?></em></p>
                </td>
                </tr>
    
                <?php
            },10,2);
    
            add_action('created_'.$taxonomy->name, function($term_id, $tt_id){
                if( isset( $_POST['ae_term_template'] ) && '' !== $_POST['ae_term_template'] ){
                    $template = sanitize_title( $_POST['ae_term_template'] );
                    add_term_meta( $term_id, 'ae_term_template', $template, true );
                }
                if( isset( $_POST['ae_term_post_template'] ) && '' !== $_POST['ae_term_post_template'] ) {
                    $termposttemplate = sanitize_title( $_POST['ae_term_post_template'] );
                    add_term_meta( $term_id, 'ae_term_post_template', $termposttemplate ,  true);
                }
            },10,2);
    
            add_action('edited_'.$taxonomy->name, function($term_id, $tt_id){
                if( isset( $_POST['ae_term_template'] ) && '' !== $_POST['ae_term_template'] ){
                    $template = sanitize_title( $_POST['ae_term_template'] );
                    update_term_meta( $term_id, 'ae_term_template', $template );
                }
                if( isset( $_POST['ae_term_post_template'] ) && '' !== $_POST['ae_term_post_template'] ) {
                    $termposttemplate = sanitize_title( $_POST['ae_term_post_template'] );
                    update_term_meta( $term_id, 'ae_term_post_template', $termposttemplate );
                }
            },10,2);
        }
    
    }    
    
    function ae_pro_shortcode_box($post){
        ?>
        <h4 style="margin-bottom:5px;">Shortcode</h4>
        <input type='text' class='widefat' value='[INSERT_ELEMENTOR id="<?php echo $post->ID; ?>"]' readonly="">
    
        <h4 style="margin-bottom:5px;">Php Code</h4>
        <input type='text' class='widefat' value="&lt;?php echo do_shortcode('[INSERT_ELEMENTOR id=&quot;<?php echo $post->ID; ?>&quot;]'); ?&gt;" readonly="">
        <?php
    }

    function post_template_list( $post ){

        $ae_post_template = get_post_meta($post->ID,'ae_post_template',true);
        
        $post_templates = Aepro::$_helper->get_ae_post_templates();

        ?>
        <h4><?php echo __('Select Layout','ae-pro'); ?></h4>
        <select name="ae_post_template">
            <?php foreach($post_templates as $key => $post_template){
                ?>
                <option <?php echo ($key == $ae_post_template)?'selected':''; ?> value="<?php echo $key; ?>"><?php echo $post_template; ?></option>
                <?php
            }
            ?>
        </select>
        <?php
        wp_nonce_field( 'ae_post_template_metabox_nonce', 'ae_post_template_nonce' );
    }

    function save_ae_post_template($post_id){

        if( !isset( $_POST['ae_post_template_nonce'] ) || !wp_verify_nonce( $_POST['ae_post_template_nonce'],'ae_post_template_metabox_nonce') ) {
            return;
        }
    
        if ( isset($_POST['ae_post_template']) ) {
            update_post_meta($post_id, 'ae_post_template', sanitize_text_field($_POST['ae_post_template']));
        }
    }

    function load_admin_style($hook){
    	wp_enqueue_style('aep-admin', AE_PRO_URL.'includes/admin/admin.css');
    }

    function aep_save_modules(){



    }
}
new Admin();