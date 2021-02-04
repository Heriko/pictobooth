<?php
/**
 * UAEL FAQ widget
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\FAQ;

use UltimateElementor\Base\Module_Base;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 1.22.0
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 1.22.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-faq';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.22.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'FAQ',
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct();
		if ( UAEL_Helper::is_widget_active( 'FAQ' ) ) {
			add_action( 'wp_head', array( $this, 'render_faq_schema' ) );
		}
	}

	/**
	 * Render the FAQ schema.
	 *
	 * @since 1.26.3
	 *
	 * @access public
	 */
	public function render_faq_schema() {
		$faqs_data = $this->get_faqs_data();

		if ( $faqs_data ) {
			$schema_data = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $faqs_data,
			);

			$encoded_data = wp_json_encode( $schema_data );
			?>
			<script type="application/ld+json">
				<?php print_r( $encoded_data ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?>
			</script>
			<?php
		}
	}

	/**
	 * Get FAQ data.
	 *
	 * @since 1.26.3
	 *
	 * @access public
	 */
	public function get_faqs_data() {
		$elementor = \Elementor\Plugin::$instance;
		$document  = $elementor->documents->get( get_the_ID() );

		if ( ! $document ) {
			return;
		}

		$data        = $document->get_elements_data();
		$widget_ids  = $this->get_widget_ids();
		$object_data = array();

		foreach ( $widget_ids as $widget_id ) {
			$widget_data            = $this->find_element_recursive( $data, $widget_id );
			$widget                 = $elementor->elements_manager->create_element_instance( $widget_data );
			$settings               = $widget->get_settings();
			$content_schema_warning = 0;
			$enable_schema          = $settings['schema_support'];

			foreach ( $settings['tabs'] as $key ) {
				if ( 'content' !== $key['faq_content_type'] ) {
					$content_schema_warning = 1;
				}
			}

			if ( 'yes' === $enable_schema && ( 0 === $content_schema_warning ) ) {
				foreach ( $settings['tabs'] as $faqs ) {
					$new_data = array(
						'@type'          => 'Question',
						'name'           => $faqs['question'],
						'acceptedAnswer' =>
						array(
							'@type' => 'Answer',
							'text'  => $faqs['answer'],
						),
					);
					array_push( $object_data, $new_data );
				}
			}
		}

		return $object_data;
	}

	/**
	 * Get the widget ID.
	 *
	 * @since 1.26.3
	 *
	 * @access public
	 */
	public function get_widget_ids() {
		$elementor = \Elementor\Plugin::$instance;
		$document  = $elementor->documents->get( get_the_ID() );

		if ( ! $document ) {
			return;
		}

		$data       = $document->get_elements_data();
		$widget_ids = array();

		$elementor->db->iterate_data(
			$data,
			function ( $element ) use ( &$widget_ids ) {
				if ( isset( $element['widgetType'] ) && 'uael-faq' === $element['widgetType'] ) {
					array_push( $widget_ids, $element['id'] );
				}
			}
		);
		return $widget_ids;
	}

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.26.3
	 * @access public
	 * @param array  $elements Element array.
	 * @param string $form_id Element ID.
	 * @return Boolean True/False.
	 */
	public function find_element_recursive( $elements, $form_id ) {

		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $form_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}
}
