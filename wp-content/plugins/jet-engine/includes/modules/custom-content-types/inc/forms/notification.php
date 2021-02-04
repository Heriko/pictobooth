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
class Notification {

	public $slug = 'insert_custom_content_type';

	public function __construct() {

		add_action(
			'jet-engine/forms/editor/before-assets',
			array( $this, 'assets' )
		);

		add_filter(
			'jet-engine/forms/booking/notification-types',
			array( $this, 'register_notification' )
		);

		add_action(
			'jet-engine/forms/booking/notifications/fields-after',
			array( $this, 'notification_fields' )
		);

		add_filter(
			'jet-engine/forms/booking/notification/' . $this->slug,
			array( $this, 'handle_notification' ),
			1, 2
		);

	}

	/**
	 * Register notification assets
	 * @return [type] [description]
	 */
	public function assets() {

		wp_enqueue_script(
			'jet-engine-cct-notification',
			Module::instance()->module_url( 'assets/js/admin/form-notification.js' ),
			array( 'wp-api-fetch' ),
			jet_engine()->get_version(),
			true
		);

		wp_enqueue_style(
			'jet-engine-cct-notification',
			Module::instance()->module_url( 'assets/css/form-notification.css' ),
			array(),
			jet_engine()->get_version()
		);

		add_action( 'admin_footer', array( $this, 'notification_component_template' ) );

	}

	/**
	 * Print notification component template
	 *
	 * @return [type] [description]
	 */
	public function notification_component_template() {

		ob_start();
		include Module::instance()->module_path( 'templates/admin/notification-component.php' );
		$content = ob_get_clean();

		printf(
			'<script type="text/x-template" id="jet-cct-notification">%s</script>',
			$content
		);

	}

	/**
	 * Register new notification type
	 *
	 * @return [type] [description]
	 */
	public function register_notification( $notifications ) {
		$notifications[ $this->slug ] = __( 'Insert/Update Custom Content Type Item', 'jet-engine' );
		return $notifications;
	}

	/**
	 * Render additional notification fields
	 *
	 * @return [type] [description]
	 */
	public function notification_fields() {

		$content_types = array();
		$statuses      = array();
		$action_slug   = $this->slug;

		foreach ( Module::instance()->manager->get_content_types() as $slug => $type ) {

			if ( empty( $statuses ) ) {
				$statuses = $type->get_statuses();
			}

			$name = $type->get_arg( 'name' );
			$name = $name ? $name : $slug;

			$content_types[] = array(
				'value' => $slug,
				'label' => $name,
			);
		}

		if ( empty( $content_types ) ) {
			$content_types = '[]';
		} else {
			$content_types = htmlspecialchars( json_encode( $content_types ) );
		}

		if ( empty( $statuses ) ) {
			$statuses = '[]';
		} else {
			$statuses = htmlspecialchars( json_encode( $statuses ) );
		}

		$fetch_path = Module::instance()->query_dialog()->api_path();

		include Module::instance()->module_path( 'templates/admin/notification-fields.php' );
	}

	/**
	 * Handle form notification
	 *
	 * @return [type] [description]
	 */
	public function handle_notification( $args, $notifications ) {

		$type        = ! empty( $args['cct']['type'] ) ? $args['cct']['type'] : false;
		$status      = ! empty( $args['cct']['status'] ) ? $args['cct']['status'] : 'publish';
		$fields      = ! empty( $args['cct']['fields_map'] ) ? $args['cct']['fields_map'] : array();
		$type_object = false;

		if ( $type ) {
			$type_object = Module::instance()->manager->get_content_types( $type );
		}

		if ( ! $type_object ) {
			$notifications->log[] = $notifications->set_specific_status( 'Internal error! Please contact website administrator. Error code: content_type_not_found' );
			return false;
		}

		$item = array();

		foreach ( $fields as $form_field => $item_field ) {
			if ( isset( $notifications->data[ $form_field ] ) ) {
				$item[ $item_field ] = $notifications->data[ $form_field ];
			}
		}

		$item['cct_status'] = $status;

		if ( empty( $item ) ) {
			$notifications->log[] = $notifications->set_specific_status( 'Internal error! Please contact website administrator. Error code: fields_mismatch' );
			return false;
		}

		$handler = $type_object->get_item_handler();
		$item_id = $handler->update_item( $item );

		if ( $item_id ) {
			$notifications->log[] = true;
		} else {
			$notifications->log[] = $notifications->set_specific_status( 'Internal error! Please contact website administrator. Error code: cant_update_item' );
			return false;
		}

	}

}
