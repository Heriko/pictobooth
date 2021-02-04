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
if(!isset($internal_cron_system) and (!isset($ix['sync_meetup_import']) or (isset($ix['sync_meetup_import']) and !$ix['sync_meetup_import']))) exit(__('Auto-import for Meetup is disabled!', 'mec'));
elseif(isset($internal_cron_system)) return;

$api_key = isset($ix['meetup_api_key']) ? $ix['meetup_api_key'] : NULL;
$group_url = isset($ix['meetup_group_url']) ? $ix['meetup_group_url'] : NULL;

if(!isset($internal_cron_system) and (!trim($api_key) or !trim($group_url))) exit(__('API key and Group URL are required!', 'mec'));
elseif(isset($internal_cron_system)) return;

try
{
    $meetup = new Meetup(array(
        'key' => $api_key
    ));

    $events = $meetup->getEvents(array(
        'urlname' => $group_url,
    ));

    // Timezone
    $timezone = $main->get_timezone();

    // MEC File
    $file = $main->getFile();
    $wp_upload_dir = wp_upload_dir();
    
    // Imported Events
    $posts = array();
    
    foreach($events as $e)
    {
        // There is not title for event
        if(trim($e->name) == '') continue;
        
        try
        {
            $event = $meetup->getEvent(array(
                'urlname' => $group_url,
                'id' => $e->id,
                'fields' => 'event_hosts,featured_photo,series,short_link'
            ));
        }
        catch(Exception $e)
        {
            continue;
        }

        // Check if Series already Imported
        $series_id = NULL;
        if(isset($event->series) and isset($event->series->id))
        {
            $series_id = $event->series->id;

            $post_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$series_id' AND `meta_key`='mec_meetup_series_id'", 'loadResult');
            if($post_id) continue;
        }

        // Event Title and Content
        $title = $event->name;
        $description = $event->description;
        $mcal_id = $event->id;

        // Event location
        $location = isset($event->venue) ? $event->venue : NULL;
        $location_id = 1;

        // Import Event Locations into MEC locations
        if($location)
        {
            $address = isset($location->address_1) ? $location->address_1 : '';
            $address .= isset($location->city) ? ', '.$location->city : '';
            $address .= isset($location->state) ? ', '.$location->state : '';
            $address .= isset($location->localized_country_name) ? ', '.$location->localized_country_name : '';

            $location_id = $main->save_location(array
            (
                'name'=>trim($location->name),
                'latitude'=>trim($location->lat),
                'longitude'=>trim($location->lon),
                'address'=>$address
            ));
        }

        // Event Organizer
        $organizers = isset($event->event_hosts) ? $event->event_hosts : NULL;
        $main_organizer_id = 1;
        $additional_organizer_ids = array();

        // Import Event Organizer into MEC organizers
        if($organizers)
        {
            $o = 1;
            foreach($organizers as $organizer)
            {
                $organizer_id = $main->save_organizer(array
                (
                    'name'=>$organizer->name,
                    'thumbnail'=>((isset($organizer->photo) and isset($organizer->photo->photo_link)) ? $organizer->photo->photo_link : NULL)
                ));

                if($o == 1) $main_organizer_id = $organizer_id;
                else $additional_organizer_ids[] = $organizer_id;

                $o++;
            }
        }

        // Event Start Date and Time
        $start = (int) ($event->time/1000);

        $date_start = new DateTime(date('Y-m-d H:i:s', $start), new DateTimeZone('UTC'));
        $date_start->setTimezone(new DateTimeZone($timezone));

        $start_date = $date_start->format('Y-m-d');
        $start_hour = $date_start->format('g');
        $start_minutes = $date_start->format('i');
        $start_ampm = $date_start->format('A');

        // Event End Date and Time
        $end = (int) (($event->time+$event->duration)/1000);

        $date_end = new DateTime(date('Y-m-d H:i:s', $end), new DateTimeZone('UTC'));
        $date_end->setTimezone(new DateTimeZone($timezone));

        $end_date = $date_end->format('Y-m-d');
        $end_hour = $date_end->format('g');
        $end_minutes = $date_end->format('i');
        $end_ampm = $date_end->format('A');

        // Meetup Link
        $more_info = isset($event->link) ? $event->link : (isset($event->short_link) ? $event->short_link : '');

        // Fee Options
        $fee = 0;
        if(isset($event->fee)) $fee = $event->fee->amount.' '.$event->fee->currency;

        // Event Time Options
        $allday = 0;

        // Recurring Event
        if(isset($event->series) and $event->series)
        {
            $repeat_status = 1;

            $interval = NULL;
            $year = NULL;
            $month = NULL;
            $day = NULL;
            $week = NULL;
            $weekday = NULL;
            $weekdays = NULL;

            if(isset($event->series->weekly))
            {
                $repeat_type = 'weekly';
                $interval = isset($event->series->weekly->interval) ? $event->series->weekly->interval*7 : 7;
            }
            elseif(isset($event->series->monthly))
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
            else $repeat_type = '';

            // Custom Week Days
            if($repeat_type == 'weekly' and isset($event->series->weekly->days_of_week) and is_array($event->series->weekly->days_of_week) and count($event->series->weekly->days_of_week))
            {
                $weekdays = ','.trim(implode(',', $event->series->weekly->days_of_week), ', ').',';
                $interval = NULL;

                $repeat_type = 'certain_weekdays';
            }

            $finish = isset($event->series->end_date) ? date('Y-m-d', ($event->series->end_date/1000)) : NULL;
        }
        // Single Event
        else
        {
            $repeat_status = 0;
            $repeat_type = '';
            $interval = NULL;
            $finish = $end_date;
            $year = NULL;
            $month = NULL;
            $day = NULL;
            $week = NULL;
            $weekday = NULL;
            $weekdays = NULL;
        }

        $args = array
        (
            'title'=>$title,
            'content'=>$description,
            'location_id'=>$location_id,
            'organizer_id'=>$main_organizer_id,
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
                'mec_source'=>'meetup',
                'mec_meetup_id'=>$mcal_id,
                'mec_meetup_series_id'=>$series_id,
                'mec_more_info'=>$more_info,
                'mec_more_info_title'=>__('Check at Meetup', 'mec'),
                'mec_more_info_target'=>'_self',
                'mec_cost'=>$fee,
                'mec_meetup_url'=>$group_url,
                'mec_allday'=>$allday
            )
        );

        $post_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$mcal_id' AND `meta_key`='mec_meetup_id'", 'loadResult');

        // Insert the event into MEC
        $post_id = $main->save_event($args, $post_id);
        $posts[] = $post_id;

        // Set location to the post
        if($location_id) wp_set_object_terms($post_id, (int) $location_id, 'mec_location');

        // Set organizer to the post
        if($main_organizer_id) wp_set_object_terms($post_id, (int) $main_organizer_id, 'mec_organizer');

        // Set Additional Organizers
        if(count($additional_organizer_ids))
        {
            foreach($additional_organizer_ids as $additional_organizer_id) wp_set_object_terms($post_id, (int) $additional_organizer_id, 'mec_organizer', true);
            update_post_meta($post_id, 'mec_additional_organizer_ids', $additional_organizer_ids);
        }

        // Featured Image
        if(!has_post_thumbnail($post_id) and isset($event->featured_photo) and isset($event->featured_photo->photo_link))
        {
            $photo = $main->get_web_page($event->featured_photo->photo_link);
            $file_name = md5($post_id).'.'.$main->get_image_type_by_buffer($photo);

            $path = rtrim($wp_upload_dir['path'], DS.' ').DS.$file_name;
            $url = rtrim($wp_upload_dir['url'], '/ ').'/'.$file_name;

            $file->write($path, $photo);
            $main->set_featured_image($url, $post_id);
        }
    }

    if(!isset($internal_cron_system))
    {
        echo sprintf(__('%s meetup events imported/updated.', 'mec'), count($posts));
        exit;
    }
}
catch(Exception $e)
{
    if(!isset($internal_cron_system))
    {
        $error = $e->getMessage();
        exit($error);
    }
}