<?php
/** no direct access **/
defined('MECEXEC') or die();

$notifications = $this->main->get_notifications();
$settings = $this->main->get_settings();
?>
<div class="wns-be-container wns-be-container-sticky">
    <div id="wns-be-infobar">
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' ,'mec'); ?>">
        </div>
        <a href="" id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'mec'); ?></a>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('notifications'); ?>
    </div>

    <div class="wns-be-main">
        <div id="wns-be-notification"></div>
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">

                    <form id="mec_notifications_form">

                        <!-- <ul> -->
                        <?php if($this->main->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status']): ?>

                        <?php do_action( 'mec_notification_menu_start', $this->main, $notifications ); ?>

                        <div id="booking_notification_section" class="mec-options-fields active">

                            <h4 class="mec-form-subtitle"><?php _e('Booking', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][booking_notification][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_booking_notification_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][booking_notification][status]" <?php if(!isset($notifications['booking_notification']['status']) or (isset($notifications['booking_notification']['status']) and $notifications['booking_notification']['status'])) echo 'checked="checked"'; ?> /> <?php _e('Enable booking notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_booking_notification_container_toggle" class="<?php if(isset($notifications['booking_notification']) and isset($notifications['booking_notification']['status']) and !$notifications['booking_notification']['status']) echo 'mec-util-hidden'; ?>">
                                <p class="description"><?php _e('Sent to attendee after booking to notify them.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_notification_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_notification][subject]" id="mec_notifications_booking_notification_subject" value="<?php echo (isset($notifications['booking_notification']['subject']) ? stripslashes($notifications['booking_notification']['subject']) : ''); ?>" />
                                </div>

                               <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_notification_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['booking_notification']['receiver_users']) ? $notifications['booking_notification']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'booking_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_notification_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['booking_notification']['receiver_roles']) ? $notifications['booking_notification']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'booking_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_notification_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_notification][recipients]" id="mec_notifications_booking_notification_recipients" value="<?php echo (isset($notifications['booking_notification']['recipients']) ? $notifications['booking_notification']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span> 
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][booking_notification][send_to_organizer]" value="1" id="mec_notifications_booking_notification_send_to_organizer" <?php echo ((isset($notifications['booking_notification']['send_to_organizer']) and $notifications['booking_notification']['send_to_organizer'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_booking_notification_send_to_organizer"><?php _e('Send the email to event organizer', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_notification_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['booking_notification']) ? stripslashes($notifications['booking_notification']['content']) : ''), 'mec_notifications_booking_notification_content', array('textarea_name'=>'mec[notifications][booking_notification][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                    <li><span>%%book_order_time%%</span>: <?php _e('Date and time of booking', 'mec'); ?></li>
                                    <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                                    <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                                    <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                                    <li><span>%%event_title%%</span>: <?php _e('Event title', 'mec'); ?></li>
                                    <li><span>%%event_link%%</span>: <?php _e('Event link', 'mec'); ?></li>
                                    <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                                    <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
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
                                    <li><span>%%invoice_link%%</span>: <?php _e('Invoice Link', 'mec'); ?></li>
                                    <li><span>%%total_attendees%%</span>: <?php _e('Total attendees of current booking', 'mec'); ?></li>
                                    <li><span>%%amount_tickets%%</span>: <?php _e('Amount of Booked Tickets (Total attendees of all bookings)', 'mec'); ?></li>
                                    <li><span>%%ticket_name%%</span>: <?php _e('Ticket name', 'mec'); ?></li>
                                    <li><span>%%ticket_time%%</span>: <?php _e('Ticket time', 'mec'); ?></li>
                                    <li><span>%%ticket_name_time%%</span>: <?php _e('Ticket name & time', 'mec'); ?></li>
                                    <li><span>%%payment_gateway%%</span>: <?php _e('Payment Gateway', 'mec'); ?></li>
                                    <li><span>%%dl_file%%</span>: <?php _e('Link to the downloadable file', 'mec'); ?></li>
                                    <li><span>%%ics_link%%</span>: <?php _e('Download ICS file', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link%%</span>: <?php _e('Add to Google Calendar', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php _e('Add to Google Calendar Links for next 20 occurrences', 'mec'); ?></li>
                                    <?php do_action('mec_extra_field_notifications'); ?>
                                </ul>
                            </div>
                        </div>

                        <div id="booking_verification" class="mec-options-fields">

                            <h4 class="mec-form-subtitle"><?php _e('Booking Verification', 'mec'); ?></h4>
                            <p class="description"><?php _e('It sends to attendee email for verifying their booking/email.', 'mec'); ?></p>
                            <div class="mec-form-row">
                                <label for="mec_notifications_email_verification_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                <input type="text" name="mec[notifications][email_verification][subject]" id="mec_notifications_email_verification_subject" value="<?php echo (isset($notifications['email_verification']['subject']) ? stripslashes($notifications['email_verification']['subject']) : ''); ?>" />
                            </div>

                            <!-- Start Receiver Users -->
                            <div class="mec-form-row">
                                <label for="mec_notifications_email_verification_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                <?php
                                    $users = isset($notifications['email_verification']['receiver_users']) ? $notifications['email_verification']['receiver_users'] : array();
                                    echo $this->main->get_users_dropdown($users, 'email_verification');
                                ?>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>
                            <!-- End Receiver Users -->

                            <!-- Start Receiver Roles -->
                            <div class="mec-form-row">
                                <label for="mec_notifications_email_verification_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                <?php
                                    $roles = isset($notifications['email_verification']['receiver_roles']) ? $notifications['email_verification']['receiver_roles'] : array();
                                    echo $this->main->get_roles_dropdown($roles, 'email_verification');
                                ?>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>
                            <!-- End Receiver Roles -->

                            <div class="mec-form-row">
                                <label for="mec_notifications_email_verification_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                <input type="text" name="mec[notifications][email_verification][recipients]" id="mec_notifications_email_verification_recipients" value="<?php echo (isset($notifications['email_verification']['recipients']) ? $notifications['email_verification']['recipients'] : ''); ?>" />
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>
                            <div class="mec-form-row">
                                <label for="mec_notifications_email_verification_content"><?php _e('Email Content', 'mec'); ?></label>
                                <?php wp_editor((isset($notifications['email_verification']) ? stripslashes($notifications['email_verification']['content']) : ''), 'mec_notifications_email_verification_content', array('textarea_name'=>'mec[notifications][email_verification][content]')); ?>
                            </div>
                            <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                <li><span>%%book_order_time%%</span>: <?php _e('Date and time of booking', 'mec'); ?></li>
                                <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                                <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                                <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                                <li><span>%%event_title%%</span>: <?php _e('Event title', 'mec'); ?></li>
                                <li><span>%%event_link%%</span>: <?php _e('Event link', 'mec'); ?></li>
                                <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                                <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
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
                                <li><span>%%verification_link%%</span>: <?php _e('Email/Booking verification link.', 'mec'); ?></li>
                                <li><span>%%total_attendees%%</span>: <?php _e('Total attendees of current booking', 'mec'); ?></li>
                                <li><span>%%amount_tickets%%</span>: <?php _e('Amount of Booked Tickets (Total attendees of all bookings)', 'mec'); ?></li>
                                <li><span>%%ticket_name%%</span>: <?php _e('Ticket name', 'mec'); ?></li>
                                <li><span>%%ticket_time%%</span>: <?php _e('Ticket time', 'mec'); ?></li>
                                <li><span>%%ticket_name_time%%</span>: <?php _e('Ticket name & time', 'mec'); ?></li>
                                <li><span>%%payment_gateway%%</span>: <?php _e('Payment Gateway', 'mec'); ?></li>
                                <li><span>%%dl_file%%</span>: <?php _e('Link to the downloadable file', 'mec'); ?></li>
                                <li><span>%%ics_link%%</span>: <?php _e('Download ICS file', 'mec'); ?></li>
                                <li><span>%%google_calendar_link%%</span>: <?php _e('Add to Google Calendar', 'mec'); ?></li>
                                <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php _e('Add to Google Calendar Links for next 20 occurrences', 'mec'); ?></li>
                                <?php do_action('mec_extra_field_notifications'); ?>
                            </ul>

                        </div>

                        <div id="booking_confirmation" class="mec-options-fields">

                            <h4 class="mec-form-subtitle"><?php _e('Booking Confirmation', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][booking_confirmation][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_booking_confirmation_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][booking_confirmation][status]" <?php if(!isset($notifications['booking_confirmation']['status']) or (isset($notifications['booking_confirmation']['status']) and $notifications['booking_confirmation']['status'])) echo 'checked="checked"'; ?> /> <?php _e('Enable booking confirmation', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_booking_confirmation_container_toggle" class="<?php if(isset($notifications['booking_confirmation']) and isset($notifications['booking_confirmation']['status']) and !$notifications['booking_confirmation']['status']) echo 'mec-util-hidden'; ?>">

                                <p class="description"><?php _e('Sent to attendee after confirming the booking by admin.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_confirmation_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_confirmation][subject]" id="mec_notifications_booking_confirmation_subject" value="<?php echo (isset($notifications['booking_confirmation']['subject']) ? stripslashes($notifications['booking_confirmation']['subject']) : ''); ?>" />
                                </div>

                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_confirmation_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                    $users = isset($notifications['booking_confirmation']['receiver_users']) ? $notifications['booking_confirmation']['receiver_users'] : array();
                                    echo $this->main->get_users_dropdown($users, 'booking_confirmation');
                                    ?>
                                    <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_confirmation_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                    $roles = isset($notifications['booking_confirmation']['receiver_roles']) ? $notifications['booking_confirmation']['receiver_roles'] : array();
                                    echo $this->main->get_roles_dropdown($roles, 'booking_confirmation');
                                    ?>
                                    <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_confirmation_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_confirmation][recipients]" id="mec_notifications_booking_confirmation_recipients" value="<?php echo (isset($notifications['booking_confirmation']['recipients']) ? $notifications['booking_confirmation']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][booking_confirmation][send_single_one_email]" value="1" id="mec_notifications_booking_confirmation_send_single_one_email" <?php echo ((isset($notifications['booking_confirmation']['send_single_one_email']) and $notifications['booking_confirmation']['send_single_one_email'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_booking_confirmation_send_single_one_email"><?php _e('Send One Single Email Only To First Attendee', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_confirmation_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['booking_confirmation']) ? stripslashes($notifications['booking_confirmation']['content']) : ''), 'mec_notifications_booking_confirmation_content', array('textarea_name'=>'mec[notifications][booking_confirmation][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                    <li><span>%%attendee_price%%</span>: <?php _e('Attendee Price', 'mec'); ?></li>
                                    <li><span>%%book_order_time%%</span>: <?php _e('Date and time of booking', 'mec'); ?></li>
                                    <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                                    <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                                    <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                                    <li><span>%%event_title%%</span>: <?php _e('Event title', 'mec'); ?></li>
                                    <li><span>%%event_link%%</span>: <?php _e('Event link', 'mec'); ?></li>
                                    <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                                    <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
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
                                    <li><span>%%cancellation_link%%</span>: <?php _e('Booking cancellation link.', 'mec'); ?></li>
                                    <li><span>%%invoice_link%%</span>: <?php _e('Invoice Link', 'mec'); ?></li>
                                    <li><span>%%total_attendees%%</span>: <?php _e('Total attendees of current booking', 'mec'); ?></li>
                                    <li><span>%%amount_tickets%%</span>: <?php _e('Amount of Booked Tickets (Total attendees of all bookings)', 'mec'); ?></li>
                                    <li><span>%%ticket_name%%</span>: <?php _e('Ticket name', 'mec'); ?></li>
                                    <li><span>%%ticket_time%%</span>: <?php _e('Ticket time', 'mec'); ?></li>
                                    <li><span>%%ticket_name_time%%</span>: <?php _e('Ticket name & time', 'mec'); ?></li>
                                    <li><span>%%payment_gateway%%</span>: <?php _e('Payment Gateway', 'mec'); ?></li>
                                    <li><span>%%dl_file%%</span>: <?php _e('Link to the downloadable file', 'mec'); ?></li>
                                    <li><span>%%ics_link%%</span>: <?php _e('Download ICS file', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link%%</span>: <?php _e('Add to Google Calendar', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php _e('Add to Google Calendar Links for next 20 occurrences', 'mec'); ?></li>
                                    <?php do_action('mec_extra_field_notifications'); ?>
                                </ul>

                            </div>

                        </div>

                        <div id="cancellation_notification" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Booking Cancellation', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][cancellation_notification][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_cancellation_notification_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][cancellation_notification][status]" <?php if((isset($notifications['cancellation_notification']['status']) and $notifications['cancellation_notification']['status'])) echo 'checked="checked"'; ?> /> <?php _e('Enable cancellation notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_cancellation_notification_container_toggle" class="<?php if((isset($notifications['cancellation_notification']) and !$notifications['cancellation_notification']['status']) or !isset($notifications['cancellation_notification'])) echo 'mec-util-hidden'; ?>">
                                <p class="description"><?php _e('Sent to selected recipients after booking cancellation to notify them.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_cancellation_notification_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][cancellation_notification][subject]" id="mec_notifications_cancellation_notification_subject" value="<?php echo (isset($notifications['cancellation_notification']['subject']) ? stripslashes($notifications['cancellation_notification']['subject']) : 'Your booking is canceled.'); ?>" />
                                </div>

                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_cancellation_notification_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['cancellation_notification']['receiver_users']) ? $notifications['cancellation_notification']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'cancellation_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_cancellation_notification_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['cancellation_notification']['receiver_roles']) ? $notifications['cancellation_notification']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'cancellation_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_cancellation_notification_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][cancellation_notification][recipients]" id="mec_notifications_cancellation_notification_recipients" value="<?php echo (isset($notifications['cancellation_notification']['recipients']) ? $notifications['cancellation_notification']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][cancellation_notification][send_to_admin]" value="1" id="mec_notifications_cancellation_notification_send_to_admin" <?php echo ((!isset($notifications['cancellation_notification']['send_to_admin']) or $notifications['cancellation_notification']['send_to_admin'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_cancellation_notification_send_to_admin"><?php _e('Send the email to admin', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][cancellation_notification][send_to_organizer]" value="1" id="mec_notifications_cancellation_notification_send_to_organizer" <?php echo ((isset($notifications['cancellation_notification']['send_to_organizer']) and $notifications['cancellation_notification']['send_to_organizer'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_cancellation_notification_send_to_organizer"><?php _e('Send the email to event organizer', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][cancellation_notification][send_to_user]" value="1" id="mec_notifications_cancellation_notification_send_to_user" <?php echo ((isset($notifications['cancellation_notification']['send_to_user']) and $notifications['cancellation_notification']['send_to_user'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_cancellation_notification_send_to_user"><?php _e('Send the email to the booked user', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_cancellation_notification_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['cancellation_notification']) ? stripslashes($notifications['cancellation_notification']['content']) : ''), 'mec_notifications_cancellation_notification_content', array('textarea_name'=>'mec[notifications][cancellation_notification][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                </ul>
                            </div>
                        </div>

                        <div id="admin_notification" class="mec-options-fields">

                            <h4 class="mec-form-subtitle"><?php _e('Admin', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][admin_notification][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_admin_notification_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][admin_notification][status]" <?php if(!isset($notifications['admin_notification']['status']) or (isset($notifications['admin_notification']['status']) and $notifications['admin_notification']['status'])) echo 'checked="checked"'; ?> /> <?php _e('Enable admin notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_admin_notification_container_toggle" class="<?php if(isset($notifications['admin_notification']) and isset($notifications['admin_notification']['status']) and !$notifications['admin_notification']['status']) echo 'mec-util-hidden'; ?>">
                                <p class="description"><?php _e('Sent to admin to notify them that a new booking has been received.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_admin_notification_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][admin_notification][subject]" id="mec_notifications_admin_notification_subject" value="<?php echo (isset($notifications['admin_notification']['subject']) ? stripslashes($notifications['admin_notification']['subject']) : ''); ?>" />
                                </div>
                                
                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_admin_notification_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['admin_notification']['receiver_users']) ? $notifications['admin_notification']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'admin_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_admin_notification_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['admin_notification']['receiver_roles']) ? $notifications['admin_notification']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'admin_notification');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_admin_notification_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][admin_notification][recipients]" id="mec_notifications_admin_notification_recipients" value="<?php echo (isset($notifications['admin_notification']['recipients']) ? $notifications['admin_notification']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][admin_notification][send_to_admin]" value="1" id="mec_notifications_admin_notification_send_to_admin" <?php echo ((!isset($notifications['admin_notification']['send_to_admin']) or $notifications['admin_notification']['send_to_admin'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_admin_notification_send_to_admin"><?php _e('Send the email to admin', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <input type="checkbox" name="mec[notifications][admin_notification][send_to_organizer]" value="1" id="mec_notifications_admin_notification_send_to_organizer" <?php echo ((isset($notifications['admin_notification']['send_to_organizer']) and $notifications['admin_notification']['send_to_organizer'] == 1) ? 'checked="checked"' : ''); ?> />
                                    <label for="mec_notifications_admin_notification_send_to_organizer"><?php _e('Send the email to event organizer', 'mec'); ?></label>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_admin_notification_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['admin_notification']) ? stripslashes($notifications['admin_notification']['content']) : ''), 'mec_notifications_admin_notification_content', array('textarea_name'=>'mec[notifications][admin_notification][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                </ul>
                            </div>
                        </div>

                        <div id="booking_reminder" class="mec-options-fields">

                            <h4 class="mec-form-subtitle"><?php _e('Booking Reminder', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][booking_reminder][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_booking_reminder_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][booking_reminder][status]" <?php if(isset($notifications['booking_reminder']) and $notifications['booking_reminder']['status']) echo 'checked="checked"'; ?> /> <?php _e('Enable booking reminder notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_booking_reminder_container_toggle" class="<?php if((isset($notifications['booking_reminder']) and !$notifications['booking_reminder']['status']) or !isset($notifications['booking_reminder'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <?php $cron = MEC_ABSPATH.'app'.DS.'crons'.DS.'booking-reminder.php'; ?>
                                    <p class="mec-col-12"><strong><?php _e('Important Note', 'mec'); ?>: </strong><?php echo sprintf(__("Set a cronjob to call %s file once per hour otherwise it won't send the reminders. Please note that you should call this file %s otherwise it may send the reminders multiple times.", 'mec'), '<code>'.$cron.'</code>', '<strong>'.__('only once per hour', 'mec').'</strong>'); ?></p>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_reminder][subject]" id="mec_notifications_booking_reminder_subject" value="<?php echo ((isset($notifications['booking_reminder']) and isset($notifications['booking_reminder']['subject'])) ? stripslashes($notifications['booking_reminder']['subject']) : ''); ?>" />
                                </div>

                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['booking_reminder']['receiver_users']) ? $notifications['booking_reminder']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'booking_reminder');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['booking_reminder']['receiver_roles']) ? $notifications['booking_reminder']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'booking_reminder');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_reminder][recipients]" id="mec_notifications_booking_reminder_recipients" value="<?php echo ((isset($notifications['booking_reminder']) and isset($notifications['booking_reminder']['recipients'])) ? $notifications['booking_reminder']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_hours"><?php _e('Hours', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][booking_reminder][hours]" id="mec_notifications_booking_reminder_hours" value="<?php echo ((isset($notifications['booking_reminder']) and isset($notifications['booking_reminder']['hours'])) ? $notifications['booking_reminder']['hours'] : '24,72,168'); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Reminder hours', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Please, insert comma to separate reminder hours.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_booking_reminder_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['booking_reminder']) ? stripslashes($notifications['booking_reminder']['content']) : ''), 'mec_notifications_booking_reminder_content', array('textarea_name'=>'mec[notifications][booking_reminder][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
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
                                    <li><span>%%cancellation_link%%</span>: <?php _e('Booking cancellation link.', 'mec'); ?></li>
                                    <li><span>%%invoice_link%%</span>: <?php _e('Invoice Link', 'mec'); ?></li>
                                    <li><span>%%total_attendees%%</span>: <?php _e('Total attendees of current booking', 'mec'); ?></li>
                                    <li><span>%%amount_tickets%%</span>: <?php _e('Amount of Booked Tickets (Total attendees of all bookings)', 'mec'); ?></li>
                                    <li><span>%%ticket_name%%</span>: <?php _e('Ticket name', 'mec'); ?></li>
                                    <li><span>%%ticket_time%%</span>: <?php _e('Ticket time', 'mec'); ?></li>
                                    <li><span>%%ticket_name_time%%</span>: <?php _e('Ticket name & time', 'mec'); ?></li>
                                    <li><span>%%payment_gateway%%</span>: <?php _e('Payment Gateway', 'mec'); ?></li>
                                    <li><span>%%dl_file%%</span>: <?php _e('Link to the downloadable file', 'mec'); ?></li>
                                    <li><span>%%ics_link%%</span>: <?php _e('Download ICS file', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link%%</span>: <?php _e('Add to Google Calendar', 'mec'); ?></li>
                                    <li><span>%%google_calendar_link_next_occurrences%%</span>: <?php _e('Add to Google Calendar Links for next 20 occurrences', 'mec'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <?php endif; ?>

                        <div id="new_event" class="mec-options-fields  <?php if(isset($this->settings['booking_status']) and $this->settings['booking_status'] == 0) echo 'active'; ?>">

                            <h4 class="mec-form-subtitle"><?php _e('New Event', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][new_event][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_new_event_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][new_event][status]" <?php if(isset($notifications['new_event']['status']) and $notifications['new_event']['status']) echo 'checked="checked"'; ?> /> <?php _e('Enable new event notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_new_event_container_toggle" class="<?php if((isset($notifications['new_event']) and !$notifications['new_event']['status']) or !isset($notifications['new_event'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <label>
                                        <input type="hidden" name="mec[notifications][new_event][send_to_admin]" value="0" />
                                        <input value="1" type="checkbox" name="mec[notifications][new_event][send_to_admin]" <?php if((!isset($notifications['new_event']['send_to_admin'])) or (isset($notifications['new_event']['send_to_admin']) and $notifications['new_event']['send_to_admin'])) echo 'checked="checked"'; ?> /> <?php _e('Send the email to admin', 'mec'); ?>
                                    </label>
                                </div>
                                <p class="description"><?php _e('Sent after adding a new event from frontend event submission or from website backend.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_new_event_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][new_event][subject]" id="mec_notifications_new_event_subject" value="<?php echo (isset($notifications['new_event']['subject']) ? stripslashes($notifications['new_event']['subject']) : ''); ?>" />
                                </div>
                                
                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_new_event_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['new_event']['receiver_users']) ? $notifications['new_event']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'new_event');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_new_event_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['new_event']['receiver_roles']) ? $notifications['new_event']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'new_event');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_new_event_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][new_event][recipients]" id="mec_notifications_new_event_recipients" value="<?php echo (isset($notifications['new_event']['recipients']) ? $notifications['new_event']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>                                             
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_new_event_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['new_event']) ? stripslashes($notifications['new_event']['content']) : ''), 'mec_notifications_new_event_content', array('textarea_name'=>'mec[notifications][new_event][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
                                <ul>
                                    <li><span>%%event_title%%</span>: <?php _e('Title of event', 'mec'); ?></li>
                                    <li><span>%%event_link%%</span>: <?php _e('Link of event', 'mec'); ?></li>
                                    <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                                    <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
                                    <li><span>%%event_status%%</span>: <?php _e('Status of event', 'mec'); ?></li>
                                    <li><span>%%event_note%%</span>: <?php _e('Event Note', 'mec'); ?></li>
                                    <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                                    <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                                    <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                                    <li><span>%%admin_link%%</span>: <?php _e('Admin events management link.', 'mec'); ?></li>
                                    <?php do_action('mec_extra_field_notifications'); ?>
                                </ul>
                            </div>

                        </div>
                        
                        <!-- MEC Event Published -->
                        <div id="user_event_publishing" class="mec-options-fields  <?php if(isset($this->settings['booking_status']) and $this->settings['booking_status'] == 0) echo 'active'; ?>">

                            <h4 class="mec-form-subtitle"><?php _e('User Event Publishing', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[notifications][user_event_publishing][status]" value="0" />
                                    <input onchange="jQuery('#mec_notification_user_event_publishing_container_toggle').toggle();" value="1" type="checkbox" name="mec[notifications][user_event_publishing][status]" <?php if(isset($notifications['user_event_publishing']['status']) and $notifications['user_event_publishing']['status']) echo 'checked="checked"'; ?> /> <?php _e('Enable user event publishing notification', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_notification_user_event_publishing_container_toggle" class="<?php if((isset($notifications['user_event_publishing']) and !$notifications['user_event_publishing']['status']) or !isset($notifications['user_event_publishing'])) echo 'mec-util-hidden'; ?>">
                                <p class="description"><?php _e('Sent after publishing a new event from frontend event submission or from website backend.', 'mec'); ?></p>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_user_event_publishing_subject"><?php _e('Email Subject', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][user_event_publishing][subject]" id="mec_notifications_user_event_publishing_subject" value="<?php echo (isset($notifications['user_event_publishing']['subject']) ? stripslashes($notifications['user_event_publishing']['subject']) : ''); ?>" />
                                </div>

                                <!-- Start Receiver Users -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_user_event_publishing_receiver_users"><?php _e('Receiver Users', 'mec'); ?></label>
                                    <?php
                                        $users = isset($notifications['user_event_publishing']['receiver_users']) ? $notifications['user_event_publishing']['receiver_users'] : array();
                                        echo $this->main->get_users_dropdown($users, 'user_event_publishing');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Users', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users to send a copy of email to them!', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Users -->

                                <!-- Start Receiver Roles -->
                                <div class="mec-form-row">
                                    <label for="mec_notifications_user_event_publishing_receiver_roles"><?php _e('Receiver Roles', 'mec'); ?></label>
                                    <?php
                                        $roles = isset($notifications['user_event_publishing']['receiver_roles']) ? $notifications['user_event_publishing']['receiver_roles'] : array();
                                        echo $this->main->get_roles_dropdown($roles, 'user_event_publishing');
                                    ?>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Receiver Roles', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Select users a specific role.', 'mec'); ?></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                                <!-- End Receiver Roles -->

                                <div class="mec-form-row">
                                    <label for="mec_notifications_user_event_publishing_recipients"><?php _e('Custom Recipients', 'mec'); ?></label>
                                    <input type="text" name="mec[notifications][user_event_publishing][recipients]" id="mec_notifications_user_event_publishing_recipients" value="<?php echo (isset($notifications['user_event_publishing']['recipients']) ? $notifications['user_event_publishing']['recipients'] : ''); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Custom Recipients', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Insert comma separated emails for multiple recipients.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/notifications/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>    
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>                                             
                                </div>
                                <div class="mec-form-row">
                                    <label for="mec_notifications_user_event_publishing_content"><?php _e('Email Content', 'mec'); ?></label>
                                    <?php wp_editor((isset($notifications['user_event_publishing']) ? stripslashes($notifications['user_event_publishing']['content']) : ''), 'mec_notifications_user_event_publishing_content', array('textarea_name'=>'mec[notifications][user_event_publishing][content]')); ?>
                                </div>
                                <p class="description"><?php _e('You can use the following placeholders', 'mec'); ?></p>
                                <ul>
                                    <li><span>%%event_title%%</span>: <?php _e('Title of event', 'mec'); ?></li>
                                    <li><span>%%event_link%%</span>: <?php _e('Link of event', 'mec'); ?></li>
                                    <li><span>%%event_start_date%%</span>: <?php _e('Event Start Date', 'mec'); ?></li>
                                    <li><span>%%event_end_date%%</span>: <?php _e('Event End Date', 'mec'); ?></li>
                                    <li><span>%%event_status%%</span>: <?php _e('Status of event', 'mec'); ?></li>
                                    <li><span>%%event_note%%</span>: <?php _e('Event Note', 'mec'); ?></li>
                                    <li><span>%%blog_name%%</span>: <?php _e('Your website title', 'mec'); ?></li>
                                    <li><span>%%blog_url%%</span>: <?php _e('Your website URL', 'mec'); ?></li>
                                    <li><span>%%blog_description%%</span>: <?php _e('Your website description', 'mec'); ?></li>
                                    <li><span>%%admin_link%%</span>: <?php _e('Admin events management link.', 'mec'); ?></li>
                                    <?php do_action('mec_extra_field_notifications'); ?>
                                </ul>
                            </div>
                        
                        </div>

                        <div id="notifications_per_event" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Notifications Per Event', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][notif_per_event]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][notif_per_event]" <?php if(isset($settings['notif_per_event']) and $settings['notif_per_event']) echo 'checked="checked"'; ?> /> <?php _e('Edit Notifications Per Event', 'mec'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- </ul> -->

                        <div class="mec-options-fields">
                            <?php wp_nonce_field('mec_options_form'); ?>
                            <button style="display: none;" id="mec_notifications_form_button" class="button button-primary mec-button-primary" type="submit"><?php _e('Save Changes', 'mec'); ?></button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>



    <div id="wns-be-footer">
        <a href="" id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'mec'); ?></a>
    </div>

</div>

<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery(".dpr-save-btn").on('click', function(event)
    {
        event.preventDefault();
        jQuery("#mec_notifications_form_button").trigger('click');
    });
});

