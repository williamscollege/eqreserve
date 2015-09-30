<?php
//******************************//
//****** Helper functions ******//
//******************************//
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

/*** Converts time (format: hour:minute:second) to an int ID ***/
function timeToInt($time)
{
    $timeArray = array('00:00:00', '00:15:00', '00:30:00','00:45:00','01:00:00','01:15:00','01:30:00','01:45:00','02:00:00','02:15:00',
        '02:30:00','02:45:00','03:00:00','03:15:00','03:30:00','03:45:00','04:00:00','04:15:00','04:30:00','04:45:00',
        '05:00:00','05:15:00','05:30:00','05:45:00','06:00:00','06:15:00','06:30:00','06:45:00','07:00:00','07:15:00',
        '07:30:00','07:45:00','08:00:00','08:15:00','08:30:00','08:45:00','09:00:00','09:15:00','09:30:00','09:45:00',
        '10:00:00','10:15:00','10:30:00','10:45:00','11:00:00','11:15:00','11:30:00','11:45:00','12:00:00','12:15:00',
        '12:30:00','12:45:00','13:00:00','13:15:00','13:30:00','13:45:00','14:00:00','14:15:00','14:30:00','14:45:00',
        '15:00:00','15:15:00','15:30:00','15:45:00','16:00:00','16:15:00','16:30:00','16:45:00','17:00:00','17:15:00',
        '17:30:00','17:45:00','18:00:00','18:15:00','18:30:00','18:45:00','19:00:00','19:15:00','19:30:00','19:45:00',
        '20:00:00','20:15:00','20:30:00','20:45:00','21:00:00','21:15:00','21:30:00','21:45:00','22:00:00','22:15:00',
        '22:30:00','22:45:00','23:00:00','23:15:00','23:30:00','23:45:00','24:00:00');
    if (in_array($time, $timeArray)){
        $timeID = array_search($time, $timeArray);
    } else {
        $timeID = -1;
    }
    return $timeID + 1;
}

/***** Converts duration (format: 15M) to minutes which are then converted to the number of blocks in the day ****/
function durationToInt($duration) {
    // 100 ensures it takes up the entire day

    //convert duration to minutes
    $duration = util_durToInt($duration);

    //convert to number of blocks (each block is 15 min)
    if($duration>=1440){
        $blocks = 100;
    }else{
        $blocks = floor($duration/15);
    }

    return $blocks;
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
    $header = '<tr class = "calendar-row">
                <td id = "prev_nav" class="nav_elt_month_prev calendar_header_format" data-yearnum = "'.$year.'" data-prev = "-1" data-monthnum ="'.$month.'">&lt;</td>
                <td id = "month_display" class="month-name calendar_header_format" data-monthnum ="'.$month.'" data-yearnum = "'.$year.'" colspan="5" style = "text-align: center">'.$month_name.' '.$year.'</td>
                <td id = "next_nav" class="nav_elt_month_next calendar_header_format" data-yearnum = "'.$year.'" data-next = "1" data-monthnum ="'.$month.'">&gt;</td>
                </tr>';
    return $header;
}

/****** Header for day view (displays Month Day format) *****/
function renderDayHeader($month,$day,$year) {
    $month_name = monthIntToString($month);
    $header = '<table cellpadding="0" cellspacing="0" class="calendar">
            <tr class = "calendar-row">
            <td id = "daily_prev_nav" class="nav_elt_day_prev calendar_header_format" data-monthnum = "'.$month.'" data-prev-day = "-1" data-daynum ="'.$day.'">&lt;</td>
            <td id = "day_display" class="day-name calendar_header_format" data-monthnum = "'.$month.'" data-daynum ="'.$day.'" data-yearnum ="'.$year.'" style = "text-align: center">'.$month_name. ' ' . $day.'</td>
            <td id = "daily_next_nav" class="nav_elt_day_next calendar_header_format" data-monthnum = "'.$month.'" data-next-day = "1" data-daynum ="'.$day.'">&gt;</td>
            </tr></table>';
    return $header;
}

