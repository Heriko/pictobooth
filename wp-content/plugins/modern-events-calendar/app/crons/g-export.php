<?php
/**
 *  WordPress initializing
 */
function mec_find_wordpress_base_path()
{
    $dir = dirname(__FILE__);

    do
    {
        if(file_exists($dir.'/wp-config.php')) return $dir;
    }
    while($dir = realpath($dir.'/..'));

    return NULL;
}

define('BASE_PATH', mec_find_wordpress_base_path().'/');
if(!defined('WP_USE_THEMES')) define('WP_USE_THEMES', false);

global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require BASE_PATH.'wp-load.php';

/** @var $main MEC_main **/

$main = MEC::getInstance('app.libraries.main');
$db = $main->getDB();

// Get MEC IX options
$ix = $main->get_ix_options();

// Auto sync is disabled
if(!isset($internal_cron_system) and (!isset($ix['sync_g_export']) or (isset($ix['sync_g_export']) and !$ix['sync_g_export']))) exit(__('Auto Google Calendar export is disabled!', 'mec'));

$client_id = isset($ix['google_export_client_id']) ? $ix['google_export_client_id'] : NULL;
$client_secret = isset($ix['google_export_client_secret']) ? $ix['google_export_client_secret'] : NULL;
$token = isset($ix['google_export_token']) ? $ix['google_export_token'] : NULL;
$refresh_token = isset($ix['google_export_refresh_token']) ? $ix['google_export_refresh_token'] : NULL;
$calendar_id = isset($ix['google_export_calendar_id']) ? $ix['google_export_calendar_id'] : NULL;

if(!isset($internal_cron_system) and (!trim($client_id) or !trim($client_secret) or !trim($calendar_id))) exit(__('Client App, Client Secret, and Calendar ID are all required!', 'mec'));

$client = new Google_Client();
$client->setApplicationName('Modern Events Calendar');
$client->setAccessType('offline');
$client->setScopes(array('https://www.googleapis.com/auth/calendar'));
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($main->add_qs_vars(array('mec-ix-action'=>'google-calendar-export-get-token'), $main->URL('backend').'admin.php?page=MEC-ix&tab=MEC-g-calendar-export'));
$client->setAccessToken($token);
$client->refreshToken($refresh_token);

$service = new Google_Service_Calendar($client);

// MEC Render Library
$render = $main->getRender();

$g_events_not_inserted = array();
$g_events_inserted = array();
$g_events_updated = array();
$g_events_upserted = array();
$g_events_deleted = array();

$mec_events = $main->get_events('-1');

