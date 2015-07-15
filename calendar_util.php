<?php
//**********************************************************
//**********************************************************
// DAILY CALENDAR
function draw_SingleDayCalendar($month,$day,$items) {
    /* draw table */
    $month_name = $month;

    /* actual month name */
    switch($month){
        case '1':
            $month_name = 'January';
            break;
        case '2':
            $month_name = 'February';
            break;
        case '3':
            $month_name = 'March';
            break;
        case '4':
            $month_name = 'April';
            break;
        case '5':
            $month_name = 'May';
            break;
        case '6':
            $month_name = 'June';
            break;
        case '7':
            $month_name = 'July';
            break;
        case '8':
            $month_name = 'August';
            break;
        case '9':
            $month_name = 'September';
            break;
        case '10':
            $month_name = 'October';
            break;
        case '11':
            $month_name = 'November';
            break;
        case '12':
            $month_name = 'December';
            break;
        default:
            $month_name = 'whoops';

    }
    /* date header */
    $header = '<table cellpadding="0" cellspacing="0" class="calendar">
                <tr class = "calendar-row">
                <td class="nav_elt_day_prev" data-monthnum = "'.$month.'" data-prev-day = "-1" data-daynum ="'.$day.'">&lt;</td>
                <td class="day-name" style = "text-align: center">'.$month_name. ' ' . $day.'</td>
                <td class="nav_elt_day_next" data-monthnum = "'.$month.'" data-next-day = "1" data-daynum ="'.$day.'">&gt;</td>
                </tr></table>';

    /* table headings */
    $calendar = '<div style="overflow-x:scroll; width:925px"><table cellpadding="0" cellspacing="2" class="header">';


    $headings = array('','12:00 AM', '12:15 AM', '12:30 AM', '12:45 AM', '1:00 AM','1:15 AM', '1:30 AM', '1:45 AM', '2:00 AM', '2:15 AM', '2:30 AM', '2:45 AM', '3:00 AM',
            '3:15 AM', '3:30 AM', '3:45 AM', '4:00 AM', '4:15 AM', '4:30 AM', '4:45 AM', '5:00 AM', '5:15 AM', '5:30 AM', '5:45 AM', '6:00 AM', '6:15 AM', '6:30 AM',
            '6:45 AM', '7:00 AM', '7:15 AM', '7:30 AM', '7:45 AM', '8:00 AM', '8:15 AM', '8:30 AM', '8:45 AM', '9:00 AM', '9:15 AM', '9:30 AM', '9:45 AM', '10:00 AM',
            '10:15 AM', '10:30 AM', '10:45 AM', '11:00 AM', '11:15 AM', '11:30 AM', '11:45 AM', '12:00 PM', '12:15 PM', '12:30 PM', '12:45 PM', '1:00 PM', '1:15 PM',
            '1:30 PM', '1:45 PM', '2:00 PM', '2:15 PM', '2:30 PM', '2:45 PM', '3:00 PM', '3:15 PM', '3:30 PM', '3:45 PM', '4:00 PM', '4:15 PM', '4:30 PM', '4:45 PM',
            '5:00 PM', '5:15 PM', '5:30 PM', '5:45 PM', '6:00 PM', '6:15 PM', '6:30 PM', '6:45 PM', '7:00 PM', '7:15 PM', '7:30 PM', '7:45 PM', '8:00 PM', '8:15 PM',
            '8:30 PM', '8:45 PM', '9:00 PM', '9:15 PM', '9:30 PM', '9:45 PM', '10:00 PM', '10:15 PM', '10:30 PM', '10:45 PM', '11:00 PM', '11:15 PM', '11:30 PM', '11:45 PM');
    $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

//    $time_slot = date('w',mktime(0,0,0,$month,1,$year));


    /* row for 1st piece of equipment */
    $calendar .= '<tr class="calendar-row">';

    /* draw the calendar for all pieces of equipment in each subgroup */
    foreach($items as $item) {
        $calendar .= '<td class="daily-items">'.$item.'</td>';
        /* draw all the time cells for a given piece of equipment */
        for ($x = 1; $x < count($headings); $x++):
            $calendar .= '<td class="calendar-time"></td>';
        endfor;
        $calendar .= '</tr>';
    }

    $calendar .= '</table></div>';

    $calendar .= '<button class="show_month_button">Go Back To Monthly View</button>';

    return $header.$calendar;
}

