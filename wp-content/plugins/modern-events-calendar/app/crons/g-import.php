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
require_once ABSPATH.'wp-admin/includes/post.php';

/** @var $main MEC_main **/

$main = MEC::getInstance('app.libraries.main');
$db = $main->getDB();

// Get MEC IX options
$ix = $main->get_ix_options();

// Auto sync is disabled
if(!isset($internal_cron_system) and (!isset($ix['sync_g_import']) or (isset($ix['sync_g_import']) and !$ix['sync_g_import']))) exit(__('Auto-import for Google Calendar is disabled!', 'mec'));

$api_key = isset($ix['google_import_api_key']) ? $ix['google_import_api_key'] : NULL;
$calendar_id = isset($ix['google_import_calendar_id']) ? $ix['google_import_calendar_id'] : NULL;

if(!isset($internal_cron_system) and (!trim($api_key) or !trim($calendar_id))) exit(__('API key and Calendar ID are required!', 'mec'));

$client = new Google_Client();
$client->setApplicationName('Modern Events Calendar');
$client->setAccessType('online');
$client->setScopes(array('https://www.googleapis.com/auth/calendar.readonly'));
$client->setDeveloperKey($api_key);

$service = new Google_Service_Calendar($client);

try
{
    // Timezone
    $timezone = $main->get_timezone();
        
    $args = array();
    $args['timeMin'] = date('c', strtotime('Today'));
    $args['maxResults'] = 50000;

    $response = $service->events->listEvents($calendar_id, $args);
    
    // Imported Events
    $posts = array();
    $posts_deleted = array();

    foreach($response->getItems() as $event)
    {
        // There is not title for event
        if(trim($event->getSummary()) == '') continue;
        
        try
        {
            $event = $service->events->get($calendar_id, $event->id, array('timeZone' => $timezone));
        }
        catch(Exception $e)
        {
            continue;
        }
        
        // Event Title and Content
        $title = $event->getSummary();
        $description = $event->getDescription();
        $gcal_ical_uid = $event->getICalUID();
        $gcal_id = $event->getId();

        // Event location
        $location = $event->getLocation();

        // Import Event Locations into MEC locations
        $location_ex = explode(',', $location);
        $location_id = $main->save_location(array
        (
            'name'=>trim($location_ex[0]),
            'address'=>$location
        ));

        // Event Organizer
        $organizer = $event->getOrganizer();

        // Import Event Organizer into MEC organizers
        $organizer_id = $main->save_organizer(array
        (
            'name'=>$organizer->getDisplayName(),
            'email'=>$organizer->getEmail()
        ));

        // Event Start Date and Time
        $start = $event->getStart();

        $g_start_date = $start->getDate();
        $g_start_datetime = $start->getDateTime();

        $date_start = new DateTime((trim($g_start_datetime) ? $g_start_datetime : $g_start_date));
        $start_date = $date_start->format('Y-m-d');
        $start_hour = 8;
        $start_minutes = '00';
        $start_ampm = 'AM';

        if(trim($g_start_datetime))
        {
            $start_hour = $date_start->format('g');
            $start_minutes = $date_start->format('i');
            $start_ampm = $date_start->format('A');
        }

        // Event End Date and Time
        $end = $event->getEnd();

        $g_end_date = $end->getDate();
        $g_end_datetime = $end->getDateTime();
        
        $date_end = new DateTime((trim($g_end_datetime) ? $g_end_datetime : $g_end_date));
        $end_date = $date_end->format('Y-m-d');
        $end_hour = 6;
        $end_minutes = '00';
        $end_ampm = 'PM';

        if(trim($g_end_datetime))
        {
            $end_hour = $date_end->format('g');
            $end_minutes = $date_end->format('i');
            $end_ampm = $date_end->format('A');
        }

        // Event Time Options
        $allday = 0;

        // Both Start and Date times are empty so it's all day event
        if(!trim($g_end_datetime) and !trim($g_start_datetime))
        {
            $allday = 1;

            $start_hour = 0;
            $start_minutes = 0;
            $start_ampm = 'AM';

            $end_hour = 11;
            $end_minutes = 55;
            $end_ampm = 'PM';
        }

        // Recurring Event
        if($event->getRecurrence())
        {
            $repeat_status = 1;
            $r_rules = $event->getRecurrence();

            $i = 0;

            do
            {
                $g_recurrence_rule = $r_rules[$i];
                $main_rule_ex = explode(':', $g_recurrence_rule);
                $rules = explode(';', $main_rule_ex[1]);

                $i++;
            } while($main_rule_ex[0] != 'RRULE' and isset($r_rules[$i]));

            $rule = array();
            foreach($rules as $rule_row)
            {
                $ex = explode('=', $rule_row);
                $key = strtolower($ex[0]);
                $value = ($key == 'until' ? $ex[1] : strtolower($ex[1]));

                $rule[$key] = $value;
            }

            $interval = NULL;
            $year = NULL;
            $month = NULL;
            $day = NULL;
            $week = NULL;
            $weekday = NULL;
            $weekdays = NULL;
            $advanced_days = NULL;

            if($rule['freq'] == 'daily')
            {
                $repeat_type = 'daily';
                $interval = isset($rule['interval']) ? $rule['interval'] : 1;
            }
            elseif($rule['freq'] == 'weekly')
            {
                $repeat_type = 'weekly';
                $interval = isset($rule['interval']) ? $rule['interval']*7 : 7;
            }
            elseif($rule['freq'] == 'monthly' and isset($rule['byday']) and trim($rule['byday']))
            {
                $repeat_type = 'advanced';

                $adv_week = (isset($rule['bysetpos']) and trim($rule['bysetpos']) != '') ? $rule['bysetpos'] : (int) substr($rule['byday'], 0, -2);
                if($adv_week < 0) $adv_week = 'l';

                $adv_day = str_replace($adv_week, '', $rule['byday']);

                $mec_adv_day = 'Sat';
                if($adv_day == 'su') $mec_adv_day = 'Sun';
                elseif($adv_day == 'mo') $mec_adv_day = 'Mon';
                elseif($adv_day == 'tu') $mec_adv_day = 'Tue';
                elseif($adv_day == 'we') $mec_adv_day = 'Wed';
                elseif($adv_day == 'th') $mec_adv_day = 'Thu';
                elseif($adv_day == 'fr') $mec_adv_day = 'Fri';

                $advanced_days = array($mec_adv_day.'.'.$adv_week);
            }
            elseif($rule['freq'] == 'monthly')
            {
                $repeat_type = 'monthly';

                $year = '*';
                $month = '*';

                $s = $start_date;
                $e = $end_date;

                $_days = array();
                while(strtotime($s) <= strtotime($e))
                {
                    $_days[] = date('d', strtotime($s));
                    $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
                }

                $day = ','.implode(',', array_unique($_days)).',';

                $week = '*';
                $weekday = '*';
            }
            elseif($rule['freq'] == 'yearly')
            {
                $repeat_type = 'yearly';

                $year = '*';

                $s = $start_date;
                $e = $end_date;

                $_months = array();
                $_days = array();
                while(strtotime($s) <= strtotime($e))
                {
                    $_months[] = date('m', strtotime($s));
                    $_days[] = date('d', strtotime($s));

                    $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
                }

                $month = ','.implode(',', array_unique($_months)).',';
                $day = ','.implode(',', array_unique($_days)).',';

                $week = '*';
                $weekday = '*';
            }
            else $repeat_type = '';

            // Custom Week Days
            if($repeat_type == 'weekly' and isset($rule['byday']) and count(explode(',', $rule['byday'])) > 1)
            {
                $g_week_days = explode(',', $rule['byday']);
                $week_day_mapping = array('mo'=>1, 'tu'=>2, 'we'=>3, 'th'=>4, 'fr'=>5, 'sa'=>6, 'su'=>7);

                $weekdays = '';
                foreach($g_week_days as $g_week_day) $weekdays .= $week_day_mapping[$g_week_day].',';

                $weekdays = ','.trim($weekdays, ', ').',';
                $interval = NULL;

                $repeat_type = 'certain_weekdays';
            }

            $finish = isset($rule['until']) ? date('Y-m-d', strtotime($rule['until'])) : NULL;
        }
        // Single Event
        else
        {
            // It's a one day single event but google sends 2020-12-12 as end date if start date is 2020-12-11
            if(trim($g_end_datetime) == '' and date('Y-m-d', strtotime('-1 day', strtotime($end_date))) == $start_date)
            {
                $end_date = $start_date;
            }
            // It's all day event so we should reduce one day from the end date! Google provides 2020-12-12 while the event ends at 2020-12-11
            elseif($allday)
            {
                $diff = $main->date_diff($start_date, $end_date);
                if(($diff ? $diff->days : 0) > 1)
                {
                    $date_end->sub(new DateInterval('P1D'));
                    $end_date = $date_end->format('Y-m-d');
                }
            }

            $repeat_status = 0;
            $g_recurrence_rule = '';
            $repeat_type = '';
            $interval = NULL;
            $finish = $end_date;
            $year = NULL;
            $month = NULL;
            $day = NULL;
            $week = NULL;
            $weekday = NULL;
            $weekdays = NULL;
            $advanced_days = NULL;
        }

        $args = array
        (
            'title'=>$title,
            'content'=>$description,
            'location_id'=>$location_id,
            'organizer_id'=>$organizer_id,
            'date'=>array
            (
                'start'=>array(
                    'date'=>$start_date,
                    'hour'=>$start_hour,
                    'minutes'=>$start_minutes,
                    'ampm'=>$start_ampm,
                ),
                'end'=>array(
                    'date'=>$end_date,
                    'hour'=>$end_hour,
                    'minutes'=>$end_minutes,
                    'ampm'=>$end_ampm,
                ),
                'repeat'=>array(),
                'allday'=>$allday,
                'comment'=>'',
                'hide_time'=>0,
                'hide_end_time'=>0,
            ),
            'start'=>$start_date,
            'start_time_hour'=>$start_hour,
            'start_time_minutes'=>$start_minutes,
            'start_time_ampm'=>$start_ampm,
            'end'=>$end_date,
            'end_time_hour'=>$end_hour,
            'end_time_minutes'=>$end_minutes,
            'end_time_ampm'=>$end_ampm,
            'repeat_status'=>$repeat_status,
            'repeat_type'=>$repeat_type,
            'interval'=>$interval,
            'finish'=>$finish,
            'year'=>$year,
            'month'=>$month,
            'day'=>$day,
            'week'=>$week,
            'weekday'=>$weekday,
            'weekdays'=>$weekdays,
            'meta'=>array
            (
                'mec_source'=>'google-calendar',
                'mec_gcal_ical_uid'=>$gcal_ical_uid,
                'mec_gcal_id'=>$gcal_id,
                'mec_gcal_calendar_id'=>$calendar_id,
                'mec_g_recurrence_rule'=>$g_recurrence_rule,
                'mec_allday'=>$allday,
                'mec_advanced_days'=>$advanced_days,
            )
        );

        $post_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$gcal_id' AND `meta_key`='mec_gcal_id'", 'loadResult');

        // Event is created in MEC so don't import it from Google
        if($post_id and !get_post_meta($post_id, 'mec_imported_from_google', true))
        {
            // Add it to the imported posts
            $posts[] = $post_id;

            continue;
        }

        // Imported From Google
        if(!post_exists($title, $description, '', $main->get_main_post_type())) $args['meta']['mec_imported_from_google'] = 1;

        // Insert the event into MEC
        $post_id = $main->save_event($args, $post_id);

        // Add it to the imported posts
        $posts[] = $post_id;
        
        // Set location to the post
        if($location_id) wp_set_object_terms($post_id, (int) $location_id, 'mec_location');

        // Set organizer to the post
        if($organizer_id) wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');
    }

    $previously_upserted = get_option('mec_ix_g_import_upserted');
    if(!is_array($previously_upserted)) $previously_upserted = array();

    update_option('mec_ix_g_import_upserted', $posts);

    // Delete Events
    foreach($previously_upserted as $mec_event_id)
    {
        // It's existing event
        if(in_array($mec_event_id, $posts)) continue;

        wp_trash_post($mec_event_id);
        $posts_deleted[] = $mec_event_id;
    }

    $message = (count($posts) ? sprintf(__('%s google events imported/updated.', 'mec'), count($posts)) : '');
    $message .= (count($posts_deleted) ? ' '.sprintf(__('%s events trashed successfully.', 'mec'), count($posts_deleted)) : '');

    if(!isset($internal_cron_system)) exit($message);
}
catch(Exception $e)
{
    if(!isset($internal_cron_system))
    {
        $error = $e->getMessage();
        exit($error);
    }
}