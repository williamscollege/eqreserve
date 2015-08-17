<div class="class=" form-horizontal">

<legend class="pull-left row-fluid">Existing Reservations
    <span id = "reservations_view">
        <a href="#" class="show_reservation_list btn btn-medium btn-primary">List View</a>
        <a href="#" class="show_reservation_calendar btn btn-medium btn-primary">Monthly Calendar View</a>
    </span>
</legend>
<br clear="all" />

<ul id="reservationSchedules" class="schedule unstyled hide">
    <?php
    $show_del_control = ($USER->flag_is_system_admin || $is_group_manager);
    if (count($Requested_EqGroup->schedules) > 0) {
        $allCount = count($Requested_EqGroup->schedules);
        $thisMonthCount = 0;
        foreach ($Requested_EqGroup->schedules as $sched) {

            $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);

            //If want to show the delete button
            if ($show_del_control) {
                $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
            }

            $year = substr($sched->start_on_date, 0, 4);
            $month = substr($sched->start_on_date, 5, 2);
            $thisSched = $sched->schedule_id;

            //Default: only list the reservations in the current year and month
            if ($year == util_getCurrentYearNum() && $month == util_getCurrentMonthNum()) {
                $li .= "<span id=\"item\" class=\"item-listing\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";

                if ($sched->type == 'manager') {
                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
                }

                //shorten the shown reservation if it is a repeated schedule
                if ($sched->frequency_type == 'monthly' || $sched->frequency_type == 'weekly') {
                    $output = $sched->summary;
                } else {
                    $output = $sched->toString();
                }

                //if this reservation is by the signed in user then schedule is reserved "by you" else show username of the reserver
                if ($sched->user_id == $USER->user_id) {
                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $output . '</a></strong> by you<br/>';
                } else {
                    $sched->loadUser();
                    $li .= '<strong>' . $output . '</strong> by ';

                    if (!$sched->user->matchesDb) {
                        $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
                        if (!$del_user->matchesDb) {
                            $li .= '<i>could not determine the user</i> ';
                        } else {
                            $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
                        }
                    } else {
                        //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
                        //Allows hover
                        $li .= $sched->user->renderRich();
                    }
                }
                $li .= "<ul class=\"unstyled\">\n";

                //Print out the corresponding reservations
                foreach ($sched->reservations as $r) {
                    $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
                }
                $li .= "</ul></li></span>\n";
                echo $li;
            }else{
                $li .= "<span id=\"item\" class=\"item-listing hide\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
                if ($sched->type == 'manager') {
                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
                }
                if ($sched->user_id == $USER->user_id) {
                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
                } else {
                    $sched->loadUser();
                    $li .= '<strong>' . $sched->toString() . '</strong> by ';

                    if (!$sched->user->matchesDb) {
                        $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
                        if (!$del_user->matchesDb) {
                            $li .= '<i>could not determine the user</i> ';
                        } else {
                            $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
                        }
                    } else {
                        //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
                        $li .= $sched->user->renderRich();
                    }
                }
                $li .= "<ul class=\"unstyled\">\n";
                foreach ($sched->reservations as $r) {
                    //                    util_prePrintR($sched);
                    $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
                }
                $li .= "</ul></li></span>\n";
                echo $li;

                $thisMonthCount++;
            }
        }

        if ($thisMonthCount == $allCount) {
            echo "<li class=\"no-reserv-month\">Nothing reserved for this month</li><br>";
        }
        echo "<li class=\"no-reserv-year hide\">Nothing reserved for this year</li><br>";
        echo "<li class=\"none-at-all hide\">There is nothing reserved.</li>";

        $thisMonth = util_getCurrentMonthNum();
        $thisYear = util_getCurrentYearNum();

        //Buttons (Default: show the all and year buttons since list view of month is on display)
        //Show all reservations, show reservations for this month, show reservations for this year
        echo "<div id='show-reservations-buttons'>";
        echo "<a href=\"#\" class=\"show-this-month hide btn btn-medium btn-primary\" data-show-month=\"0\" data-thisMonth=\"$thisMonth\" data-thisYear=\"$thisYear\">Show Reservations for This Month</a>";
        echo " ";
        echo "<a href=\"#\" class=\"show-this-year btn btn-medium btn-primary\" data-show-this=\"1\" data-thisYear=\"$thisYear\">Show Reservations for This Year</a>";
        echo " ";
        echo "<a href=\"#\" class=\"show-all btn btn-medium btn-primary\" data-show-all=\"2\">Show All Reservations</a>";


    } else {
        echo "<li>There is nothing reserved.</li>";
    }

    ?>
</ul>

<!--calendar for displaying schedule-->
<div>
    <div id="monthly_calendar_view" class="calendar">
		<?php
        $month = util_getCurrentMonthNum();
        $year = util_getCurrentYearNum();

        echo draw_MonthlyCalendar($month, $year,$Requested_EqGroup->schedules);

        ?>
    </div>

    <div id="daily_calendar_view" class="calendar_day hide">

    </div>
    <?php

//    <div id="daily_view_header">
//            <div id="show_month_button"  style="width: 50px; height; 50px; border: 2px solid red">show month</div>
//            <div class="calendar_header_text">The Current Day</div>
//            <div class="daily_nav_elements">
//                <div class="nav_elt_day_prev">&lt;</div>
//                <div class="nav_elt_day_next">&gt;</div>
//            </div>
//        </div>
////</div>
//<br>
//<div>
//<!--    should draw only when someone clicks-->
//<!--    -->
//<!--//    $items = array("things","other things","more things");-->
//<!--//    echo draw_SingleDayCalendar(7,2015,10,$items);-->
//<!--//    -->
//</div>

//    echo "<script>";
//    echo "$(document).ready(function(){";
//    echo "$('calendar-day').click(function(){";
//    echo "$items = array('things','other things','more things');";
//    echo "draw_SingleDayCalendar(7,2015,5,$items);";
//    echo "$('monthly-calendar').hide();";
//    echo "});";
//    echo "});";
?>

</div>
