<?php
/**
 * Misc functions
 */

/**
 * Includes Jet_Engine_Img_Gallery class if it was not included before
 *
 * @return void
 */
function jet_engine_get_gallery() {
	if ( ! class_exists( 'Jet_Engine_Img_Gallery' ) ) {
		require_once jet_engine()->plugin_path( 'includes/classes/gallery.php' );
	}
}

/**
 * Callback for filter field option
 *
 * @return void
 */
function jet_engine_img_gallery_slider( $value = null, $args = array() ) {

	if ( is_array( $value ) ) {
		$value = implode( ',', $value );
	}

	return jet_engine()->listings->filters->img_gallery_slider( $value, $args );
}

/**
 * Callback for filter field option
 *
 * @return void
 */
function jet_engine_img_gallery_grid( $value = null, $args = array() ) {

	if ( is_array( $value ) ) {
		$value = implode( ',', $value );
	}

	return jet_engine()->listings->filters->img_gallery_grid( $value, $args );
}

/**
 * Returns image size array in slug => name format
 *
 * @return  array
 */
function jet_engine_get_image_sizes() {

	global $_wp_additional_image_sizes;

	$sizes  = get_intermediate_image_sizes();
	$result = array();

	foreach ( $sizes as $size ) {
		if ( in_array( $size, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) ) {
			$result[ $size ] = ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) );
		} else {
			$result[ $size ] = sprintf(
				'%1$s (%2$sx%3$s)',
				ucwords( trim( str_replace( array( '-', '_' ), array( ' ', ' ' ), $size ) ) ),
				$_wp_additional_image_sizes[ $size ]['width'],
				$_wp_additional_image_sizes[ $size ]['height']
			);
		}
	}

	return array_merge( array( 'full' => esc_html__( 'Full', 'jet-engine' ), ), $result );
}

/**
 * Sanitize WYSIWYG field
 *
 * @return string
 */
function jet_engine_sanitize_wysiwyg( $input ) {
	$input = wpautop( $input );
	return wp_kses_post( $input );
}

/**
 * Sanitize Textarea field
 *
 * @return string
 */
function jet_engine_sanitize_textarea( $input ) {
	return wp_check_invalid_utf8( $input, true );
}

/**
 * Return multiselect values as string with passed delimiter
 *
 * @param  [type] $value     [description]
 * @param  [type] $delimiter [description]
 * @return [type]            [description]
 */
function jet_engine_render_multiselect( $value = null, $delimiter = ', ' ) {

	if ( ! $value || ! is_array( $value ) ) {
		return $value;
	}

	return wp_kses_post( implode( $delimiter, $value ) );

}

/**
 * Returns prepared checkbox values list
 *
 * @param  [type] $value [description]
 * @return [type]        [description]
 */
function jet_engine_get_prepared_check_values( $value = null ) {

	$result = array();

	if ( in_array( 'true', $value ) || in_array( 'false', $value ) ) {
		foreach ( $value as $key => $val ) {
			if ( 'true' === $val ) {
				$result[] = $key;
			}
		}
	} else {
		$result = $value;
	}

	return $result;
}

/**
 * Return checkbox values as string with passed delimiter
 *
 * @param  [type] $value     [description]
 * @param  [type] $delimiter [description]
 * @return [type]            [description]
 */
function jet_engine_render_checkbox_values( $value = null, $delimiter = ', ' ) {

	if ( ! $value || ! is_array( $value ) ) {
		return $value;
	}

	$result = jet_engine_get_prepared_check_values( $value );

	return wp_kses_post( implode( $delimiter, $result ) );

}

/**
 * Return checkbox values as checkd list
 *
 * @param  [type] $value     [description]
 * @param  [type] $delimiter [description]
 * @return [type]            [description]
 */
function jet_engine_render_checklist( $value = null, $icon = null, $columns = 1, $divider = false ) {

	if ( ! $value || ! is_array( $value ) ) {
		return $value;
	}

	$result = jet_engine_get_prepared_check_values( $value );

	if ( empty( $result ) ) {
		return '';
	}

	ob_start();

	$classes = array(
		'jet-check-list',
		'jet-check-list--columns-' . $columns,
	);

	if ( $divider ) {
		$classes[] = 'jet-check-list--has-divider';
	}

	echo '<div class="' . implode( ' ', $classes ) . '">';

	foreach ( $result as $item ) {
		printf( '<div class="jet-check-list__item">%2$s<div class="jet-check-list__item-content">%1$s</div></div>', $item, $icon );
	}

	echo '</div>';

	return ob_get_clean();

}

/**
 * Render filtered switcher result
 *
 * @param  [type] $value       [description]
 * @param  [type] $true_label  [description]
 * @param  [type] $false_label [description]
 * @return [type]              [description]
 */
