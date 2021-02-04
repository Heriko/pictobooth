<?php
/**
 * Calendar header template
 */

global $wp_locale;

?>
<caption class="jet-calendar-caption">
	<div class="jet-calendar-caption__wrap wrap-<?php echo $settings['caption_layout']; ?>">
		<div class="jet-calendar-caption__name"><?php echo date_i18n( 'F Y', $current_month ); ?></div>
		<div class="jet-calendar-nav__link nav-link-prev" data-month="<?php echo $human_read_prev; ?>">
			<i class="fa fa-angle-left" aria-hidden="true"></i>
		</div>
		<div class="jet-calendar-nav__link nav-link-next" data-month="<?php echo $human_read_next; ?>">
			<i class="fa fa-angle-right" aria-hidden="true"></i>
		</div>
	</div>
</caption>
<thead class="jet-calendar-header">
	<tr class="jet-calendar-header__week"><?php

		for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {

			switch ( $days_format ) {
				case 'short':
					$day_name = $wp_locale->get_weekday_abbrev( $wd );
					break;

				case 'initial':
					$day_name = $wp_locale->get_weekday_initial( $wd );
					break;

				default:
					$day_name = $wd;
					break;
			}

			printf( '<th class="jet-calendar-header__week-day">%s</th>', $day_name );
		}

	?></tr>
</thead>