jQuery("#mec_notifications_form").on('submit', function(event)
{
    event.preventDefault();
    
    jQuery("#mec_notifications_booking_notification_content-html").click();
    jQuery("#mec_notifications_booking_notification_content-tmce").click();
    
    jQuery("#mec_notifications_email_verification_content-html").click();
    jQuery("#mec_notifications_email_verification_content-tmce").click();
    
    jQuery("#mec_notifications_booking_confirmation_content-html").click();
    jQuery("#mec_notifications_booking_confirmation_content-tmce").click();
    
    jQuery("#mec_notifications_admin_notification_content-html").click();
    jQuery("#mec_notifications_admin_notification_content-tmce").click();

    jQuery("#mec_notifications_booking_reminder_content-html").click();
    jQuery("#mec_notifications_booking_reminder_content-tmce").click();

    jQuery("#mec_notifications_new_event_content-html").click();
    jQuery("#mec_notifications_new_event_content-tmce").click();

    jQuery("#mec_notifications_user_event_publishing_content-html").click();
    jQuery("#mec_notifications_user_event_publishing_content-tmce").click();

    <?php do_action( 'mec_notification_menu_js' ); ?>
});
</script>

<script type="text/javascript">
jQuery(document).ready(function()
{   
    jQuery('.WnTabLinks').each(function()
    {
        var ContentId = jQuery(this).attr('data-id');
         jQuery(this).click(function()
         {
            jQuery('.pr-be-group-menu-li').removeClass('active');
            jQuery(this).parent().addClass('active');
            jQuery(".mec-options-fields").hide();
            jQuery(".mec-options-fields").removeClass('active');
            jQuery("#"+ContentId+"").show();
            jQuery("#"+ContentId+"").addClass('active');
            jQuery('html, body').animate({
                scrollTop: jQuery("#"+ContentId+"").offset().top - 140
            }, 300);
        });
        var hash = window.location.hash.replace('#', '');
        jQuery('[data-id="'+hash+'"]').trigger('click');
    });
   
    jQuery(".wns-be-sidebar .pr-be-group-menu-li").on('click', function(event)
    {
        jQuery(".wns-be-sidebar .pr-be-group-menu-li").removeClass('active');
        jQuery(this).addClass('active');
    });
});

