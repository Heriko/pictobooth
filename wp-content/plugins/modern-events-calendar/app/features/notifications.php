<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Notifications Per Event class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_notifications extends MEC_base
{
    public $factory;
    public $main;
    public $settings;
    public $notif_settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();

        // MEC Notification Settings
        $this->notif_settings = $this->main->get_notifications();
    }
    
    /**
     * Initialize notifications feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Module is disabled
        if(!isset($this->settings['notif_per_event']) or (isset($this->settings['notif_per_event']) and !$this->settings['notif_per_event'])) return;

        $this->factory->action('mec_metabox_details', array($this, 'meta_box_notifications'), 30);
    }
    
    /**
     * Show notification meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_notifications($post)
    {
        $values = get_post_meta($post->ID, 'mec_notifications', true);
        if(!is_array($values)) $values = array();

        $notifications = $this->get_notifications();
    ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-notifications">
            <?php foreach($notifications as $key => $notification): if(isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['status']) and !$this->notif_settings[$key]['status']) continue; ?>
			<div class="mec-form-row">
                <h4><?php echo $notification['label']; ?></h4>
                <div class="mec-form-row">
                    <label>
                        <input type="hidden" name="mec[notifications][<?php echo $key; ?>][status]" value="0" />
                        <input onchange="jQuery('#mec_notification_<?php echo $key; ?>_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][<?php echo $key; ?>][status]" <?php if(isset($values[$key]) and isset($values[$key]['status']) and $values[$key]['status']) echo 'checked="checked"'; ?> /> <?php echo __("Modify", 'mec'); ?>
                    </label>
                </div>
                <div id="mec_notification_<?php echo $key; ?>_container_toggle" class="<?php if(!isset($values[$key]) or (isset($values[$key]) and !$values[$key]['status'])) echo 'mec-util-hidden'; ?>">
                    <div class="mec-form-row">
                        <div class="mec-col-2">
                            <label for="mec_notifications_<?php echo $key; ?>_subject"><?php esc_html_e('Email Subject', 'mec'); ?></label>
                        </div>
                        <div class="mec-col-10">
                            <input id="mec_notifications_<?php echo $key; ?>_subject" type="text" name="mec[notifications][<?php echo $key; ?>][subject]" value="<?php echo ((isset($values[$key]) and isset($values[$key]['subject']) and trim($values[$key]['subject'])) ? $values[$key]['subject'] : ((isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['subject']) and trim($this->notif_settings[$key]['subject'])) ? $this->notif_settings[$key]['subject'] : '')); ?>">
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <div class="mec-col-2">
                            <label for="mec_notifications_<?php echo $key; ?>_content"><?php esc_html_e('Email Content', 'mec'); ?></label>
                        </div>
                        <div class="mec-col-10">
                            <?php wp_editor(((isset($values[$key]) and isset($values[$key]['content']) and trim($values[$key]['content'])) ? $values[$key]['content'] : ((isset($this->notif_settings[$key]) and isset($this->notif_settings[$key]['content']) and trim($this->notif_settings[$key]['content'])) ? $this->notif_settings[$key]['content'] : '')), 'mec_notifications_'.$key.'_content', array('textarea_name'=>'mec[notifications]['.$key.'][content]')); ?>
                        </div>
                    </div>
                </div>
			</div>
            <?php endforeach; ?>
            <h4><?php echo __('Placeholders', 'mec'); ?></h4>
            <ul>
                <li><span>%%first_name%%</span>: <?php _e('First name of attendee', 'mec'); ?></li>
                <li><span>%%last_name%%</span>: <?php _e('Last name of attendee', 'mec'); ?></li>
                <li><span>%%user_email%%</span>: <?php _e('Email of attendee', 'mec'); ?></li>
                <li><span>%%book_date%%</span>: <?php _e('Booked date of event', 'mec'); ?></li>
                <li><span>%%book_time%%</span>: <?php _e('Booked time of event', 'mec'); ?></li>
                <li><span>%%book_datetime%%</span>: <?php _e('Booked date and time of event', 'mec'); ?></li>
                <li><span>%%book_date_next_occurrences%%</span>: <?php _e('Date of next 20 occurrences of booked event (including the booked date)', 'mec'); ?></li>
                <li><span>%%book_datetime_next_occurrences%%</span>: <?php _e('Date and Time of next 20 occurrences of booked event (including the booked date)', 'mec'); ?></li>
                <li><span>%%book_price%%</span>: <?php _e('Booking Price', 'mec'); ?></li>
                <li><span>%%attendee_price%%</span>: <?php _e('Attendee Price (for booking confirmation notification)', 'mec'); ?></li>
                <li><span>%%book_order_time%%</span>: <?php _e('Date and time of booking', 'mec'); ?></li>
                <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                <li><span>%%event_title%%</span>: <?php _e('Event title', 'mec'); ?></li>
                <li><span>%%event_link%%</span>: <?php _e('Event link', 'mec'); ?></li>
                <li><span>%%event_speaker_name%%</span>: <?php _e('Speaker name of booked event', 'mec'); ?></li>
                <li><span>%%event_organizer_name%%</span>: <?php _e('Organizer name of booked event', 'mec'); ?></li>
                <li><span>%%event_organizer_tel%%</span>: <?php _e('Organizer tel of booked event', 'mec'); ?></li>
                <li><span>%%event_organizer_email%%</span>: <?php _e('Organizer email of booked event', 'mec'); ?></li>
                <li><span>%%event_other_organizers_name%%</span>: <?php _e('Additional organizers name of booked event', 'mec'); ?></li>
                <li><span>%%event_other_organizers_tel%%</span>: <?php _e('Additional organizers tel of booked event', 'mec'); ?></li>
                <li><span>%%event_other_organizers_email%%</span>: <?php _e('Additional organizers email of booked event', 'mec'); ?></li>
                <li><span>%%event_location_name%%</span>: <?php _e('Location name of booked event', 'mec'); ?></li>
                <li><span>%%event_location_address%%</span>: <?php _e('Location address of booked event', 'mec'); ?></li>
                <li><span>%%event_other_locations_name%%</span>: <?php _e('Additional locations name of booked event', 'mec'); ?></li>
                <li><span>%%event_other_locations_address%%</span>: <?php _e('Additional locations address of booked event', 'mec'); ?></li>
                <li><span>%%event_featured_image%%</span>: <?php _e('Featured image of booked event', 'mec'); ?></li>
                <li><span>%%event_more_info%%</span>: <?php _e('Event more info link', 'mec'); ?></li>
                <li><span>%%event_other_info%%</span>: <?php _e('Event other info link', 'mec'); ?></li>
                <li><span>%%online_link%%</span>: <?php _e('Event online link', 'mec'); ?></li>
                <li><span>%%attendees_full_info%%</span>: <?php _e('Full Attendee info such as booking form data, name, email etc.', 'mec'); ?></li>
                <li><span>%%booking_id%%</span>: <?php _e('Booking ID', 'mec'); ?></li>
                <li><span>%%booking_transaction_id%%</span>: <?php _e('Transaction ID of Booking', 'mec'); ?></li>
                <li><span>%%admin_link%%</span>: <?php _e('Admin booking management link.', 'mec'); ?></li>
                <li><span>%%total_attendees%%</span>: <?php _e('Total attendees of current booking', 'mec'); ?></li>
                <li><span>%%amount_tickets%%</span>: <?php _e('Amount of Booked Tickets (Total attendees of all bookings)', 'mec'); ?></li>
                <li><span>%%ticket_name%%</span>: <?php _e('Ticket name', 'mec'); ?></li>
                <li><span>%%ticket_time%%</span>: <?php _e('Ticket time', 'mec'); ?></li>
                <li><span>%%ticket_name_time%%</span>: <?php _e('Ticket name & time', 'mec'); ?></li>
                <li><span>%%payment_gateway%%</span>: <?php _e('Payment Gateway', 'mec'); ?></li>
                <li><span>%%dl_file%%</span>: <?php _e('Link to the downloadable file', 'mec'); ?></li>
                <li><span>%%google_calendar_link%%</span>: <?php _e('Add to Google Calendar', 'mec'); ?></li>
                <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php _e('Add to Google Calendar Links for next 20 occurrences', 'mec'); ?></li>
                <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
                <li><span>%%event_status%%</span>: <?php _e('Status of event', 'mec'); ?></li>
                <li><span>%%event_note%%</span>: <?php _e('Event Note', 'mec'); ?></li>
                <?php do_action('mec_extra_field_notifications'); ?>
            </ul>
		</div>
    <?php
    }

    public function get_notifications()
    {
        return array(
            'email_verification' => array(
                'label' => __('Email Verification', 'mec')
            ),
            'booking_notification' => array(
                'label' => __('Booking Notification', 'mec')
            ),
            'booking_confirmation' => array(
                'label' => __('Booking Confirmation', 'mec')
            ),
            'cancellation_notification' => array(
                'label' => __('Booking Cancellation', 'mec')
            ),
            'admin_notification' => array(
                'label' => __('Admin Notification', 'mec')
            ),
            'booking_reminder' => array(
                'label' => __('Booking Reminder', 'mec')
            ),
        );
    }
}