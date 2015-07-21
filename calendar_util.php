<?php
//****** Helper functions ******
/****** Converts month number to month name *****/
function monthIntToString($month) {
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
    return $month_name;
}

/****** Header for month view (displays Month Year format) *****/
function renderMonthHeader($month,$year){
    $month_name = monthIntToString($month);

    /* table headings */
    /* AJAX data: */
    /* data-yearnum = current year */
    /* data-prev = tells calendar to decrease month */
    /* data-next = tells calendar to increase month */
    /* data-monthnum = current month */
<<<<<<< HEAD
    $header = '<tr class = "calendar-row">
                <td id = "prev_nav" class="nav_elt_month_prev" data-yearnum = "'.$year.'" data-prev = "-1" data-monthnum ="'.$month.'">&lt;</td>
                <td id = "month_display" class="month-name" data-monthnum ="'.$month.'" data-yearnum = "'.$year.'" colspan="5" style = "text-align: center">'.$month_name.' '.$year.'</td>
=======
    $header = '<table cellpadding="0" cellspacing="0" class="calendar"><tr class = "calendar-row">
                <td id = "prev_nav" class="nav_elt_month_prev" data-yearnum = "'.$year.'" data-prev = "-1" data-monthnum ="'.$month.'">&lt;</td>
                <td id = "month_display" class="month-name" colspan="5" style = "text-align: center">'.$month_name.' '.$year.'</td>
>>>>>>> 9f991ff2deb92aec0a2f5016311f53b34def6844
                <td id = "next_nav" class="nav_elt_month_next" data-yearnum = "'.$year.'" data-next = "1" data-monthnum ="'.$month.'">&gt;</td>
                </tr>';
    return $header;
}

/****** Header for day view (displays Month Day format) *****/
function renderDayHeader($month,$day) {
    $month_name = monthIntToString($month);
    $header = '<table cellpadding="0" cellspacing="0" class="calendar">
            <tr class = "calendar-row">
            <td class="nav_elt_day_prev" data-monthnum = "'.$month.'" data-prev-day = "-1" data-daynum ="'.$day.'">&lt;</td>
            <td class="day-name" style = "text-align: center">'.$month_name. ' ' . $day.'</td>
            <td class="nav_elt_day_next" data-monthnum = "'.$month.'" data-next-day = "1" data-daynum ="'.$day.'">&gt;</td>
            </tr></table>';
    return $header;
}

/****** Add in items for daily view *****/
function renderItemRows($items,$headings) {
    /* draw the calendar for all pieces of equipment in each subgroup */
    $rows = "";
    foreach($items as $item) {
        $rows .= '<td class="daily-items">'.$item.'</td>';
        /* draw all the time cells for a given piece of equipment */
        for ($x = 1; $x < count($headings); $x++):
            $rows .= '<td class="calendar-time"></td>';
        endfor;
        $rows .= '</tr>';
    }
    return $rows;
}

/******* Add in all the cells with the appropriate days and items ******/
function renderCalendarCells($month,$year,$schedule)
{
    /* fill in the days */
    $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
    $days_in_this_week = 1;
    $day_counter = 0;

    $cells = '<tr class="calendar-row">';

    /* prints blank days before the first of the month */
    for ($x = 0; $x < $running_day; $x++):
        $cells .= '<td class="calendar-day-np"></td>';
        $days_in_this_week++;
    endfor;

    $month_bool = FALSE;
    $year_bool = FALSE;
    //Extract month of the start on date and if it is not within the month then discard items
    //Extract year of the start on date and if it is not within the year then discard items
    foreach ($schedule as $sched) {
        if (intval(substr($sched->start_on_date, 0, 4)) == $year) {
            $year_bool = TRUE;
        }
        if (intval(substr($sched->start_on_date, 5, 2)) == $month) {
            $month_bool = TRUE;
        }
    }

    //Requested_EqGroup gets the schedule which gets the reservations which gets the eq_items which gets the eq_subgroup
    //Way to get the name of the item
//    foreach ($schedule as $sched) {
//        foreach ($sched->reservations as $r) {
//            $items[] = $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name;
//        }
//    }
//
//    util_prePrintR($items);

    //*******TO IMPLEMENT: REPEATS FOR ITEM DAYS AND MULTIPLE ITEMS*******//

    for($list_day = 1; $list_day <= $days_in_month; $list_day++):

        $cells .='<td id = "day_lists" class="calendar-day" data-monthnum = "'.$month.'" data-caldate = "'.$list_day.'">';
        /* add in the day number */
        $cells.= '<div class="day-number">'.$list_day.'</div>';

        /** add in items here */
        //if list day is equal to the start_on_date day of the item then add in the item and keep on adding in until the list day equals the end_on_date day
        foreach ($schedule as $sched) {
            foreach ($sched->reservations as $r) {
                if ($year_bool && $month_bool) {
                    if (intval(substr($sched->start_on_date, 8, 2)) <= $list_day && $list_day <= intval(substr($sched->end_on_date, 8, 2))) {
//                        util_prePrintR($r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name);
                        $cells .= '<p class="monthly-items" id="schedule-'.$sched->schedule_id.'" start-date="'.$sched->start_on_date.'"
                        start-time="'.$sched->timeblock_start_time.'" duration="'.$sched->timeblock_duration.'">' . $sched->toString() .
                            '<br>' . $r->eq_item->eq_subgroup->name . ':' . $r->eq_item->name . '</p>';
                    }
                }
            }
        }

        $cells.= '</td>';
        if($running_day == 6):
            $cells.= '</tr>';
            if(($day_counter+1) != $days_in_month):
                $cells.= '<tr class="calendar-row">';
            endif;
            $running_day = -1;
            $days_in_this_week = 0;
        endif;
        $days_in_this_week++; $running_day++; $day_counter++;
    endfor;

    /* finish the rest of the days in the week */
    if($days_in_this_week < 8):
        for($x = 1; $x <= (8 - $days_in_this_week); $x++):
            $cells .= '<td class="calendar-day-np"> </td>';
        endfor;
    endif;

    /* final row */
    $cells.= '</tr>';

    return $cells;
}

//********************************************************************************
//****************** Draw actual calendars here **********************************
//********************************************************************************

/*************** MONTHLY CALENDAR *********************/
function draw_MonthlyCalendar($month,$year,$all_schedules) {
    /* render header for the monthly calendar */
    $header = '<table cellpadding="0" cellspacing="0" class="calendar">';
    $header .= renderMonthHeader($month, $year);

    /* draw table */
    $headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    $calendar_days = '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

    /* make the cells with days and items */
    $calendar_cells = renderCalendarCells($month, $year, $all_schedules);

	/* end the table */
	$end= '</table>';

	/* all done, return result */
	return $header.$calendar_days.$calendar_cells.$end;
}

/*************** DAILY CALENDAR *********************/
function draw_SingleDayCalendar($month,$day,$items) {

    /* date header */
    $header = renderDayHeader($month,$day);

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
    $calendar .= renderItemRows($items,$headings);

    $calendar .= '</table></div>';

    $calendar .= '<button class="show_month_button">Go Back To Monthly View</button>';

    return $header.$calendar;
}