jQuery("#mec_notifications_form").on('submit', function(event)
{
    event.preventDefault();
    
    // Add loading Class to the button
    jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saved', 'mec')); ?>");
    jQuery('<div class="wns-saved-settings"><?php echo esc_js(esc_attr__('Settings Saved!', 'mec')); ?></div>').insertBefore('#wns-be-content');

    if(jQuery(".mec-purchase-verify").text() != '<?php echo esc_js(esc_attr__('Verified', 'mec')); ?>')
    {
        jQuery(".mec-purchase-verify").text("<?php echo esc_js(esc_attr__('Checking ...', 'mec')); ?>");
    }

    var settings = jQuery("#mec_notifications_form").serialize();
    jQuery.ajax(
    {
        type: "POST",
        url: ajaxurl,
        data: "action=mec_save_settings&"+settings,
        beforeSend: function () {
            jQuery('.wns-be-main').append('<div class="mec-loarder-wrap mec-settings-loader"><div class="mec-loarder"><div></div><div></div><div></div></div></div>');
        },
        success: function(data)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'mec')); ?>");
                jQuery('.wns-saved-settings').remove();
                jQuery('.mec-loarder-wrap').remove();
                if(jQuery(".mec-purchase-verify").text() != '<?php echo esc_js(esc_attr__('Verified', 'mec')); ?>')
                {
                    jQuery(".mec-purchase-verify").text("<?php echo esc_js(esc_attr__('Please Refresh Page', 'mec')); ?>");
                }
            }, 1000);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            // Remove the loading Class to the button
            setTimeout(function()
            {
                jQuery(".dpr-save-btn").removeClass('loading').text("<?php echo esc_js(esc_attr__('Save Changes', 'mec')); ?>");
                jQuery('.wns-saved-settings').remove();
                jQuery('.mec-loarder-wrap').remove();
            }, 1000);
        }
    });
});

</script>