function jet_engine_render_switcher( $value = null, $true_label = null, $false_label = null ) {

	$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );

	if ( $value ) {
		return $true_label;
	} else {
		return $false_label;
	}

}

/**
 * Return checkbox values as string with passed delimiter
 *
 * @param  [type] $value     [description]
 * @param  [type] $delimiter [description]
 * @return [type]            [description]
 */
function jet_engine_render_acf_checkbox_values( $value = null, $delimiter = ', ' ) {

	if ( ! $value || ! is_array( $value ) ) {
		return $value;
	}

	return wp_kses_post( implode( $delimiter, $value ) );

}

/**
 * Return post titles from post IDs array as string with passed delimiter
 *
 * @param  [type] $value     [description]
 * @param  [type] $delimiter [description]
 * @return [type]            [description]
 */
function jet_engine_render_post_titles( $value = null, $delimiter = ', ' ) {

	if ( ! $value || ! is_array( $value ) ) {
		return $value;
	}

	return wp_kses_post( implode( $delimiter, array_map( 'get_the_title', $value ) ) );

}

/**
 * Returns link to post by ID
 *
 * @return [type] [description]
 */
function jet_get_pretty_post_link( $value ) {

	if ( empty( $value ) ) {
		return;
	}

	$result = '';

	if ( is_array( $value ) ) {

		$delimiter = '';

		foreach ( $value as $post_id ) {

			$result .= sprintf(
				'%3$s<a href="%1$s">%2$s</a>',
				get_permalink( $post_id ),
				get_the_title( $post_id ),
				$delimiter
			);

			$delimiter = ', ';

		}

	} else {
		$post_id = $value;
		$result  = sprintf( '<a href="%1$s">%2$s</a>', get_permalink( $post_id ), get_the_title( $post_id ) );
	}

	return $result;

}

/**
 * Return icon HTML for icon, set in JetEngine iconpicker
 *
 * @param  string $value Icon class
 * @return string
 */
function jet_engine_icon_html( $value = null ) {

	$format = apply_filters(
		'jet-engine/listings/icon-html-format',
		'<i class="fa %s"></i>'
	);

	return sprintf( $format, $value );

}

/**
 * Returns QR code for meta value
 *
 * @return string
 */
function jet_engine_get_qr_code( $meta_value = null, $size = 150 ) {

	$qr_code = jet_engine()->modules->get_module( 'qr-code' );
	return $qr_code->get_qr_code( $meta_value, $size );

}

/**
 * Render related posts array as HTML list
 *
 * @param  array  $related_posts [description]
 * @return [type]                [description]
 */
function jet_related_posts_list( $related_posts = array(), $tag = 'ul', $is_single = false, $is_linked = true, $delimiter = '' ) {

	if ( ! is_array( $related_posts ) ) {
		$related_posts = array_filter( array( absint( $related_posts ) ) );
	}

	if ( empty( $related_posts ) ) {
		return;
	}

	switch ( $tag ) {
		case 'ol':
			$parent_tag = 'ol';
			$child_tag  = 'li';
			break;

		case 'div':
			$parent_tag = 'div';
			$child_tag  = 'span';
			break;

		default:
			$parent_tag = 'ul';
			$child_tag  = 'li';
			break;
	}

	if ( $is_single ) {
		$related_posts = array( $related_posts[0] );
	}

	ob_start();

	printf( '<%s>', $parent_tag );

	$count = count( $related_posts );
	$i     = 1;

	foreach ( $related_posts as $post_id ) {

		if ( $i === $count ) {
			$delimiter = '';
		}

		if ( $is_linked ) {

			printf(
				'<%1$s><a href="%3$s">%2$s</a>%4$s</%1$s>',
				$child_tag,
				get_the_title( $post_id ),
				get_permalink( $post_id ),
				$delimiter
			);

		} else {

			printf(
				'<%1$s>%2$s%3$s</%1$s>',
				$child_tag,
				get_the_title( $post_id ),
				$delimiter
			);

		}

		$i++;
	}

	printf( '</%s>', $parent_tag );

	return ob_get_clean();

}

