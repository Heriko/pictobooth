<?php
/** no direct access **/
defined('MECEXEC') or die();

/** @var MEC_feature_mec $this */

$settings = $this->main->get_settings();

// WordPress Pages
$pages = get_pages();

// Event Fields
$event_fields = $this->main->get_event_fields();
?>
<div class="wns-be-container wns-be-container-sticky">
    <div id="wns-be-infobar">
        <div class="mec-search-settings-wrap">
            <i class="mec-sl-magnifier"></i>
            <input id="mec-search-settings" type="text" placeholder="<?php esc_html_e('Search...' ,'mec'); ?>">
        </div>
        <a id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'mec'); ?></a>
    </div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('single_event'); ?>
    </div>

    <div class="wns-be-main">
        <div id="wns-be-notification"></div>
        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <div class="mec-container">

                    <form id="mec_single_form">

                        <div id="event_options" class="mec-options-fields active">

                            <h4 class="mec-form-subtitle"><?php _e('Single Event Page', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_date_format1"><?php _e('Single Event Date Format', 'mec'); ?></label>
                                <div class="mec-col-4">
                                    <input type="text" id="mec_settings_single_event_date_format1" name="mec[settings][single_date_format1]" value="<?php echo ((isset($settings['single_date_format1']) and trim($settings['single_date_format1']) != '') ? $settings['single_date_format1'] : 'M d Y'); ?>" />
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Single Event Date Format', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Default is M d Y", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_date_method"><?php _e('Date Method', 'mec'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_date_method" name="mec[settings][single_date_method]">
                                        <option value="next" <?php echo (isset($settings['single_date_method']) and $settings['single_date_method'] == 'next') ? 'selected="selected"' : ''; ?>><?php _e('Next occurrence date', 'mec'); ?></option>
                                        <option value="referred" <?php echo (isset($settings['single_date_method']) and $settings['single_date_method'] == 'referred') ? 'selected="selected"' : ''; ?>><?php _e('Referred date', 'mec'); ?></option>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Date Method', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e('Referred date" shows the event date based on referred date in event list.', 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_single_style"><?php _e('Single Event Style', 'mec'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_single_style" name="mec[settings][single_single_style]">
                                        <option value="default" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'default') ? 'selected="selected"' : ''; ?>><?php _e('Default Style', 'mec'); ?></option>
                                        <option value="modern" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'modern') ? 'selected="selected"' : ''; ?>><?php _e('Modern Style', 'mec'); ?></option>
                                        <?php do_action('mec_single_style', $settings); ?>
                                        <?php if ( is_plugin_active( 'mec-single-builder/mec-single-builder.php' ) ) : ?>
                                        <option value="builder" <?php echo (isset($settings['single_single_style']) and $settings['single_single_style'] == 'builder') ? 'selected="selected"' : ''; ?>><?php _e('Elementor Single Builder', 'mec'); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Single Event Style', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Choose your single event style.", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <?php do_action('mec_single_style_setting_after', $this) ?>
                            <?php if($this->main->getPRO() and isset($this->settings['booking_status']) and $this->settings['booking_status']): ?>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_single_event_booking_style"><?php _e('Booking Style', 'mec'); ?></label>
                                <div class="mec-col-4">
                                    <select id="mec_settings_single_event_booking_style" name="mec[settings][single_booking_style]">
                                        <option value="default" <?php echo (isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'default') ? 'selected="selected"' : ''; ?>><?php _e('Default', 'mec'); ?></option>
                                        <option value="modal" <?php echo (isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal') ? 'selected="selected"' : ''; ?>><?php _e('Modal', 'mec'); ?></option>
                                    </select>
                                    <span class="mec-tooltip">
                                        <div class="box top">
                                            <h5 class="title"><?php _e('Booking Style', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Choose your Booking style. Note: When you set this feature to Modal, you cannot see the booking box if you set popup module view on shortcodes", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </div>
                            </div>
                            <?php endif;?>
                            <div class="mec-form-row">
                                <label class="mec-col-3" for="mec_settings_tz_per_event"><?php _e('Timezone Per Event', 'mec'); ?></label>
                                <label id="mec_settings_tz_per_event" >
                                    <input type="hidden" name="mec[settings][tz_per_event]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][tz_per_event]" <?php if(isset($settings['tz_per_event']) and $settings['tz_per_event']) echo 'checked="checked"'; ?> /> <?php _e('Enable', 'mec'); ?>
                                </label>
                            </div>
                            <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_settings_gutenberg"><?php _e('Disable Block Editor (Gutenberg)', 'mec'); ?></label>
                                <label id="mec_settings_gutenberg" >
                                    <input type="hidden" name="mec[settings][gutenberg]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][gutenberg]" <?php if(!isset($settings['gutenberg']) or (isset($settings['gutenberg']) and $settings['gutenberg'])) echo 'checked="checked"'; ?> /> <?php _e('Disable Block Editor', 'mec'); ?>
                                </label>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Block Editor', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e("If you want to use the new WordPress block editor you should keep this checkbox unchecked.", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>

                            <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_settings_breadcrumbs"><?php _e('Breadcrumbs', 'mec'); ?></label>
                                <label id="mec_settings_breadcrumbs" >
                                    <input type="hidden" name="mec[settings][breadcrumbs]" value="0" />
                                    <input type="checkbox" name="mec[settings][breadcrumbs]" id="mec_settings_breadcrumbs" <?php echo ((isset($settings['breadcrumbs']) and $settings['breadcrumbs']) ? 'checked="checked"' : ''); ?> value="1" /><?php _e('Enable Breadcrumbs.', 'mec'); ?>
                                </label>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Breadcrumbs', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e("Check this option, for showing the breadcrumbs on single event page", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>

                            <div class="mec-form-row">
                            <label class="mec-col-3" for="mec_settings_organizer_description"><?php _e('Organizer Description', 'mec'); ?></label>
                                <label id="mec_settings_organizer_description" >
                                    <input type="hidden" name="mec[settings][organizer_description]" value="0" />
                                    <input type="checkbox" name="mec[settings][organizer_description]" id="mec_settings_organizer_description" <?php echo ((isset($settings['organizer_description']) and $settings['organizer_description']) ? 'checked="checked"' : ''); ?> value="1" /><?php _e('Enable Description For Organizer.', 'mec'); ?>
                                </label>
                                <span class="mec-tooltip">
                                    <div class="box top">
                                        <h5 class="title"><?php _e('Organizer Description', 'mec'); ?></h5>
                                        <div class="content"><p><?php esc_attr_e("If you want to turn on description for other organizer plase go to 'Additional Organizers - After enabling and saving the settings, reloading the settings page.' tab", 'mec'); ?></p></div>
                                    </div>
                                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                                </span>
                            </div>

                        </div>

                        <div id="event_form_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Custom Fields', 'mec'); ?></h4>
                            <div class="mec-container">
                                <div class="mec-form-row" id="mec_event_form_container">
                                    <?php /** Don't remove this hidden field **/ ?>
                                    <input type="hidden" name="mec[event_fields]" value="" />

                                    <ul id="mec_event_form_fields">
                                        <?php
                                        $i = 0;
                                        foreach($event_fields as $key => $event_field)
                                        {
                                            if(!is_numeric($key)) continue;
                                            $i = max($i, $key);

                                            if($event_field['type'] == 'text') echo $this->main->field_text($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'email') echo $this->main->field_email($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'url') echo $this->main->field_url($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'date') echo $this->main->field_date($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'tel') echo $this->main->field_tel($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'textarea') echo $this->main->field_textarea($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'p') echo $this->main->field_p($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'checkbox') echo $this->main->field_checkbox($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'radio') echo $this->main->field_radio($key, $event_field, 'event');
                                            elseif($event_field['type'] == 'select') echo $this->main->field_select($key, $event_field, 'event');
                                        }
                                        ?>
                                    </ul>
                                    <div id="mec_event_form_field_types">
                                        <button type="button" class="button" data-type="text"><?php _e('Text', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="email"><?php _e('Email', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="url"><?php _e('URL', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="date"><?php _e('Date', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="tel"><?php _e('Tel', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="textarea"><?php _e('Textarea', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="p"><?php _e('Paragraph', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="checkbox"><?php _e('Checkboxes', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="radio"><?php _e('Radio Buttons', 'mec'); ?></button>
                                        <button type="button" class="button" data-type="select"><?php _e('Dropdown', 'mec'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="mec_new_event_field_key" value="<?php echo $i + 1; ?>" />
                            <div class="mec-util-hidden">
                                <div id="mec_event_field_text">
                                    <?php echo $this->main->field_text(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_email">
                                    <?php echo $this->main->field_email(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_url">
                                    <?php echo $this->main->field_url(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_tel">
                                    <?php echo $this->main->field_tel(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_date">
                                    <?php echo $this->main->field_date(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_textarea">
                                    <?php echo $this->main->field_textarea(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_checkbox">
                                    <?php echo $this->main->field_checkbox(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_radio">
                                    <?php echo $this->main->field_radio(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_select">
                                    <?php echo $this->main->field_select(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_p">
                                    <?php echo $this->main->field_p(':i:', array(), 'event'); ?>
                                </div>
                                <div id="mec_event_field_option">
                                    <?php echo $this->main->field_option(':fi:', ':i:', array(), 'event'); ?>
                                </div>
                            </div>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][display_event_fields]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][display_event_fields]" <?php if(!isset($settings['display_event_fields']) or (isset($settings['display_event_fields']) and $settings['display_event_fields'])) echo 'checked="checked"'; ?> /> <?php _e('Display Event Fields in Single Event Pages', 'mec'); ?>
                                </label>
                            </div>
                        </div>

                        <div id="countdown_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Countdown Options', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][countdown_status]" value="0" />
                                    <input onchange="jQuery('#mec_count_down_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][countdown_status]" <?php if(isset($settings['countdown_status']) and $settings['countdown_status']) echo 'checked="checked"'; ?> /> <?php _e('Show countdown module on event page', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_count_down_container_toggle" class="<?php if((isset($settings['countdown_status']) and !$settings['countdown_status']) or !isset($settings['countdown_status'])) echo 'mec-util-hidden'; ?>">
                                <div class="mec-form-row">
                                    <label class="mec-col-3" for="mec_settings_countdown_list"><?php _e('Countdown Style', 'mec'); ?></label>
                                    <div class="mec-col-4">
                                        <select id="mec_settings_countdown_list" name="mec[settings][countdown_list]">
                                            <option value="default" <?php echo ((isset($settings['countdown_list']) and $settings['countdown_list'] == "default") ? 'selected="selected"' : ''); ?> ><?php _e('Plain Style', 'mec'); ?></option>
                                            <option value="flip" <?php echo ((isset($settings['countdown_list']) and $settings['countdown_list'] == "flip") ? 'selected="selected"' : ''); ?> ><?php _e('Flip Style', 'mec'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="exceptional_option" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Exceptional days (Exclude Dates)', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][exceptional_days]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][exceptional_days]" <?php if(isset($settings['exceptional_days']) and $settings['exceptional_days']) echo 'checked="checked"'; ?> /> <?php _e('Show exceptional days option on Add/Edit events page', 'mec'); ?>
                                    <span class="mec-tooltip">
                                        <div class="box">
                                            <h5 class="title"><?php _e('Exceptional days (Exclude Dates)', 'mec'); ?></h5>
                                            <div class="content"><p><?php esc_attr_e("Using this option you can exclude certain days from event occurrence dates.", 'mec'); ?><a href="https://webnus.net/dox/modern-events-calendar/exceptional-days/" target="_blank"><?php _e('Read More', 'mec'); ?></a></p></div>
                                        </div>
                                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div id="additional_organizers" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Additional Organizers', 'mec'); ?></h4>

                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][additional_organizers]" value="0" />
                                    <input onchange="jQuery('#mec_settings_additional_organizers_description').toggle();" value="1" type="checkbox" name="mec[settings][additional_organizers]" <?php if(!isset($settings['additional_organizers']) or (isset($settings['additional_organizers']) and $settings['additional_organizers'])) echo 'checked="checked"'; ?> /> <?php _e('Show additional organizers option on Add/Edit events page and single event page.', 'mec'); ?>
                                </label>
                            </div>

                            <div id="mec_settings_additional_organizers_description" class="<?php if((isset($settings['additional_organizers']) and !$settings['additional_organizers']) or !isset($settings['additional_organizers'])) echo 'mec-util-hidden'; ?>">

                                <div class="mec-form-row">
                                    <label id="mec_settings_additional_organizers_description" >
                                        <input type="hidden" name="mec[settings][addintional_organizers_description]" value="0" />
                                        <input type="checkbox" name="mec[settings][addintional_organizers_description]" id="mec_settings_additional_organizers_description" <?php echo ((isset($settings['addintional_organizers_description']) and $settings['addintional_organizers_description']) ? 'checked="checked"' : ''); ?> value="1" /><?php _e('Enable Description For Other Organizers.', 'mec'); ?>
                                    </label>
                                </div>

                            </div>

                        </div>
                        <div id="additional_locations" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Additional locations', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][additional_locations]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][additional_locations]" <?php if(!isset($settings['additional_locations']) or (isset($settings['additional_locations']) and $settings['additional_locations'])) echo 'checked="checked"'; ?> /> <?php _e('Show additional locations option on Add/Edit events page and single event page.', 'mec'); ?>
                                </label>
                            </div>
                        </div>

                        <div id="related_events" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Related Events', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][related_events]" value="0" />
                                    <input onchange="jQuery('#mec_related_events_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][related_events]" <?php if(isset($settings['related_events']) and $settings['related_events']) echo 'checked="checked"'; ?> /> <?php _e('Display related events based on taxonomy in single event page.', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_related_events_container_toggle" class="<?php if((isset($settings['related_events']) and !$settings['related_events']) or !isset($settings['related_events'])) echo 'mec-util-hidden'; ?>">

                                <div class="mec-form-row" style="margin-top:20px;">
                                    <label style="margin-right:20px;" for="mec_settings_countdown_list"><?php _e('Select Taxonomies:', 'mec'); ?></label>
                                    <label style="margin-right:20px;margin-bottom: 20px">
                                        <input type="hidden" name="mec[settings][related_events_basedon_category]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_category]" <?php if(isset($settings['related_events_basedon_category']) and $settings['related_events_basedon_category']) echo 'checked="checked"'; ?> /> <?php _e('Category', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_organizer]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_organizer]" <?php if(isset($settings['related_events_basedon_organizer']) and $settings['related_events_basedon_organizer']) echo 'checked="checked"'; ?> /> <?php _e('Organizer', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_location]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_location]" <?php if(isset($settings['related_events_basedon_location']) and $settings['related_events_basedon_location']) echo 'checked="checked"'; ?> /> <?php _e('Location', 'mec'); ?>
                                    </label>
                                    <?php if(isset($settings['speakers_status']) and $settings['speakers_status']) : ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_speaker]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_speaker]" <?php if(isset($settings['related_events_basedon_speaker']) and $settings['related_events_basedon_speaker']) echo 'checked="checked"'; ?> /> <?php _e('Speaker', 'mec'); ?>
                                    </label>
                                    <?php endif; ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_label]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_label]" <?php if(isset($settings['related_events_basedon_label']) and $settings['related_events_basedon_label']) echo 'checked="checked"'; ?> /> <?php _e('Label', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][related_events_basedon_tag]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][related_events_basedon_tag]" <?php if(isset($settings['related_events_basedon_tag']) and $settings['related_events_basedon_tag']) echo 'checked="checked"'; ?> /> <?php _e('Tag', 'mec'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="next_previous_events" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Next / Previous Events', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][next_previous_events]" value="0" />
                                    <input onchange="jQuery('#mec_next_previous_events_container_toggle').toggle();" value="1" type="checkbox" name="mec[settings][next_previous_events]" <?php if(isset($settings['next_previous_events']) and $settings['next_previous_events']) echo 'checked="checked"'; ?> /> <?php _e('Display next / previous events based on taxonomy in single event page.', 'mec'); ?>
                                </label>
                            </div>
                            <div id="mec_next_previous_events_container_toggle" class="<?php if((isset($settings['next_previous_events']) and !$settings['next_previous_events']) or !isset($settings['next_previous_events'])) echo 'mec-util-hidden'; ?>">

                                <div class="mec-form-row" style="margin-top:20px;">
                                    <label style="margin-right:20px;" for="mec_settings_countdown_list"><?php _e('Select Taxonomies:', 'mec'); ?></label>
                                    <label style="margin-right:20px; margin-bottom: 20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_category]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_category]" <?php if(isset($settings['next_previous_events_category']) and $settings['next_previous_events_category']) echo 'checked="checked"'; ?> /> <?php _e('Category', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_organizer]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_organizer]" <?php if(isset($settings['next_previous_events_organizer']) and $settings['next_previous_events_organizer']) echo 'checked="checked"'; ?> /> <?php _e('Organizer', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_location]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_location]" <?php if(isset($settings['next_previous_events_location']) and $settings['next_previous_events_location']) echo 'checked="checked"'; ?> /> <?php _e('Location', 'mec'); ?>
                                    </label>
                                    <?php if(isset($settings['speakers_status']) and $settings['speakers_status']) : ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_speaker]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_speaker]" <?php if(isset($settings['next_previous_events_speaker']) and $settings['next_previous_events_speaker']) echo 'checked="checked"'; ?> /> <?php _e('Speaker', 'mec'); ?>
                                    </label>
                                    <?php endif; ?>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_label]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_label]" <?php if(isset($settings['next_previous_events_label']) and $settings['next_previous_events_label']) echo 'checked="checked"'; ?> /> <?php _e('Label', 'mec'); ?>
                                    </label>
                                    <label style="margin-right:20px;">
                                        <input type="hidden" name="mec[settings][next_previous_events_tag]" value="0" />
                                        <input value="1" type="checkbox" name="mec[settings][next_previous_events_tag]" <?php if(isset($settings['next_previous_events_tag']) and $settings['next_previous_events_tag']) echo 'checked="checked"'; ?> /> <?php _e('Tag', 'mec'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="per_occurrences" class="mec-options-fields">
                            <h4 class="mec-form-subtitle"><?php _e('Edit Per Occurrences', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="hidden" name="mec[settings][per_occurrences_status]" value="0" />
                                    <input value="1" type="checkbox" name="mec[settings][per_occurrences_status]" <?php if(isset($settings['per_occurrences_status']) and $settings['per_occurrences_status']) echo 'checked="checked"'; ?> /> <?php _e('Ability to edit some event information per occurrence', 'mec'); ?>
                                </label>
                            </div>
                        </div>

                        <div class="mec-options-fields">
                            <?php wp_nonce_field('mec_options_form'); ?>
                            <button style="display: none;" id="mec_single_form_button" class="button button-primary mec-button-primary" type="submit"><?php _e('Save Changes', 'mec'); ?></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div id="wns-be-footer">
        <a id="" class="dpr-btn dpr-save-btn"><?php _e('Save Changes', 'mec'); ?></a>
    </div>

</div>

<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery(".dpr-save-btn").on('click', function(event)
    {
        event.preventDefault();
        jQuery("#mec_single_form_button").trigger('click');
    });
});

jQuery("#mec_single_form").on('submit', function(event)
{
    event.preventDefault();

    // Add loading Class to the button
    jQuery(".dpr-save-btn").addClass('loading').text("<?php echo esc_js(esc_attr__('Saved', 'mec')); ?>");
    jQuery('<div class="wns-saved-settings"><?php echo esc_js(esc_attr__('Settings Saved!', 'mec')); ?></div>').insertBefore('#wns-be-content');

    if(jQuery(".mec-purchase-verify").text() != '<?php echo esc_js(esc_attr__('Verified', 'mec')); ?>')
    {
        jQuery(".mec-purchase-verify").text("<?php echo esc_js(esc_attr__('Checking ...', 'mec')); ?>");
    }

    var settings = jQuery("#mec_single_form").serialize();
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