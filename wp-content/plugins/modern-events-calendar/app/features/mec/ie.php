<?php
/** no direct access **/
defined('MECEXEC') or die();
?>

<div class="wns-be-container wns-be-container-sticky">

    <div id="wns-be-infobar"></div>

    <div class="wns-be-sidebar">
        <?php $this->main->get_sidebar_menu('ie'); ?>
    </div>

    <div class="wns-be-main">

        <div id="wns-be-notification"></div>

        <div id="wns-be-content">
            <div class="wns-be-group-tab">
                <h2><?php _e('Import', 'mec'); ?></h2>
                <p><?php _e('Insert your backup files below and press import to restore your site\'s options to the last backup.', 'mec'); ?></p>
                <p style="color:#d80000"><?php _e('WARNING! Restoring backup will overwrite all of your current option values. Caution Indeed.', 'mec'); ?></p>
                <div class="mec-container">
                    <div class="mec-import-settings-wrap">
                        <textarea class="mec-import-settings-content" placeholder="<?php esc_html_e('Please paste your options here', 'mec'); ?>"></textarea>
                    </div>
                    <a class="mec-import-settings" href="#"><?php _e("Import Settings", 'mec'); ?></a>
                    <div class="mec-import-options-notification"></div>
                </div>

                <h2><?php _e('Export', 'mec'); ?></h2>
                <div class="mec-container">
                    <?php
                        $nonce = wp_create_nonce("mec_settings_download");
                        $export_link = admin_url('admin-ajax.php?action=download_settings&nonce='.$nonce);
                    ?>
                    <a class="mec-export-settings" href="<?php echo $export_link; ?>"><?php _e("Download Settings", 'mec'); ?></a>
                </div>
            </div>
        </div>

    </div>

</div>