/****** Add in items for daily view *****/
function renderItemRows($items,$headings,$scheds,$month,$day,$year)
{
    /* draw the calendar for all pieces of equipment in each subgroup */
    $rows = "";

    //counter to keep track of number of rows
    $i = 0;
    foreach ($items as $item) {
        //inserts another row of times if there are too many items to see at the same time
        if ($i > 8) {
            $rows .= '<td class="calendar-day-head calendar-day-time-head calendar_header_format">' . implode('</td><td class="calendar-day-head calendar-day-time-head calendar_header_format">', $headings) . '</td></tr>';
            $i = $i - 8;
        }
        $i++;
        $itemSched = [];
        $starts = [];
        $start_percent = [];
        $end_percent = [];
        //gets any schedules that have this item reserved
        foreach ($scheds as $s) {
            $s->loadReservations();
            foreach ($s->reservations as $r) {
                $r->loadEqItem();
                $r->loadUser();
                $id = key($items);

                if ($r->eq_item->eq_item_id == $id) {
                    array_push($itemSched, $s);
                }
            }
        }

        //for each schedule
        ## array for the start percentage (durationToInt($sched->timeblock_duration) -> percentage)
        ## array for the end percentage (durationToInt($sched->timeblock_duration) -> percentage)
        ## array for timetoInt to durationToInt

        ## timeToInt finds the number of the starting point in terms of headers/boxes based upon the date time
        ## durationToInt finds the number of boxes that the reservation should take up based upon the durations

        //durationToInt does not allow for custom but we do not allow for custom durations so it should be fine but should change if we ever do

        foreach ($itemSched as $sched) {
            foreach ($sched->time_blocks as $tb) {
                $start_tb = $tb->start_datetime;
                $end_tb = $tb->end_datetime;

                if (strtotime(substr($start_tb, 0, 10)) === strtotime($year . '-' . $month . '-' . $day) || strtotime($year . '-' . $month . '-' . $day) === strtotime(substr($end_tb, 0, 10))) {
                    if (strtotime(substr($start_tb, 0, 10)) === strtotime($year . '-' . $month . '-' . $day) && strtotime(substr($end_tb, 0, 10)) !== strtotime($year . '-' . $month . '-' . $day)) {
                        $start_minute = intval(substr($start_tb, 14, 2));
                        $end_minute = intval('59');
                        $duration = $sched->timeblock_duration;
                    } elseif (strtotime(substr($start_tb, 0, 10)) !== strtotime($year . '-' . $month . '-' . $day) && strtotime(substr($end_tb, 0, 10)) === strtotime($year . '-' . $month . '-' . $day)) {
                        $start_minute = 'x';
                        $end_minute = intval(substr($end_tb, 14, 2));

                        //Changes the duration to be in terms of the last day of the repeated reservation
                        $duration = strtotime(substr($tb->end_datetime, 11, 8)) - strtotime('00:00:00');
                        if($duration >= 3600){
                            $duration = floor($duration / 3600);
                            $duration = $duration .'H';
                        }else{
                            $duration = floor($duration / 60);
                            $duration = $duration . 'M';
                        }
                    } else {
                        $start_minute = intval(substr($start_tb, 14, 2));
                        $end_minute = intval(substr($end_tb, 14, 2));
                        $duration = $sched->timeblock_duration;
                    }

                    ## if start time % 15 != 0 (eg: start at 12:20)
                    //**** find percentage based upon the TIMEBLOCK start times and store as timetoInt($sched->timeblock_duration) to percentage to use later
                    //**** find the rounded start time to find the correct time block to start on using timeToInt
                    //******** to find percentage:
                    //******** if start time (minute) >15, then reduce until under 15 and then use formula (rounded)
                    //******** to find rounded start time:
                    //******** round the timeblock_start_time minutes to the nearest quarter hour using the formula and convert to date
                    ## else if start time % 15 == 0 then use regular start time (not rounded) and have start percentage = 100
                    if ($start_minute === 'x') {
                        $sched_tb_round = '00:00:00';
                        $start_percent[timetoInt($sched_tb_round)] = 100;
                        //the duration should only go until the end time block
                    } elseif ($start_minute % 15 != 0) {

                        while ($start_minute > 15) {
                            $start_minute -= 15;
                        }
                        $starting_perc = round((float)($start_minute / 15) * 100);
                        $sched_tb_round = round(strtotime($sched->timeblock_start_time) / (15 * 60)) * (15 * 60);
                        $sched_tb_round = strval(date('H:i:s', $sched_tb_round)); //h,m,s in 24 hour format
                        $start_percent[timetoInt($sched_tb_round)] = $starting_perc;
                    } else {
                        $sched_tb_round = $sched->timeblock_start_time;
                        $start_percent[timetoInt($sched_tb_round)] = 100;
                    }

                    ## if end time % 15 != 0 (eg: end at 12:40)
                    //**** find percentage based upon the TIMEBLOCK end times and store as timetoInt($sched->timeblock_duration) to percentage to use later
                    ## else if end time % 15 == 0 then use regular end time (not rounded) and have end percentage = 100
                    if ($end_minute % 15 != 0) {
                        while ($end_minute > 15) {
                            $end_minute -= 15;
                        }
                        $end_perc = round((float)($end_minute / 15) * 100);
                        $end_percent[timetoInt($sched_tb_round)] = $end_perc;
                    } else {
                        $end_percent[timetoInt($sched_tb_round)] = 100;
                    }

                    ## finds the start box based upon the start time given (rounded or unrounded) and relates it to the duration and the user who made the reservation
                    $starts[timetoInt($sched_tb_round)] = array(durationToInt($duration), $sched->reservations[0]->user,$sched);

                } elseif (strtotime(substr($start_tb, 0, 10)) < strtotime($year . '-' . $month . '-' . $day) && strtotime($year . '-' . $month . '-' . $day) < strtotime(substr($end_tb, 0, 10))) {
                    $sched_tb_round = '00:00:00';
                    $start_percent[timetoInt($sched_tb_round)] = 100;
                    $end_percent[timetoInt($sched_tb_round)] = 100;

                    ## finds the start box based upon the start time given (rounded or unrounded) and relates it to the duration and the user who made the reservation
                    $starts[timetoInt($sched_tb_round)] = array(durationToInt($sched->timeblock_duration),$sched->reservations[0]->user,$sched);
                }
            }
        }

        $rows .= '<td class="daily-items">' . $item . '</td>';
        $endTime = 0;
        $starter = 0;

        /* draw all the time cells for a given piece of equipment */
        $userRes = NULL;
        for ($x = 1; $x < count($headings); $x++):
            $isStart = array_key_exists($x, $starts);


            ## If this is the starting box then...
            if ($isStart) {
                $dur = $starts[$x][0];
                $endTime = $dur + $x;

                //user who made the reservation
                $user = $starts[$x][1];
                $sched = $starts[$x][2];
                //store for later use
                $userRes = $user;

                //Store to use later with the end_percent array
                $starter = $x;

                //Round to the nearest 5th because it looks prettier (will be solid after 50% mark)
                $start_cell_perc = round($start_percent[$x] / 5) * 5;
                $ender = 100 - $start_cell_perc;

		// CSW TODO- branch on sched type (is mgt?) to use time-slot-in-use-mgt vs plain time-slot-in-use
		// CSW TODO- add mgt info to title attrib and label text

		$used_cell_class = 'time-slot-in-use';
		$used_cell_color = '#09f';
		$label_suffix = '';
		if ($sched->type == 'manager') {
		   $used_cell_class = 'time-slot-in-use-mgt';
		   $used_cell_color = '#f95';
		   $label_suffix = '&nbsp(mgt&nbsp;reservation)';
		}

                ## If the start percent is 100 (or the reservation starts on a quarter marker) then just fill in the box
                ## Else have to fill in according to the percentages
                if ($start_percent[$x] == 100) {
                    $rows .= '<td class="calendar-time '.$used_cell_class.'" title="' . $user->fname . " " . $user->lname . $label_suffix .'">';
                    $rows .= '<div class="slot-use-label"><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1">' . $user->fname . "&nbsp;" . $user->lname . $label_suffix .'</a></div>';
                    $rows .= '</td>';
                } else {
                    $rows .= '<td class="calendar-time"
                        style="background: -webkit-linear-gradient(left, #FFFFFF ' . $start_cell_perc . '%, '.$used_cell_color.' ' . $ender . '%);
                        background: -moz-linear-gradient(left, #FFFFFF ' . $start_cell_perc . '%, '.$used_cell_color.' ' . $ender . '%);
                        background: -o-linear-gradient(left, #FFFFFF ' . $start_cell_perc . '%, '.$used_cell_color.' ' . $ender . '%);
                        background: -ms-linear-gradient(left, #FFFFFF ' . $start_cell_perc . '%, '.$used_cell_color.' ' . $ender . '%);
                        background: linear-gradient(left, #FFFFFF ' . $start_cell_perc . '%, '.$used_cell_color.' ' . $ender . '%);"
                        title="' . $user->fname . " " . $user->lname . $label_suffix.'">';
                    $rows .= '<div class="slot-use-label"><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1">' . $user->fname . "&nbsp;" . $user->lname . $label_suffix .'</a></div>';
		    $rows .= '</td>';
                }

                ## If we have found the end box then...
            } else if ($x == $endTime) {
                //Round to the nearest 5th because it looks prettier (will be solid after 50% mark)
                $end_cell_perc = round($end_percent[$starter] / 5) * 5;
                $ender = 100 - $end_cell_perc;

                ## If the end percent is 100 (or the reservation ends on a quarter marker) then leave it blank
                ## Else have to fill according to the percentages
                if ($end_percent[$starter] == 100) {
                    $rows .= '<td class="calendar-time"></td>';
                } else {
                    $rows .= '<td class="calendar-time"
                        style="background: -webkit-linear-gradient(left, '.$used_cell_color.' ' . $end_cell_perc . '%, #FFFFFF ' . $ender . '%);
                        background: -moz-linear-gradient(left, '.$used_cell_color.' ' . $end_cell_perc . '%, #FFFFFF ' . $ender . '%);
                        background: -o-linear-gradient(left, '.$used_cell_color.' ' . $end_cell_perc . '%, #FFFFFF ' . $ender . '%);
                        background: -ms-linear-gradient(left, '.$used_cell_color.' ' . $end_cell_perc . '%, #FFFFFF ' . $ender . '%);
                        background: linear-gradient(left, '.$used_cell_color.' ' . $end_cell_perc . '%, #FFFFFF ' . $ender . '%);"
                        title="' . $userRes->fname . " " . $userRes->lname . $label_suffix .'"></td>';
                }

            } else if ($x < $endTime) {
                ## If we're in between start and end, then continue coloring
                $rows .= '<td class="calendar-time '.$used_cell_class.'" title="' . $userRes->fname . " " . $userRes->lname . $label_suffix.'"></td>';

            } else {
                ## If we have not yet found an item reservation for this time then leave the cell blank
                $rows .= '<td class="calendar-time"></td>';
            }
        endfor;
        next($items);
        $rows .= "</tr>";
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

    $current_year = date('Y');
    $current_month = date('m');
    $current_day = date('d');

//    echo "|||$current_year - $current_month - $current_day|||";

    for($list_day = 1; $list_day <= $days_in_month; $list_day++):
        if(strlen($list_day)<2){
            $list_day = '0'.$list_day;
        }

	$cell_classes = "calendar-day";
	if ($year < $current_year)
	{
	   $cell_classes .= " day-in-past";
	} elseif (($year == $current_year) && ($month < $current_month))
	{
	   $cell_classes .= " day-in-past";
	} elseif (($year == $current_year) && ($month == $current_month) && ($list_day < $current_day))
	{
	   $cell_classes .= " day-in-past";
	} elseif (($year == $current_year) && ($month == $current_month) && ($list_day == $current_day))
	{
	   $cell_classes .= " day-today";
	}
	
	$cell_title = date('l, F j Y', mktime(10, 10, 10, $month, $list_day, $year));
	$cell_id = 'day_lists_'.$year.'_'.$month.'_'.$list_day;
        $cells .='<td id = "'.$cell_id.'" class="'.$cell_classes.'" title="'.$cell_title.'" data-monthnum = "'.$month.'" data-daynum = "'.$list_day.'">';
        /* add in the day number */
        $cells.= '<div class="day-number calendar_header_format">'.$list_day.'</div>';

        $cells .= '<div class = "all-items">';
        /* add in items here */
        $num_schedules = 0;
        foreach($schedule as $sched) {
	    $sched_block_class = 'monthly-items';
	    $mgr_label = '';
//	    util_prePrintR($sched); exit; // DEBUG
	    if ($sched->type == 'manager') {
	       $sched_block_class = 'monthly-items-mgr';
	       $mgr_label = '(MGT) ';
	    }
            foreach ($sched->time_blocks as $tb) {
                foreach ($sched->reservations as $r) {
                    //Makes sure that schedules that run for days/weeks/years are shown
                    if (strtotime($tb->start_datetime) <= strtotime($year . '-' . $month . '-' . $list_day . ' 23:59:59') && strtotime($year . '-' . $month . '-' . $list_day) <= strtotime($tb->end_datetime)) {
                        if ($num_schedules > 2) {
                            $num_schedules++;
                            break;
                        } elseif ((strtotime(substr($tb->start_datetime, 0, 10)) == strtotime($year . '-' . $month . '-' . $list_day)) && (strtotime(substr($tb->end_datetime, 0, 10)) == strtotime($year . '-' . $month . '-' . $list_day))) {
			    if ($r->eq_item->name) { 
                           $cells .= '<p class="'.$sched_block_class.'" id="schedule-' . $sched->schedule_id . '" start-date="' . $sched->start_on_date . '"
                start-time="' . $sched->timeblock_start_time . '" duration="' . $sched->timeblock_duration . '">' . $mgr_label.$tb->toStringShort() .
                                '<br>' . $r->eq_item->eq_subgroup->name . ':<br>' . $r->eq_item->name . '</p>';
                            $num_schedules++;
			    }
                        } elseif (strtotime(substr($tb->start_datetime, 0, 10)) == strtotime($year . '-' . $month . '-' . $list_day)) {
			    if ($r->eq_item->name) {
                            $cells .= '<p class="'.$sched_block_class.'" id="schedule-' . $sched->schedule_id . '" start-date="' . $sched->start_on_date . '"
                start-time="' . $sched->timeblock_start_time . '" duration="' . $sched->timeblock_duration . '">' . $mgr_label.$tb->toStringStart() .
                                '<br>' . $r->eq_item->eq_subgroup->name . ':<br>' . $r->eq_item->name . '</p>';
                            $num_schedules++;
			    }
                        } elseif (strtotime(substr($tb->end_datetime, 0, 10)) == strtotime($year . '-' . $month . '-' . $list_day)) {
			    if ($r->eq_item->name) {
                            $cells .= '<p data-findit="findthisthing" class="'.$sched_block_class.'" id="schedule-' . $sched->schedule_id . '" start-date="' . $sched->start_on_date . '"
                start-time="' . $sched->timeblock_start_time . '" duration="' . $sched->timeblock_duration . '">' . $mgr_label.$tb->toStringEnd() .
                                '<br>' . $r->eq_item->eq_subgroup->name . ':<br>' . $r->eq_item->name . '</p>';

                            $num_schedules++;
}
                        } else {
                            $cells .= '<p class="'.$sched_block_class.'" id="schedule-' . $sched->schedule_id . '" start-date="' . $sched->start_on_date . '"
                start-time="' . $sched->timeblock_start_time . '" duration="' . $sched->timeblock_duration . '"> '.$mgr_label.'All Day <br>' . $r->eq_item->eq_subgroup->name . ':<br>' . $r->eq_item->name . '</p>';
                            $num_schedules++;
                        }
                    }
                }
            }
        }

        //prints message if more than 3 reservations for the day
        if($num_schedules>3){
            $cells .= '<p style="font-size: small; text-align: center">Click to view more reservations</p>';
        }

        $cells .= '</div>';

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

//****************************************************************************
//****************** Draw actual calendars  **********************************
//****************************************************************************

/*************** MONTHLY CALENDAR *********************/
function draw_MonthlyCalendar($month,$year,$all_schedules) {
    /* render header for the monthly calendar */
    $header = '<table cellpadding="0" cellspacing="0" class="calendar">';
    $header .= renderMonthHeader($month, $year);

    /* draw table */
    $headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    $calendar_days = '<tr class="calendar-row"><td class="calendar-day-head calendar_header_format">'.implode('</td><td class="calendar-day-head calendar_header_format">',$headings).'</td></tr>';

    /* make the cells with days and items */
    $calendar_cells = renderCalendarCells($month, $year, $all_schedules);

	/* end the table */
	$end = '</table>';

	/* all done, return result */
	return $header.$calendar_days.$calendar_cells.$end;
}

/*************** DAILY CALENDAR *********************/
function draw_SingleDayCalendar($month,$day,$year,$items,$day_sched) {
    /* date header */
    if (strrpos($day,'0', -strlen($day)) !== FALSE) {
        $day = substr($day, 1);
    }

    $header = renderDayHeader($month,$day,$year);

    /* table headings */
    $calendar = '<div style="overflow-x:scroll; width:925px"><table cellpadding="0" cellspacing="2" class="header">';


    $headings = array('','12:00 AM', '12:15 AM', '12:30 AM', '12:45 AM', '1:00 AM','1:15 AM', '1:30 AM', '1:45 AM', '2:00 AM', '2:15 AM', '2:30 AM', '2:45 AM', '3:00 AM',
        '3:15 AM', '3:30 AM', '3:45 AM', '4:00 AM', '4:15 AM', '4:30 AM', '4:45 AM', '5:00 AM', '5:15 AM', '5:30 AM', '5:45 AM', '6:00 AM', '6:15 AM', '6:30 AM',
        '6:45 AM', '7:00 AM', '7:15 AM', '7:30 AM', '7:45 AM', '8:00 AM', '8:15 AM', '8:30 AM', '8:45 AM', '9:00 AM', '9:15 AM', '9:30 AM', '9:45 AM', '10:00 AM',
        '10:15 AM', '10:30 AM', '10:45 AM', '11:00 AM', '11:15 AM', '11:30 AM', '11:45 AM', '12:00 PM', '12:15 PM', '12:30 PM', '12:45 PM', '1:00 PM', '1:15 PM',
        '1:30 PM', '1:45 PM', '2:00 PM', '2:15 PM', '2:30 PM', '2:45 PM', '3:00 PM', '3:15 PM', '3:30 PM', '3:45 PM', '4:00 PM', '4:15 PM', '4:30 PM', '4:45 PM',
        '5:00 PM', '5:15 PM', '5:30 PM', '5:45 PM', '6:00 PM', '6:15 PM', '6:30 PM', '6:45 PM', '7:00 PM', '7:15 PM', '7:30 PM', '7:45 PM', '8:00 PM', '8:15 PM',
        '8:30 PM', '8:45 PM', '9:00 PM', '9:15 PM', '9:30 PM', '9:45 PM', '10:00 PM', '10:15 PM', '10:30 PM', '10:45 PM', '11:00 PM', '11:15 PM', '11:30 PM', '11:45 PM');

	$headings = array_map(function($e){return substr($e,0,-1);},$headings);

    $calendar .= '<td class="calendar-day-head calendar-day-time-head calendar_header_format">'.implode('</td><td class="calendar-day-head calendar-day-time-head calendar_header_format">',$headings).'</td></tr>';

    /* draw the calendar for all pieces of equipment in each subgroup */
    $calendar .= renderItemRows($items,$headings,$day_sched,$month,$day,$year);

    $calendar .= '</table></div>';

//    $calendar .= '<button class="show_month_button">Go Back To Monthly View</button>';

    return $header.$calendar;
}
