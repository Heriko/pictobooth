<?php
/**
 * Elementor views manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Render_Dynamic_Repeater' ) ) {

	class Jet_Engine_Render_Dynamic_Repeater extends Jet_Engine_Render_Base {

		private $show_field = true;

		public function get_name() {
			return 'jet-listing-dynamic-repeater';
		}

		/**
		 * Return saved fields from post/term meta
		 *
		 * @param  [type] $settings [description]
		 * @return [type]           [description]
		 */
		public function get_saved_fields( $settings ) {

			$saved = apply_filters( 'jet-engine/listings/dynamic-repeater/pre-get-saved', false, $settings );

			if ( $saved ) {
				return $saved;
			}

			$source = isset( $settings['dynamic_field_source'] ) ? $settings['dynamic_field_source'] : false;

			if ( 'options_page' === $source ) {
				$option = ! empty( $settings['dynamic_field_option'] ) ? $settings['dynamic_field_option'] : false;
				return jet_engine()->listings->data->get_option( $option );
			} else {
				return jet_engine()->listings->data->get_meta( $source );
			}

		}

		/**
		 * Render field content
		 *
		 * @return [type] [description]
		 */
		public function render_repeater_items( $settings ) {

			global $post;

			$fields        = $this->get_saved_fields( $settings );
			$format        = isset( $settings['dynamic_field_format'] ) ? $settings['dynamic_field_format'] : false;
			$delimiter     = isset( $settings['items_delimiter'] ) ? $settings['items_delimiter'] : false;
			$item_tag      = isset( $settings['item_tag'] ) ? $settings['item_tag'] : 'div';
			$items_before  = isset( $settings['dynamic_field_before'] ) ? $settings['dynamic_field_before'] : '';
			$items_after   = isset( $settings['dynamic_field_after'] ) ? $settings['dynamic_field_after'] : '';
			$is_first      = true;
			$counter       = ! empty( $settings['dynamic_field_counter'] ) ? $settings['dynamic_field_counter'] : false;
			$counter_after = ! empty( $settings['dynamic_field_counter_after'] ) ? $settings['dynamic_field_counter_after'] : false;
			$leading_zero  = ! empty( $settings['dynamic_field_leading_zero'] ) ? $settings['dynamic_field_leading_zero'] : false;
			$counter_pos   = ! empty( $settings['dynamic_field_counter_position'] ) ? $settings['dynamic_field_counter_position'] : 'at-left';

			if ( empty( $fields ) ) {

				if ( ! empty( $settings['hide_if_empty'] ) ) {
					$this->show_field = false;
				}

				return;
			}

			$base_class = $this->get_name();

			printf(
				'<div class="%1$s__items %2$s">',
				$base_class,
				( $counter ? 'has-counter counter--' . $counter_pos : '' )
			);

			if ( $items_before ) {
				echo $items_before;
			}

			$index = 1;

			foreach ( $fields as $field ) {

				$item_content = preg_replace_callback(
					'/\%(([a-zA-Z0-9_-]+)(\|([a-zA-Z0-9\(\)\,\:\/\s_-]+))*)\%/',
					function( $matches ) use ( $field ) {

						if ( ! isset( $matches[2] ) ) {
							return $matches[0];
						}

						if ( ! isset( $field[ $matches[2] ] ) ) {
							return $matches[0];
						} else {
							if ( isset( $matches[4] ) ) {
								return jet_engine()->listings->filters->apply_filters(
									$field[ $matches[2] ], $matches[4]
								);
							} else {
								return $field[ $matches[2] ];
							}
						}

					},
					$format
				);

				if ( $delimiter && ! $is_first ) {
					printf(
						'<div class="%1$s__delimiter">%2$s</div>',
						$base_class,
						$delimiter
					);
				}



				if ( false === strpos( $item_content, '<' ) ) {
					$item_content = '<div>' . $item_content . '</div>';
				}

				if ( $counter ) {

					$counter_html = $index;

					if ( $leading_zero ) {
						$counter_html = zeroise( $counter_html, 2 );
					}

					if ( $counter_after ) {
						$counter_html = $counter_html . $counter_after;
					}

					$item_content = sprintf(
						'<div class="%2$s__counter">%3$s</div><div class="%2$s__body">%1$s</div>',
						$item_content,
						$base_class,
						$counter_html
					);
				}

				printf(
					'<%3$s class="%1$s__item">%2$s</%3$s>',
					$base_class,
					$item_content,
					$item_tag
				);

				$is_first = false;
				$index++;

			}

			if ( $items_after ) {
				echo $items_after;
			}

			echo '</div>';

		}

		public function render() {

			$base_class = $this->get_name();
			$settings   = $this->get_settings();

			$classes = array(
				'jet-listing',
				$base_class,
			);

			if ( ! empty( $settings['className'] ) ) {
				$classes[] = esc_attr( $settings['className'] );
			}

			ob_start();

			printf( '<div class="%1$s">', implode( ' ', $classes ) );

				do_action( 'jet-engine/listing/dynamic-repeater/before-field', $this );

				$this->render_repeater_items( $settings );

				do_action( 'jet-engine/listing/dynamic-repeater/after-field', $this );

			echo '</div>';

			$content = ob_get_clean();

			if ( $this->show_field ) {
				echo $content;
			}

		}

	}

}