/* a method to get all the items that can be reserved */
//function getUsers($eqg) {
//    return $eqg->eq_item->name;
//}

//**********************************************************
//**********************************************************
// MONTHLY CALENDAR
function draw_MonthlyCalendar($month,$year,$items) {
    /* draw table */
    $calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';

    $month_name = $month;

    /* actual month name */
    switch($month){
        case '1':
            $month_name = 'January';
            break;
        case '2':
            $month_name = 'February';
            break;
        case '3':
            $month_name = 'March';
            break;
        case '4':
            $month_name = 'April';
            break;
        case '5':
            $month_name = 'May';
            break;
        case '6':
            $month_name = 'June';
            break;
        case '7':
            $month_name = 'July';
            break;
        case '8':
            $month_name = 'August';
            break;
        case '9':
            $month_name = 'September';
            break;
        case '10':
            $month_name = 'October';
            break;
        case '11':
            $month_name = 'November';
            break;
        case '12':
            $month_name = 'December';
            break;
        default:
            $month_name = 'whoops';

    }
    /* table headings */
    /* AJAX data: */
    /* data-yearnum = current year */
    /* data-prev = tells calendar to decrease month */
    /* data-next = tells calendar to increase month */
    /* data-monthnum = current month */
    $calendar .=  '<tr class = "calendar-row">
                <td class="nav_elt_month_prev" data-yearnum = "'.$year.'" data-prev = "-1" data-monthnum ="'.$month.'">&lt;</td>
                <td class="month-name" colspan="5" style = "text-align: center">'.$month_name.' '.$year.'</td>
                <td class="nav_elt_month_next" data-yearnum = "'.$year.'" data-next = "1" data-monthnum ="'.$month.'">&gt;</td>
                </tr>';
    $headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

    $running_day = date('w',mktime(0,0,0,$month,1,$year));
    $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
    $days_in_this_week = 1;
    $day_counter = 0;

    $calendar .='<tr class="calendar-row">';

    /* prints blank days before the first of the month */
    for($x = 0; $x < $running_day; $x++):
        $calendar .= '<td class="calendar-day-np"></td>';
        $days_in_this_week++;
    endfor;

    /* find the items: for each reservation in the subgroup, find each item and put it into an array */
//    $items = array();
//
//    foreach ($all_schedules->reservations as $r) {
//        //not entering the loop
////        util_prePrintR($r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name);
//        $items[] = $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name;
//    }

    /* fill in the rest of the days */
    for($list_day = 1; $list_day <= $days_in_month; $list_day++):
        $calendar .= '<td class="calendar-day" data-monthnum = "'.$month.'" data-caldate = "'.$list_day.'" data-items = "'.$items.'">';
            /* add in the day number */
			$calendar.= '<div class="day-number">'.$list_day.'</div>';

		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
        /** add in items here */
        foreach($items as $item){
            $calendar .= '<p class="monthly-items">'.$item.'</p>';
        }
//			$calendar.= str_repeat('<p></p>',2);

//        $schedule->toString();

		$calendar.= '</td>';
		if($running_day == 6):
            $calendar.= '</tr>';
            if(($day_counter+1) != $days_in_month):
                $calendar.= '<tr class="calendar-row">';
            endif;
            $running_day = -1;
            $days_in_this_week = 0;
        endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
        for($x = 1; $x <= (8 - $days_in_this_week); $x++):
            $calendar.= '<td class="calendar-day-np"> </td>';
        endfor;
    endif;

	/* final row */
	$calendar.= '</tr>';

	/* end the table */
	$calendar.= '</table>';

	/* all done, return result */
	return $calendar;
}