/**
 * Returns formatted date from post meta by post id, field and format string
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  string  $format  [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_date( $post_id = 0, $field = '', $format = '' ) {

	$value = get_post_meta( $post_id, $field, true );

	if ( $value ) {
		return date_i18n( $format, $value );
	} else {
		return null;
	}

}

/**
 * Returns post link from post meta by post id, field and format string
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  string  $format  [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_pretty_post_link( $post_id = 0, $field = '' ) {

	$value = get_post_meta( $post_id, $field, true );

	if ( $value ) {
		return jet_get_pretty_post_link( $value );
	} else {
		return null;
	}

}

/**
 * Returns menu order value from current post
 *
 * @param  integer $post_id [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_menu_order( $post_id = 0 ) {

	$post = get_post( $post_id );

	if ( ! $post || is_wp_error( $post ) ) {
		return null;
	} else {
		return $post->menu_order;
	}

}

/**
 * Returns post link from post meta by post id, field and format string
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  string  $format  [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_related_posts( $post_id = 0, $field = '' ) {

	$value = get_post_meta( $post_id, $field, false );

	if ( $value ) {
		return jet_get_pretty_post_link( $value );
	} else {
		return null;
	}

}

/**
 * Returns rendered switcher value from post meta by post id, field and format string
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  string  $format  [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_render_switcher( $post_id = 0, $field = '', $true_label = '', $false_label = '' ) {
	$value = get_post_meta( $post_id, $field, true );
	return jet_engine_render_switcher( $value, $true_label, $false_label );
}

/**
 * Returns rendered checkbox values from post meta by post id, field and format string
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  string  $format  [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_render_checkbox( $post_id = 0, $field = '', $delimiter = ', ' ) {

	$value = get_post_meta( $post_id, $field, true );

	if ( $value ) {
		return jet_engine_render_checkbox_values( $value, $delimiter );
	} else {
		return null;
	}

}

/**
 * Render image tag by post id, meta field and pased size
 *
 * @param  integer $post_id [description]
 * @param  string  $field   [description]
 * @param  integer $size    [description]
 * @return [type]           [description]
 */
function jet_engine_custom_cb_render_image( $post_id = 0, $field = 'thumbnail', $size = 100 ) {

	$size = absint( $size );

	if ( ! $size ) {
		$size = 100;
	}

	if ( 'thumbnail' === $field ) {
		if ( has_post_thumbnail( $post_id ) ) {
			return get_the_post_thumbnail( $post_id, array( $size, $size ) );
		} else {
			return null;
		}
	} else {
		$value = get_post_meta( $post_id, $field, true );

		if ( $value ) {
			return wp_get_attachment_image( $value, array( $size, $size ) );
		} else {
			return null;
		}

	}

}

/**
 * Render field values count.
 *
 * @param  array $values
 * @return int|void
 */
function jet_engine_render_field_values_count( $values = array() ) {

	if ( empty( $values ) ) {
		return;
	}

	if ( ! is_array( $values ) ) {
		return 1;
	}

	return count( $values );
}

/**
 * Returns rendered select value from post meta by post id, field string
 *
 * @param  integer $post_id
 * @param  string  $field
 * @param  string  $delimeter
 * @return mixed
 */
function jet_engine_custom_cb_render_select( $post_id = 0, $field = '', $delimeter = ', ' ) {
	$value = get_post_meta( $post_id, $field, true );

	if ( ! $value ) {
		return null;
	}

	$post_type   = get_post_type( $post_id );
	$all_fields  = jet_engine()->meta_boxes->get_registered_fields();
	$found_field = null;
	$result      = array();

	if ( ! isset( $all_fields[ $post_type ] ) ) {
		return is_array( $value ) ? wp_kses_post( implode( $delimeter, $value ) ) : wp_kses_post( $value );
	}

	foreach ( $all_fields[ $post_type ] as $field_data ) {
		if ( ! empty( $field_data['name'] ) && $field === $field_data['name'] ) {
			$found_field = $field_data;
		}
	}

	if ( ! $found_field || empty( $found_field['options'] ) ) {
		return is_array( $value ) ? wp_kses_post( implode( $delimeter, $value ) ) : wp_kses_post( $value );
	}

	foreach ( $found_field['options'] as $option ) {
		if ( is_array( $value ) && in_array( $option['key'], $value ) ) {
			$result[] = $option['value'];
		} elseif ( $value === $option['key'] )  {
			$result[] = $option['value'];
		}
	}

	return wp_kses_post( implode( $delimeter, $result ) );
}

/**
 * Return term title from ID
 *
 * @param mixed $id Term ID.
 *
 * @return string
 */
function jet_engine_get_term_title( $id = null ) {
	$term = get_term( $id );

	if ( is_wp_error( $term ) ) {
		return '';
	}

	return $term->name;
}

/**
 * Return term titles from terms IDs array as a string with passed delimiter
 *
 * @param array  $ids
 * @param string $delimiter
 *
 * @return mixed
 */
function jet_engine_get_term_titles( $ids = array(), $delimiter = ', ' ) {

	if ( ! $ids || ! is_array( $ids ) ) {
		return $ids;
	}

	$titles = array_map( 'jet_engine_get_term_title', $ids );
	$titles = array_filter( $titles );

	return wp_kses_post( implode( $delimiter, $titles ) );
}
