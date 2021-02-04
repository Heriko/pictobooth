<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Blocks_Views' ) ) {

	/**
	 * Define Jet_Engine_Blocks_Views class
	 */
	class Jet_Engine_Blocks_Views {

		public $editor;
		public $render;
		public $block_types;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			if ( ! jet_engine()->components->is_component_active( 'listings' ) ) {
				return;
			}

			add_filter( 'upload_mimes', array( $this, 'allow_svg' ) );
			add_filter( 'jet-engine/templates/create/data', array( $this, 'inject_listing_settings' ), 0 );

			if ( is_admin() ) {
				require $this->component_path( 'editor.php' );
				$this->editor = new Jet_Engine_Blocks_Views_Editor();
			}

			require $this->component_path( 'render.php' );
			require $this->component_path( 'block-types.php' );

			$this->render      = new Jet_Engine_Blocks_Views_Render();
			$this->block_types = new Jet_Engine_Blocks_Views_Types();

		}

		/**
		 * Allow SVG images uploading
		 *
		 * @return array
		 */
		public function allow_svg( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		}

		/**
		 * Return path to file inside component
		 *
		 * @param  [type] $path_inside_component [description]
		 * @return [type]                        [description]
		 */
		public function component_path( $path_inside_component ) {
			return jet_engine()->plugin_path( 'includes/components/blocks-views/' . $path_inside_component );
		}

		/**
		 * Return listing template ediit URL to redirect on
		 * @return [type] [description]
		 */
		public function get_redirect_url( $template_id ) {
			return get_edit_post_link( $template_id, '' );
		}

		/**
		 * Check if current listing is rendered with blocks
		 * @param  [type]  $listing_id [description]
		 * @return boolean             [description]
		 */
		public function is_blocks_listing( $listing_id ) {
			$meta = get_post_meta( $listing_id, '_listing_type', true );
			return ( 'blocks' === $meta );
		}

		/**
		 * Inject listing settings from tamplate into _elementor_page_settings meta
		 * @param  [type] $template_data [description]
		 * @return [type]                [description]
		 */
		public function inject_listing_settings( $template_data ) {

			if ( empty( $_REQUEST['listing_view_type'] ) || 'blocks' !== $_REQUEST['listing_view_type'] ) {
				return $template_data;
			}

			if ( ! isset( $_REQUEST['listing_source'] ) ) {
				return $template_data;
			}

			$source     = ! empty( $_REQUEST['listing_source'] ) ? esc_attr( $_REQUEST['listing_source'] ) : 'posts';
			$post_type  = ! empty( $_REQUEST['listing_post_type'] ) ? esc_attr( $_REQUEST['listing_post_type'] ) : '';
			$tax        = ! empty( $_REQUEST['listing_tax'] ) ? esc_attr( $_REQUEST['listing_tax'] ) : '';
			$rep_source = ! empty( $_REQUEST['repeater_source'] ) ? esc_attr( $_REQUEST['repeater_source'] ) : '';
			$rep_field  = ! empty( $_REQUEST['repeater_field'] ) ? esc_attr( $_REQUEST['repeater_field'] ) : '';
			$rep_option = ! empty( $_REQUEST['repeater_option'] ) ? esc_attr( $_REQUEST['repeater_option'] ) : '';

			$listing = array(
				'source'    => $source,
				'post_type' => $post_type,
				'tax'       => $tax,
			);

			$template_data['post_status']                                                 = 'publish';
			$template_data['meta_input']['_listing_type']                                 = 'blocks';
			$template_data['meta_input']['_listing_data']                                 = $listing;
			$template_data['meta_input']['_elementor_page_settings']['listing_source']    = $source;
			$template_data['meta_input']['_elementor_page_settings']['listing_post_type'] = $post_type;
			$template_data['meta_input']['_elementor_page_settings']['listing_tax']       = $tax;
			$template_data['meta_input']['_elementor_page_settings']['repeater_source']   = $rep_source;
			$template_data['meta_input']['_elementor_page_settings']['repeater_field']    = $rep_field;
			$template_data['meta_input']['_elementor_page_settings']['repeater_option']   = $rep_option;

			return $template_data;

		}

	}

}
