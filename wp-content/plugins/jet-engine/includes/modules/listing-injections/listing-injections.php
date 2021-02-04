<?php
/**
 * Listing injections module
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Engine_Module_Listing_Injections' ) ) {

	/**
	 * Define Jet_Engine_Module_Listing_Injections class
	 */
	class Jet_Engine_Module_Listing_Injections extends Jet_Engine_Module_Base {

		private $injected_counter = array();
		private $injected_indexes = array();

		/**
		 * Module ID
		 *
		 * @return string
		 */
		public function module_id() {
			return 'listing-injections';
		}

		/**
		 * Module name
		 *
		 * @return string
		 */
		public function module_name() {
			return __( 'Listing Grid injections', 'jet-engine' );
		}

		/**
		 * Module init
		 *
		 * @return void
		 */
		public function module_init() {
			add_action( 'jet-engine/listing/after-general-settings', array( $this, 'add_settings' ) );
			add_action( 'jet-engine/listing/grid/before', array( $this, 'reset_injected_counter' ) );
			add_filter( 'jet-engine/listing/pre-get-item-content', array( $this, 'maybe_inject_item' ), 10, 4 );
			add_filter( 'jet-engine/listing/item-classes', array( $this, 'maybe_add_colspan' ), 10, 4 );
			add_filter( 'jet-engine/listing/grid/nav-widget-settings', array( $this, 'store_nav_settings' ), 10, 2 );
		}

		/**
		 * Store injection-specific settings for nav
		 *
		 * @return [type] [description]
		 */
		public function store_nav_settings( $nav_settngs = array(), $settings = array() ) {

			$nav_settngs['inject_alternative_items'] = ! empty( $settings['inject_alternative_items'] ) ? $settings['inject_alternative_items'] : '';
			$nav_settngs['injection_items'] = ! empty( $settings['injection_items'] ) ? $settings['injection_items'] : array();

			return $nav_settngs;

		}

		/**
		 * Reset injected counter
		 *
		 * @return [type] [description]
		 */
		public function reset_injected_counter() {
			$this->injected_counter = array();
			$this->injected_indexes = array();
		}

		/**
		 * Maybe inject new listing item
		 */
		public function maybe_inject_item( $content = false, $post, $i, $widget ) {

			$settings      = $widget->get_settings();
			$injected_item = $this->get_injected_item( $settings, $post, $i, $widget );

			if ( ! $injected_item ) {
				return $content;
			} else {
				return $this->get_injected_item_content( $injected_item, $post );
			}

		}

		/**
		 * Returns injected item ID
		 *
		 * @return [type] [description]
		 */
		public function get_injected_item_content( $item_id, $post ) {

			jet_engine()->frontend->set_listing( $item_id );
			ob_start();
			$listing_item = jet_engine()->frontend->get_listing_item( $post );
			$inline_css = ob_get_clean();

			return $inline_css . $listing_item;
		}

		/**
		 * Maybe add clumns colspan on apropriate indexes
		 *
		 * @param  [type] $classes [description]
		 * @param  [type] $post    [description]
		 * @param  [type] $i       [description]
		 * @param  [type] $widget  [description]
		 * @return [type]          [description]
		 */
		public function maybe_add_colspan( $classes, $post, $i, $widget ) {

			if ( empty( $this->injected_indexes[ $i ] ) ) {
				return $classes;
			}

			$item = $this->injected_indexes[ $i ];

			if ( empty( $item['item_colspan'] ) ) {
				return $classes;
			}

			$colspan = absint( $item['item_colspan'] );

			if ( 1 < $colspan ) {

				$settings = $widget->get_settings();
				$columns  = ! empty( $settings['columns'] ) ? absint( $settings['columns'] ) : 3;

				if ( $colspan > $columns ) {
					$final_colspan = '1';
				} elseif ( $columns === $colspan ) {
					$final_colspan = '1';
				} else {
					$final_colspan = $colspan . '-' . $columns;
				}

				$classes[] = 'colspan-' . $final_colspan;
			}

			return $classes;

		}

		/**
		 * Check if current iterator is matched with required number
		 *
		 * @param  [type]  $i          [description]
		 * @param  [type]  $number     [description]
		 * @param  [type]  $from_first [description]
		 * @return boolean             [description]
		 */
		public function is_matched_num( $i = 1, $number = 2, $from_first = false, $once = false ) {

			if ( empty( $number ) ) {
				return false;
			}

			if ( empty( $once ) ) {
				if ( $from_first ) {
					return ( 1 === $i || 0 === ( $i - 1 ) % $number );
				} else {
					return ( 0 === $i % $number );
				}
			} else {
				if ( $from_first ) {
					return ( 1 === $i );
				} else {
					return ( $number === $i );
				}
			}

		}

		/**
		 * Check if we need to inject item on this moment
		 *
		 * @return [type] [description]
		 */
		public function get_injected_item( $settings, $post, $i, $widget ) {

			$inject = ! empty( $settings['inject_alternative_items'] ) ? $settings['inject_alternative_items'] : false;

			if ( ! $inject ) {
				return false;
			}

			$items = ! empty( $settings['injection_items'] ) ? $settings['injection_items'] : array();

			if ( empty( $items ) || ! is_array( $items ) ) {
				return false;
			}

			$i = absint( $i );

			$items = $this->sort_items( $items );

			foreach ( $items as $item ) {

				$result = false;

				if ( empty( $item['item'] ) ) {
					continue;
				}

				$type = ! empty( $item['item_condition_type'] ) ? $item['item_condition_type'] : 'on_item';
				$once = ! empty( $item['inject_once'] ) ? $item['inject_once'] : false;

				switch ( $type ) {

					case 'on_item':

						$num        = ! empty( $item['item_num'] ) ? absint( $item['item_num'] ) : 2;
						$from_first = ! empty( $item['start_from_first'] ) ? true : false;

						if ( $this->is_matched_num( $i, $num, $from_first, $once ) ) {
							$this->increase_count( $item['item'], $i, $item );
							$result = $item['item'];
						}

						break;

					case 'item_meta':

						$meta_key     = ! empty( $item['meta_key'] ) ? $item['meta_key'] : false;
						$meta_compare = ! empty( $item['meta_key_compare'] ) ? $item['meta_key_compare'] : '=';
						$compare_val  = ! empty( $item['meta_key_val'] ) ? $item['meta_key_val'] : false;

						if ( $meta_key ) {

							$class = get_class( $post );

							switch ( $class ) {
								case 'WP_User':
									$meta_val = get_user_meta( $post->ID, $meta_key );
									break;

								case 'WP_Term':
									$meta_val = get_term_meta( $post->term_id, $meta_key );
									break;

								default:
									$meta_val = get_post_meta( $post->ID, $meta_key );
							}

							$exists   = ! empty( $meta_val ) ? true : false;
							$meta_val = $exists ? $meta_val[0] : false;
							$matched  = false;

							switch ( $meta_compare ) {
								case '=':
									if ( $meta_val == $compare_val ) {
										$matched = true;
									}
									break;

								case '!=':
									if ( $meta_val != $compare_val ) {
										$matched = true;
									}
									break;

								case '>':
									if ( $meta_val > $compare_val ) {
										$matched = true;
									}
									break;

								case '<':
									if ( $meta_val < $compare_val ) {
										$matched = true;
									}
									break;

								case '>=':
									if ( $meta_val >= $compare_val ) {
										$matched = true;
									}
									break;

								case '<=':
									if ( $meta_val <= $compare_val ) {
										$matched = true;
									}
									break;

								case 'LIKE':
									if ( false !== strpos( $compare_val, $meta_val ) ) {
										$matched = true;
									}
									break;

								case 'NOT LIKE':
									if ( false === strpos( $compare_val, $meta_val ) ) {
										$matched = true;
									}
									break;

								case 'IN':
									$compare_val = explode( ',', $compare_val );
									$compare_val = array_map( 'trim', $compare_val );

									if ( in_array( $meta_val, $compare_val ) ) {
										$matched = true;
									}

									break;

								case 'NOT IN':
									$compare_val = explode( ',', $compare_val );
									$compare_val = array_map( 'trim', $compare_val );

									if ( ! in_array( $meta_val, $compare_val ) ) {
										$matched = true;
									}

									break;

								case 'BETWEEN':
									$compare_val = explode( ',', $compare_val );
									$compare_val = array_map( 'trim', $compare_val );

									$from = ! isset( $compare_val[0] ) ? $compare_val[0] : 0;
									$to   = ! isset( $compare_val[1] ) ? $compare_val[1] : 0;

									if ( ( $from <= $meta_val ) && ( $meta_val <= $to ) ) {
										$matched = true;
									}

									break;

								case 'NOT BETWEEN':

									$compare_val = explode( ',', $compare_val );
									$compare_val = array_map( 'trim', $compare_val );

									$from = ! isset( $compare_val[0] ) ? $compare_val[0] : 0;
									$to   = ! isset( $compare_val[1] ) ? $compare_val[1] : 0;

									if ( ( $meta_val < $from ) || ( $to < $meta_val ) ) {
										$matched = true;
									}

									break;

							}

							if ( $matched ) {
								if ( $once ) {
									if ( ! isset( $this->injected_counter[ $item['item'] ] ) ) {
										$this->increase_count( $item['item'], $i, $item );
										$result = $item['item'];
									}
								} else {
									$this->increase_count( $item['item'], $i, $item );
									$result = $item['item'];
								}
							}

						}

						break;
				}

				if ( $result ) {
					if ( ! empty( $item['static_item'] ) ) {
						$post = apply_filters( 'jet-engine/listing-injections/static-item-post', $post, $item, $settings, $widget );
						if ( $post ) {
							echo $this->get_injected_item_content( $result, $post );
						}
						//return false;
					} else {
						return $result;
					}
				}

			}

			return false;

		}

		/**
		 * Sort items. Move static items to the top of the list of items.
		 *
		 * @param  array $items
		 * @return array
		 */
		public function sort_items( $items ) {

			$static_items = wp_list_pluck( $items, 'static_item' );

			array_multisort( $items, SORT_DESC, $static_items );

			return $items;
		}

		/**
		 * Increase injected items counter
		 *
		 * @return [type] [description]
		 */
		public function increase_count( $item_id, $i, $item ) {

			if ( ! isset( $this->injected_counter[ $item_id ] ) ) {
				$this->injected_counter[ $item_id ] = 0;
			}

			$this->injected_counter[ $item_id ]++;
			$this->injected_indexes[ $i ] = $item;

		}

		/**
		 * Register listing injection settings
		 *
		 * @param [type] $widget [description]
		 */
		public function add_settings( $widget ) {

			$widget->add_control(
				'inject_alternative_items',
				array(
					'label'        => __( 'Inject alternative listing items', 'jet-engine' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'description'  => '',
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$items_repeater = new \Elementor\Repeater();

			$items_repeater->add_control(
				'item',
				array(
					'label'   => __( 'Listing template', 'jet-engine' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => '',
					'options' => jet_engine()->listings->get_listings_for_options(),
				)
			);

			$items_repeater->add_control(
				'item_condition_type',
				array(
					'label'   => __( 'Inject on', 'jet-engine' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => 'on_item',
					'options' => array(
						'on_item'   => __( 'On each N item', 'jet-engine' ),
						'item_meta' => __( 'Depends on item meta field value', 'jet-engine' ),
					),
				)
			);

			$items_repeater->add_control(
				'inject_once',
				array(
					'label'        => __( 'Inject this item only once', 'jet-engine' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'description'  => '',
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$items_repeater->add_control(
				'item_num',
				array(
					'label'       => __( 'Item number', 'jet-engine' ),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'default'     => 2,
					'min'         => 1,
					'max'         => 1000,
					'step'        => 1,
					'condition'   => array(
						'item_condition_type' => 'on_item',
					),
				)
			);

			$items_repeater->add_control(
				'start_from_first',
				array(
					'label'        => __( 'Start from first', 'jet-engine' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'description'  => __( 'If checked - alternative item will be injected on first item and then on each N item after first. If not - on each N item from start.', 'jet-engine' ),
					'default'      => '',
				)
			);

			$items_repeater->add_control(
				'meta_key',
				array(
					'label'   => __( 'Key (name/ID)', 'jet-engine' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => '',
					'condition' => array(
						'item_condition_type' => 'item_meta'
					),
				)
			);

			$items_repeater->add_control(
				'meta_key_compare',
				array(
					'label'   => __( 'Operator', 'jet-engine' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'default' => '=',
					'options' => array(
						'='           => __( 'Equal', 'jet-engine' ),
						'!='          => __( 'Not equal', 'jet-engine' ),
						'>'           => __( 'Greater than', 'jet-engine' ),
						'>='          => __( 'Greater or equal', 'jet-engine' ),
						'<'           => __( 'Less than', 'jet-engine' ),
						'<='          => __( 'Equal or less', 'jet-engine' ),
						'LIKE'        => __( 'Like', 'jet-engine' ),
						'NOT LIKE'    => __( 'Not like', 'jet-engine' ),
						'IN'          => __( 'In', 'jet-engine' ),
						'NOT IN'      => __( 'Not in', 'jet-engine' ),
						'BETWEEN'     => __( 'Between', 'jet-engine' ),
						'NOT BETWEEN' => __( 'Not between', 'jet-engine' ),
					),
					'condition'   => array(
						'item_condition_type' => 'item_meta'
					),
				)
			);

			$items_repeater->add_control(
				'meta_key_val',
				array(
					'label'       => __( 'Value', 'jet-engine' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => '',
					'label_block' => true,
					'description' => __( 'For <b>In</b>, <b>Not in</b>, <b>Between</b> and <b>Not between</b> compare separate multiple values with comma', 'jet-engine' ),
					'condition'   => array(
						'item_condition_type' => 'item_meta'
					),
				)
			);

			$items_repeater->add_control(
				'item_colspan',
				array(
					'label'       => __( 'Column span', 'jet-engine' ),
					'type'        => \Elementor\Controls_Manager::SELECT,
					'default'     => 1,
					'description' => __( 'Note: Can\'t be bigger than Columns Number value', 'jet-engine' ),
					'options'     => array(
						1 => 1,
						2 => 2,
						3 => 3,
						4 => 4,
						5 => 5,
						6 => 6,
					),
				)
			);

			$items_repeater->add_control(
				'static_item',
				array(
					'label'        => __( 'Static item', 'jet-engine' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'description'  => __( 'If checked - alternative item will be injected without current post context. Use this to inject static items into listing.', 'jet-engine' ),
					'default'      => '',
				)
			);

			do_action( 'jet-engine/listing-injections/item-controls', $items_repeater, $widget );

			$widget->add_control(
				'injection_items',
				array(
					'type'      => \Elementor\Controls_Manager::REPEATER,
					'fields'    => $items_repeater->get_controls(),
					'default'   => array(),
					'condition' => array(
						'inject_alternative_items' => 'yes',
					)
				)
			);

		}

	}

}
