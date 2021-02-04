<?php
/**
 * Listings manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Listings' ) ) {

	/**
	 * Define Jet_Engine_Listings class
	 */
	class Jet_Engine_Listings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Library items id for tabs and options list
		 *
		 * @var string
		 */
		private $_id= 'jet-listing-items';

		/**
		 * Macros manager instance
		 *
		 * @var null
		 */
		public $macros = null;

		/**
		 * Filters manager instance
		 *
		 * @var null
		 */
		public $filters = null;

		/**
		 * Data manager instance
		 *
		 * @var null
		 */
		public $data = null;

		/**
		 * Holder for created listings
		 *
		 * @var null
		 */
		public $listings = null;

		/**
		 * Listings post type object
		 *
		 * @var null
		 */
		public $post_type = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			require jet_engine()->plugin_path( 'includes/components/listings/post-type.php' );
			require jet_engine()->plugin_path( 'includes/components/listings/macros.php' );
			require jet_engine()->plugin_path( 'includes/components/listings/filters.php' );
			require jet_engine()->plugin_path( 'includes/components/listings/data.php' );
			require jet_engine()->plugin_path( 'includes/components/listings/delete-post.php' );

			$this->post_type   = new Jet_Engine_Listings_Post_Type();
			$this->macros      = new Jet_Engine_Listings_Macros();
			$this->filters     = new Jet_Engine_Listings_Filters();
			$this->data        = new Jet_Engine_Listings_Data();
			$this->delete_post = new Jet_Engine_Delete_Post();

			// Ensure backward compatibility
			jet_engine()->post_type = $this->post_type;

			// Frontend
			require jet_engine()->plugin_path( 'includes/components/listings/frontend.php' );
			jet_engine()->frontend = new Jet_Engine_Frontend();

			if ( $this->is_listing_ajax() ) {
				require jet_engine()->plugin_path( 'includes/components/listings/ajax-handlers.php' );
				new Jet_Engine_Listings_Ajax_Handlers();
			}

		}

		/**
		 * Check if is AJAXlisting request
		 */
		public function is_listing_ajax() {
			return wp_doing_ajax() && ! empty( $_REQUEST['action'] ) && 'jet_engine_ajax' === $_REQUEST['action'];
		}

		public function repeater_sources() {
			return apply_filters( 'jet-engine/listing/repeater-sources', array(
				'jet_engine'         => __( 'JetEngine', 'jet-engine' ),
				'jet_engine_options' => __( 'JetEngine Options Page', 'jet-engine' ),
				'acf'                => __( 'ACF', 'jet-engine' ),
			) );
		}

		/**
		 * Returns new listing document
		 *
		 * @param  array  $setting [description]
		 * @return [type]          [description]
		 */
		public function get_new_doc( $setting = array() ) {

			if ( ! class_exists( 'Jet_Engine_Listings_Document' ) ) {
				require jet_engine()->plugin_path( 'includes/components/listings/document.php' );
			}

			return new Jet_Engine_Listings_Document( $setting );
		}

		/**
		 * Return registered listings
		 *
		 * @return [type] [description]
		 */
		public function get_listings() {

			if ( null === $this->listings ) {
				$this->listings = get_posts( array(
					'post_type'      => jet_engine()->post_type->slug(),
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				) );
			}

			return $this->listings;
		}

		/**
		 * Get listings list for options.
		 *
		 * @param string $context Context: elementor or blocks
		 *
		 * @return array
		 */
		public function get_listings_for_options( $context = 'elementor' ) {
			$listings = $this->get_listings();
			$list = wp_list_pluck( $listings, 'post_title', 'ID' );

			$result = array();

			if ( 'blocks' === $context ) {

				$result[] = array(
					'value' => '',
					'label' => esc_html__( 'Select...', 'jet-engine' ),
				);

				foreach ( $list as $value => $label ) {
					$result[] = array(
						'value' => $value,
						'label' => $label,
					);
				}

			} else {
				$result = array( '' => esc_html__( 'Select...', 'jet-engine' ) ) + $list;
			}

			return $result;
		}

		/**
		 * Get widget hide options.
		 *
		 * @param string $context Context: elementor or blocks
		 *
		 * @return array
		 */
		public function get_widget_hide_options( $context = 'elementor' ) {

			$hide_options = apply_filters( 'jet-engine/listing/grid/widget-hide-options', array(
				''            => __( 'Always show', 'jet-engine' ),
				'empty_query' => __( 'Query is empty', 'jet-engine' ),
			) );

			$result = array();

			if ( 'blocks' === $context ) {
				foreach ( $hide_options as $value => $label ) {
					$result[] = array(
						'value' => $value,
						'label' => $label,
					);
				}

			} else {
				$result = $hide_options;
			}

			return $result;
		}

		/**
		 * Return Listings items slug/ID
		 *
		 * @return [type] [description]
		 */
		public function get_id() {
			return $this->_id;
		}

		/**
		 * Get post types list for options.
		 *
		 * @return array
		 */
		public function get_post_types_for_options() {

			$args = array(
				'public' => true,
			);

			$post_types = get_post_types( $args, 'objects', 'and' );
			$post_types = wp_list_pluck( $post_types, 'label', 'name' );

			if ( isset( $post_types[ jet_engine()->post_type->slug() ] ) ) {
				unset( $post_types[ jet_engine()->post_type->slug() ] );
			}

			return $post_types;

		}

		/**
		 * Returns image size array in slug => name format
		 *
		 * @return  array
		 */
		public function get_image_sizes( $context = 'elementor' ) {

			global $_wp_additional_image_sizes;

			$sizes         = get_intermediate_image_sizes();
			$result        = array();
			$blocks_result = array();

			foreach ( $sizes as $size ) {
				if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
					$label           = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
					$result[ $size ] = $label;
					$blocks_result[] = array(
						'value' => $size,
						'label' => $label,
					);

				} else {

					$label = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);

					$result[ $size ] = $label;
					$blocks_result[] = array(
						'value' => $size,
						'label' => $label,
					);
				}
			}

			$result        = array_merge( array( 'full' => __( 'Full', 'jet-engine' ), ), $result );
			$blocks_result = array_merge(
				array(
					array(
						'value' => 'full',
						'label' => __( 'Full', 'jet-engine' ),
					)
				),
				$blocks_result
			);

			if ( 'blocks' === $context ) {
				return $blocks_result;
			} else {
				return $result;
			}
		}

		/**
		 * Get post taxonomies for options.
		 *
		 * @return array
		 */
		public function get_taxonomies_for_options() {

			$args = array(
				'public' => true,
			);

			$taxonomies = get_taxonomies( $args, 'objects', 'and' );

			return apply_filters(
				'jet-engine/listings/taxonomies-for-options',
				wp_list_pluck( $taxonomies, 'label', 'name' )
			);
		}

		/**
		 * Returns current render instance
		 *
		 * @return object
		 */
		public function get_render_instance( $item = null, $settings = array() ) {

			$renderers = array(
				'dynamic-field'    => 'Jet_Engine_Render_Dynamic_Field',
				'dynamic-image'    => 'Jet_Engine_Render_Dynamic_Image',
				'dynamic-repeater' => 'Jet_Engine_Render_Dynamic_Repeater',
				'dynamic-meta'     => 'Jet_Engine_Render_Dynamic_Meta',
				'dynamic-link'     => 'Jet_Engine_Render_Dynamic_Link',
				'dynamic-terms'    => 'Jet_Engine_Render_Dynamic_Terms',
				'listing-grid'     => 'Jet_Engine_Render_Listing_Grid',
			);

			$current_renderer = isset( $renderers[ $item ] ) ? $renderers[ $item ] : false;

			if ( ! $current_renderer ) {
				return;
			}

			if ( ! class_exists( 'Jet_Engine_Render_Base' ) ) {
				require jet_engine()->plugin_path( 'includes/components/listings/render/base.php' );
			}

			if ( ! class_exists( $current_renderer ) ) {
				require jet_engine()->plugin_path( 'includes/components/listings/render/' . $item . '.php' );
			}

			return new $current_renderer( $settings );
		}

		/**
		 * Render listing
		 *
		 * @param array $settings
		 */
		public function render_listing( $settings = array() ) {

			$instance = $this->get_render_instance( 'listing-grid', $settings );
			$instance->render();

		}

		/**
		 * Render new listing item part
		 *
		 * @param  [type] $item     [description]
		 * @param  [type] $settings [description]
		 * @return [type]           [description]
		 */
		public function render_item( $item = null, $settings = array() ) {

			$instance = $this->get_render_instance( $item, $settings );
			$instance->render();

		}

		/**
		 * Returns allowed fields callbacks
		 *
		 * @return [type] [description]
		 */
		public function get_allowed_callbacks() {

			return apply_filters( 'jet-engine/listings/allowed-callbacks', array(
				'date'                                  => __( 'Format date', 'jet-engine' ),
				'date_i18n'                             => __( 'Format date (localized)', 'jet-engine' ),
				'number_format'                         => __( 'Format number', 'jet-engine' ),
				'get_the_title'                         => __( 'Get post/page title', 'jet-engine' ),
				'get_permalink'                         => __( 'Get post/page link (only URL)', 'jet-engine' ),
				'jet_get_pretty_post_link'              => __( 'Get post/page link (linked title)', 'jet-engine' ),
				'get_term_link'                         => __( 'Get term link', 'jet-engine' ),
				'wp_oembed_get'                         => __( 'Embed URL', 'jet-engine' ),
				'make_clickable'                        => __( 'Make clickable', 'jet-engine' ),
				'jet_engine_icon_html'                  => __( 'Embed icon from Iconpicker', 'jet-engine' ),
				'jet_engine_render_multiselect'         => __( 'Multiple select field values', 'jet-engine' ),
				'jet_engine_render_checkbox_values'     => __( 'Checkbox field values', 'jet-engine' ),
				'jet_engine_render_checklist'           => __( 'Checked values list', 'jet-engine' ),
				'jet_engine_render_switcher'            => __( 'Switcher field values', 'jet-engine' ),
				'jet_engine_render_acf_checkbox_values' => __( 'ACF Checkbox field values', 'jet-engine' ),
				'jet_engine_render_post_titles'         => __( 'Get post titles from IDs', 'jet-engine' ),
				'jet_related_posts_list'                => __( 'Related posts list', 'jet-engine' ),
				'jet_engine_render_field_values_count'  => __( 'Field values count', 'jet-engine' ),
				'wp_get_attachment_image'               => __( 'Get image by ID', 'jet-engine' ),
				'do_shortcode'                          => __( 'Do shortcodes', 'jet-engine' ),
				'human_time_diff'                       => __( 'Human readable time difference', 'jet-engine' ),
				'wpautop'                               => __( 'Add paragraph tags (wpautop)', 'jet-engine' ),
				'zeroise'                               => __( 'Zeroise (add leading zeros)', 'jet-engine' ),
			) );

		}

		/**
		 * Apply filter callback
		 *
		 * @return [type] [description]
		 */
		public function apply_callback( $input, $callback, $settings = array(), $widget ) {

			if ( ! $callback ) {
				return;
			}

			if ( ! is_callable( $callback ) ) {
				return;
			}

			$args   = array();
			$result = $input;

			switch ( $callback ) {

				case 'date':
				case 'date_i18n':

					if ( ! Jet_Engine_Tools::is_valid_timestamp( $result ) ) {
						$result = strtotime( $result );
					}

					$format = ! empty( $settings['date_format'] ) ? $settings['date_format'] : 'F j, Y';
					$args   = array( $format, $result );

					break;

				case 'number_format':

					$result        = floatval( $result );
					$dec_point     = isset( $settings['num_dec_point'] ) ? $settings['num_dec_point'] : '.';
					$thousands_sep = isset( $settings['num_thousands_sep'] ) ? $settings['num_thousands_sep'] : ',';
					$decimals      = isset( $settings['num_decimals'] ) ? $settings['num_decimals'] : 2;
					$args          = array( $result, $decimals, $dec_point, $thousands_sep );

					break;

				case 'wp_get_attachment_image':

					$args = array( $result, 'full' );

					break;

				case 'jet_engine_render_multiselect':
				case 'jet_engine_render_post_titles':
				case 'jet_engine_render_checkbox_values':

					$delimiter = isset( $settings['multiselect_delimiter'] ) ? $settings['multiselect_delimiter'] : ', ';
					$args      = array( $result, $delimiter );

					break;

				case 'jet_related_posts_list':

					$tag       = isset( $settings['related_list_tag'] ) ? $settings['related_list_tag'] : '';
					$is_linked = isset( $settings['related_list_is_linked'] ) ? $settings['related_list_is_linked'] : '';
					$is_single = isset( $settings['related_list_is_single'] ) ? $settings['related_list_is_single'] : '';
					$delimiter = isset( $settings['multiselect_delimiter'] ) ? $settings['multiselect_delimiter'] : ', ';
					$is_linked = filter_var( $is_linked, FILTER_VALIDATE_BOOLEAN );
					$is_single = filter_var( $is_single, FILTER_VALIDATE_BOOLEAN );
					$args      = array( $result, $tag, $is_single, $is_linked, $delimiter );

					break;

				case 'jet_engine_render_switcher':

					$true_text  = isset( $settings['switcher_true'] ) ? $settings['switcher_true'] : '';
					$false_text = isset( $settings['switcher_false'] ) ? $settings['switcher_false'] : '';
					$args       = array( $result, $true_text, $false_text );

					break;

				case 'jet_engine_render_checklist':

					$cols = isset( $settings['checklist_cols_num'] ) ? $settings['checklist_cols_num'] : 1;

					$field_icon = ! empty( $settings['field_icon'] ) ? esc_attr( $settings['field_icon'] ) : false;
					$new_icon   = ! empty( $settings['selected_field_icon'] ) ? $settings['selected_field_icon'] : false;

					$base_class    = $widget->get_name();
					$new_icon_html = Jet_Engine_Tools::render_icon( $new_icon, $base_class . '__icon' );
					$icon          = false;

					if ( $new_icon_html ) {
						$icon = $new_icon_html;
					} elseif ( $field_icon ) {
						$icon = sprintf( '<i class="%1$s %2$s__icon"></i>', $field_icon, $base_class );
					}

					if ( $icon ) {
						$widget->prevent_icon = true;
					}

					$args = array( $result, $icon, $cols );

					break;

				case 'human_time_diff':
					$from = ! empty( $settings['human_time_diff_from_key'] ) ? jet_engine()->listings->data->get_meta( $settings['human_time_diff_from_key'] ) : 0;
					$from = absint( $from );

					$args = array( $from, $result );

					break;

				case 'zeroise':
					$threshold = isset( $settings['zeroise_threshold'] ) ? $settings['zeroise_threshold'] : 3;
					$args      = array( $result, $threshold );
					break;

				default:

					$args = apply_filters(
						'jet-engine/listing/dynamic-field/callback-args',
						array( $result ),
						$callback,
						$settings,
						$widget
					);

					break;
			}

			return call_user_func_array( $callback, $args );

		}

	}

}
