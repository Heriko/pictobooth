<?php
/** no direct access **/
defined('MECEXEC') or die();

$ix_options = $this->main->get_ix_options();
?>
<div class="wrap" id="mec-wrap">
    <h1><?php _e('MEC Import / Export', 'mec'); ?></h1>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo $this->main->remove_qs_var('tab'); ?>" class="nav-tab"><?php echo __('Google Cal. Import', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-g-calendar-export'); ?>" class="nav-tab"><?php echo __('Google Cal. Export', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-f-calendar-import'); ?>" class="nav-tab"><?php echo __('Facebook Cal. Import', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-meetup-import'); ?>" class="nav-tab nav-tab-active"><?php echo __('Meetup Import', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-sync'); ?>" class="nav-tab"><?php echo __('Synchronization', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-export'); ?>" class="nav-tab"><?php echo __('Export', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-import'); ?>" class="nav-tab"><?php echo __('Import', 'mec'); ?></a>
        <a href="<?php echo $this->main->add_qs_var('tab', 'MEC-thirdparty'); ?>" class="nav-tab"><?php echo __('Third Party Plugins', 'mec'); ?></a>
    </h2>
    <div class="mec-container">
        <div class="import-content w-clearfix extra">
            <div class="mec-meetup-import">
                <form id="mec_meetup_import_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
                    <h3><?php _e('Import from Meetup', 'mec'); ?></h3>
                    <p class="description"><?php _e('This will import all your meetup events into MEC.', 'mec'); ?></p>
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_ix_meetup_api_key"><?php _e('Meetup API Key', 'mec'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" id="mec_ix_meetup_api_key" name="ix[meetup_api_key]" value="<?php echo (isset($ix_options['meetup_api_key']) ? $ix_options['meetup_api_key'] : ''); ?>" />
                        </div>
                    </div>
                    <div class="mec-form-row">
                        <label class="mec-col-3" for="mec_ix_meetup_group_url"><?php _e('Group URL', 'mec'); ?></label>
                        <div class="mec-col-4">
                            <input type="text" id="mec_ix_meetup_group_url" name="ix[meetup_group_url]" value="<?php echo (isset($ix_options['meetup_group_url']) ? $ix_options['meetup_group_url'] : ''); ?>" />
                            <p><?php echo sprintf(__('just put the slug of your group like %s in %s', 'mec'), '<strong>your-group-slug</strong>', 'https://www.meetup.com/your-group-slug/'); ?></p>
                        </div>
                    </div>
                    <div class="mec-options-fields">
                        <input type="hidden" name="mec-ix-action" value="meetup-import-start" />
                        <button id="mec_ix_meetup_form_button" class="button button-primary mec-button-primary" type="submit"><?php _e('Start', 'mec'); ?></button>
                    </div>
                </form>
                <?php if($this->action == 'meetup-import-start'): ?>
                <div class="mec-ix-meetup-started">
                    <?php if($this->response['success'] == 0): ?>
                    <div class="mec-error"><?php echo $this->response['error']; ?></div>
                    <?php else: ?>
                    <form id="mec_meetup_do_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
                        <div class="mec-xi-meetup-events mec-options-fields">
                            <h4><?php _e('Meetup Events', 'mec'); ?></h4>
                            <div class="mec-success"><?php echo sprintf(__('We found %s events for %s group. Please select your desired events to import.', 'mec'), '<strong>'.$this->response['data']['count'].'</strong>', '<strong>'.$this->response['data']['title'].'</strong>'); ?></div>
                            <ul class="mec-select-deselect-actions" data-for="#mec_import_meetup_events">
                                <li data-action="select-all"><?php _e('Select All', 'mec'); ?></li>
                                <li data-action="deselect-all"><?php _e('Deselect All', 'mec'); ?></li>
                                <li data-action="toggle"><?php _e('Toggle', 'mec'); ?></li>
                            </ul>
                            <ul id="mec_import_meetup_events">
                                <?php foreach($this->response['data']['events'] as $event): ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="m-events[]" value="<?php echo $event['id']; ?>" checked="checked" />
                                        <span><?php echo sprintf(__('Event Title: %s Event Date: %s - %s', 'mec'), '<strong>'.$event['title'].'</strong>', '<strong>'.$event['start'].'</strong>', '<strong>'.$event['end'].'</strong>'); ?></span>
                                    </label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="mec-options-fields">
                            <h4><?php _e('Import Options', 'mec'); ?></h4>
                            <div class="mec-form-row">
                                <label>
                                    <input type="checkbox" name="ix[import_organizers]" value="1" checked="checked" />
                                    <?php _e('Import Organizers', 'mec'); ?>
                                </label>
                            </div>
                            <div class="mec-form-row">
                                <label>
                                    <input type="checkbox" name="ix[import_locations]" value="1" checked="checked" />
                                    <?php _e('Import Locations', 'mec'); ?>
                                </label>
                            </div>
                            <input type="hidden" name="mec-ix-action" value="meetup-import-do" />
                            <input type="hidden" name="ix[meetup_api_key]" value="<?php echo (isset($this->ix['meetup_api_key']) ? $this->ix['meetup_api_key'] : ''); ?>" />
                            <input type="hidden" name="ix[meetup_group_url]" value="<?php echo (isset($this->ix['meetup_group_url']) ? $this->ix['meetup_group_url'] : ''); ?>" />
                            <button id="mec_ix_meetup_import_do_form_button" class="button button-primary mec-button-primary" type="submit"><?php _e('Import', 'mec'); ?></button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
                <?php elseif($this->action == 'meetup-import-do'): ?>
                <div class="mec-ix-meetup-import-do">
                    <?php if($this->response['success'] == 0): ?>
                    <div class="mec-error"><?php echo $this->response['error']; ?></div>
                    <?php else: ?>
                    <div class="mec-success"><?php echo sprintf(__('%s events successfully imported to your website from meetup.', 'mec'), '<strong>'.count($this->response['data']).'</strong>'); ?></div>
                    <div class="info-msg"><strong><?php _e('Attention', 'mec'); ?>:</strong> <?php _e("Although we tried our best to make the events completely compatible with MEC but some modification might be needed. We suggest you to edit the imported listings one by one on MEC edit event page and make sure thay're correct.", 'mec'); ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>