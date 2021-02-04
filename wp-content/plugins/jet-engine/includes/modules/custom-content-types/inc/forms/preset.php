<?php
namespace Jet_Engine\Modules\Custom_Content_Types\Forms;

use Jet_Engine\Modules\Custom_Content_Types\Module;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Notification class
 */
class Preset {

	public $preset_source = 'custom_content_type';

	public function __construct() {
		add_filter( 'jet-engine/forms/preset-sources', array( $this, 'register_source' ) );
		add_filter( 'jet-engine/forms/preset-value/' . $this->preset_source, array( $this, 'apply_preset' ), 10, 3 );

		add_action( 'jet-engine/forms/preset-editor/custom-controls-source', array( $this, 'preset_controls_source' ) );
		add_action( 'jet-engine/forms/preset-editor/custom-controls-global', array( $this, 'preset_controls_global' ) );
		add_action( 'jet-engine/forms/preset-editor/custom-controls-field', array( $this, 'preset_controls_field' ) );
	}

	public function apply_preset( $value, $field_data, $args ) {

		$key = ! empty( $field_data['key'] ) ? $field_data['key'] : false;

		if ( ! $key ) {
			return $value;
		}

		$key = explode( '::', $key );

		if ( 2 !== count( $key ) ) {
			return $value;
		}

		$content_type = Module::instance()->manager->get_content_types( $key[0] );
		$field = $key[1];

		$from = ! empty( $args['post_from'] ) ? $args['post_from'] : 'current_post';
		$query_var = ! empty( $args['query_var'] ) ? $args['query_var'] : false;
		$item = false;

		if ( 'current_post' === $from ) {
			
			$item = $content_type->db->get_queried_item();

			if ( ! $item ) {
				$post_id = get_the_ID();
				if ( $post_id ) {
					$item = Module::instance()->manager->get_item_for_post( $post_id, $content_type );
				}
				
			}

		} else {
			
			$item_id = ! empty( $_REQUEST[ $query_var ] ) ? $_REQUEST[ $query_var ] : false;

			if ( $item_id ) {
				$item = $content_type->db->get_item( $item_id );
			}

		}

		if ( ! $item ) {
			return $value;
		} else {
			return isset( $item[ $field ] ) ? $item[ $field ] : $value;
		}

	}

	public function register_source( $sources ) {

		$sources[] = array(
			'value' => $this->preset_source,
			'label' => __( 'Custom Content Type', 'jet-engine' ),
		);

		return $sources;

	}

	public function preset_controls_source() {
		?>
		<div class="jet-form-canvas__preset-row" v-if="'<?php echo $this->preset_source; ?>' === preset.from">
			<span><?php _e( 'Get item ID from:', 'jet-engine' ); ?></span>
			<select type="text" name="_preset[post_from]" v-model="preset.post_from">
				<option value="current_post"><?php _e( 'Current post', 'jet-engine' ); ?></option>
				<option value="query_var"><?php _e( 'URL Query Variable', 'jet-engine' ); ?></option>
			</select>
		</div>
		<div class="jet-form-canvas__preset-row" v-if="'<?php echo $this->preset_source; ?>' === preset.from && 'query_var' === preset.post_from">
			<span><?php _e( 'Query variable name:', 'jet-engine' ); ?></span>
			<input type="text" name="_preset[query_var]" v-model="preset.query_var">
		</div>
		<?php
	}

	public function preset_controls_global() {
		$this->preset_controls( "_preset[fields_map][' + field + '][key]", "preset.fields_map[ field ].key" );
	}

	public function preset_controls_field() {
		$this->preset_controls( "current_field_key", "preset.current_field_key" );
	}

	public function preset_controls( $name, $model ) {
		?>
		<div class="jet-post-field-control__inner" v-if="'<?php echo $this->preset_source; ?>' === preset.from">
			<select :name="'<?php echo $name; ?>'" v-model="<?php echo $model ?>">
				<option value=""><?php _e( 'Select custom content type field...', 'jet-engine' ); ?></option>
				<?php
					foreach ( Module::instance()->manager->get_content_types() as $type => $instance ) {

						$fields = $instance->get_fields_list( 'all' );
						$prefixed_fields = array();

						if ( empty( $fields ) ) {
							continue;
						}

						echo '<optgroup label="' . $instance->get_arg( 'name' ) . '">';

						printf( '<option value="%1$s">%2$s</option>', $type . '::_ID', __( 'Item ID', 'jet-engine' ) );

						foreach ( $fields as $key => $label ) {
							printf( '<option value="%1$s">%2$s</option>', $type . '::' . $key, $label );
						}

						echo '</optgroup>';

					}
				?>
			</select>
		</div>
		<?php
	}

}