// Update & Insert Events
foreach($mec_events as $mec_event)
{
    $mec_event_id = $mec_event->ID;
    $data = $render->data($mec_event_id);

    $dates = $render->dates($mec_event_id, $data);
    $date = isset($dates[0]) ? $dates[0] : array();

    // Timezone Options
    $timezone = $main->get_timezone($mec_event_id);
    $gmt_offset = $main->get_gmt_offset($mec_event_id);

    $location = isset($data->locations[$data->meta['mec_location_id']]) ? $data->locations[$data->meta['mec_location_id']] : array();
    $organizer = isset($data->organizers[$data->meta['mec_organizer_id']]) ? $data->organizers[$data->meta['mec_organizer_id']] : array();

    $recurrence = $main->get_ical_rrules($data);

    $start = array(
        'dateTime'=>date('Y-m-d\TH:i:s', $date['start']['timestamp']).$gmt_offset,
        'timeZone'=>$timezone,
    );

    $end = array(
        'dateTime'=>date('Y-m-d\TH:i:s', $date['end']['timestamp']).$gmt_offset,
        'timeZone'=>$timezone,
    );

    $allday = isset($data->meta['mec_allday']) ? $data->meta['mec_allday'] : 0;
    if($allday)
    {
        $start['dateTime'] = date('Y-m-d\T00:00:00', $date['start']['timestamp']).$gmt_offset;
        $end['dateTime'] = date('Y-m-d\T00:00:00', strtotime('+1 Day', strtotime($end['dateTime']))).$gmt_offset;
    }

    // Event Data
    $event_data = array
    (
        'summary'=>$data->title,
        'location'=>(isset($location['address']) ? $location['address'] : (isset($location['name']) ? $location['name'] : '')),
        'description'=>$data->content,
        'start'=>$start,
        'end'=>$end,
        'recurrence'=>$recurrence,
        'attendees'=>array(),
        'reminders'=>array(),
    );

    $event = new Google_Service_Calendar_Event($event_data);
    $iCalUID = 'mec-ical-'.$data->ID;

    $mec_iCalUID = get_post_meta($data->ID, 'mec_gcal_ical_uid', true);
    $mec_calendar_id = get_post_meta($data->ID, 'mec_gcal_calendar_id', true);

    /**
     * Event is imported from same google calendar
     * and now it's exporting to its calendar again
     * so we're trying to update existing one by setting event iCal ID
     */
    if($mec_calendar_id == $calendar_id and trim($mec_iCalUID)) $iCalUID = $mec_iCalUID;

    $event->setICalUID($iCalUID);

    // Set the organizer if exists
    if(isset($organizer['name']))
    {
        $g_organizer = new Google_Service_Calendar_EventOrganizer();
        $g_organizer->setDisplayName($organizer['name']);
        $g_organizer->setEmail($organizer['email']);

        $event->setOrganizer($g_organizer);
    }

    try
    {
        $g_event = $service->events->insert($calendar_id, $event);

        // Set Google Calendar ID to MEC databse for updating it in the future instead of adding it twice
        update_post_meta($data->ID, 'mec_gcal_ical_uid', $g_event->getICalUID());
        update_post_meta($data->ID, 'mec_gcal_calendar_id', $calendar_id);
        update_post_meta($data->ID, 'mec_gcal_id', $g_event->getId());

        $g_events_inserted[] = array('title'=>$data->title, 'message'=>$g_event->htmlLink);
        $g_events_upserted[] = $g_event->getId();
    }
    catch(Exception $ex)
    {
        // Event already existed
        if($ex->getCode() == 409)
        {
            try
            {
                $g_event_id = get_post_meta($data->ID, 'mec_gcal_id', true);
                $g_event = $service->events->get($calendar_id, $g_event_id);

                // Imported From Google so Don't Export it from MEC
                if(get_post_meta($mec_event_id, 'mec_imported_from_google', true))
                {
                    $g_events_upserted[] = $g_event_id;
                    continue;
                }

                // Update Event Data
                $g_event->setSummary($event_data['summary']);
                $g_event->setLocation($event_data['location']);
                $g_event->setDescription($event_data['description']);
                $g_event->setRecurrence($event_data['recurrence']);

                $start = new Google_Service_Calendar_EventDateTime();
                $start->setDateTime($event_data['start']['dateTime']);
                $start->setTimeZone($event_data['start']['timeZone']);
                $g_event->setStart($start);

                $end = new Google_Service_Calendar_EventDateTime();
                $end->setDateTime($event_data['end']['dateTime']);
                $end->setTimeZone($event_data['end']['timeZone']);
                $g_event->setEnd($end);

                // Status
                $g_event->setStatus('confirmed');

                $g_updated_event = $service->events->update($calendar_id, $g_event_id, $g_event);

                $g_events_updated[] = array('title'=>$data->title, 'message'=>$g_updated_event->htmlLink);
                $g_events_upserted[] = $g_event_id;
            }
            catch(Exception $ex)
            {
                $g_events_not_inserted[] = array('title'=>$data->title, 'message'=>$ex->getMessage());
            }
        }
        else $g_events_not_inserted[] = array('title'=>$data->title, 'message'=>$ex->getMessage());
    }
}

$previously_upserted = get_option('mec_ix_g_export_upserted');
if(!is_array($previously_upserted)) $previously_upserted = array();

update_option('mec_ix_g_export_upserted', $g_events_upserted);

// Delete Events
foreach($previously_upserted as $g_event_id)
{
    // It's existing event
    if(in_array($g_event_id, $g_events_upserted)) continue;

    try
    {
        $service->events->delete($calendar_id, $g_event_id);
        $g_events_deleted[] = $g_event_id;
    }
    catch(Exception $ex)
    {
    }
}

$results = '<ul>';
foreach($g_events_not_inserted as $g_event_not_inserted) $results .= '<li><strong>'.$g_event_not_inserted['title'].'</strong>: '.$g_event_not_inserted['message'].'</li>';
$results .= '<ul>';

$message = (count($g_events_inserted) ? sprintf(__('%s events added to Google Calendar with success.', 'mec'), '<strong>'.count($g_events_inserted).'</strong>') : '');
$message .= (count($g_events_updated) ? ' '.sprintf(__('%s previously added events get updated.', 'mec'), '<strong>'.count($g_events_updated).'</strong>') : '');
$message .= (count($g_events_not_inserted) ? ' '.sprintf(__('%s failed to add events because: %s', 'mec'), '<strong>'.count($g_events_not_inserted).'</strong>', $results) : '');
$message .= (count($g_events_deleted) ? ' '.sprintf(__('%s events deleted successfully.', 'mec'), '<strong>'.count($g_events_deleted).'</strong>') : '');

if(!isset($internal_cron_system))
{
    exit(trim($message));
}