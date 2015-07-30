<div class="class=" form-horizontal">

<legend class="pull-left row-fluid">Existing Reservations</legend><br clear="all" />
<!--<ul id="reservationSchedules" class="unstyled">-->
<!--    $show_del_control = ($USER->flag_is_system_admin || $is_group_manager);-->
<!--    if (count($Requested_EqGroup->schedules) > 0) {-->
<!--        $allCount = count($Requested_EqGroup->schedules);-->
<!--        $thisMonthCount = 0;-->
<!--        foreach ($Requested_EqGroup->schedules as $sched) {-->
<!--            $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);-->
<!--            //this toggles weirdly-->
<!--            if ($show_del_control) {-->
<!--                $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';-->
<!--            }-->
<!--			if ((substr($sched->start_on_date, 0, 4) == util_getCurrentYearNum()) && (substr($sched->start_on_date, 5, 2) == util_getCurrentMonthNum())) {-->
<!--                if ($sched->type == 'manager') {-->
<!--                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';-->
<!--                }-->
<!--                if ($sched->user_id == $USER->user_id) {-->
<!--                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';-->
<!--                } else {-->
<!--                    $sched->loadUser();-->
<!--                    $li .= '<strong>' . $sched->toString() . '</strong> by ';-->
<!---->
<!--                    if (!$sched->user->matchesDb) {-->
<!--                        $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);-->
<!--                        if (!$del_user->matchesDb) {-->
<!--                            $li .= '<i>could not determine the user</i> ';-->
<!--                        } else {-->
<!--                            $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';-->
<!--                        }-->
<!--                    } else {-->
<!--    //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';-->
<!--                        $li .= $sched->user->renderRich();-->
<!--                    }-->
<!--                }-->
<!--                $li .= "<ul class=\"unstyled\">\n";-->
<!--                foreach ($sched->reservations as $r) {-->
<!--    //                    util_prePrintR($sched);-->
<!--                    $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";-->
<!--                }-->
<!--                $li .= "</ul></li>\n";-->
<!--                echo $li;-->
<!--			}else{-->
<!--				$thisMonthCount++;-->
<!--            }-->
<!--        }-->
<!--        if ($thisMonthCount == $allCount) {-->
<!--            echo "<li class=\"no-reserv\">Nothing reserved for this month</li><br>";-->
<!--        }-->
<!--        echo "<div id='show-reservations-buttons'>";-->
<!--        echo '<a href="#" class="show-this-year btn btn-medium btn-primary" data-show-this="1">Show Reservations for This Year</a>';-->
<!--        echo " ";-->
<!--        echo '<a href="#" class="show-all btn btn-medium btn-primary" data-show-all="2">Show All Reservations</a>';-->
<!--    } else {-->
<!--        echo "<li>There is nothing reserved.</li>";-->
<!--    }-->
<!---->
<!--</ul>-->
<!--</div>-->

<ul id="reservationSchedules" class="unstyled">
    <?php
    $show_del_control = ($USER->flag_is_system_admin || $is_group_manager);
    if (count($Requested_EqGroup->schedules) > 0) {
        $allCount = count($Requested_EqGroup->schedules);
        $thisMonthCount = 0;
        foreach ($Requested_EqGroup->schedules as $sched) {
			$li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
			//this toggles weirdly
			if ($show_del_control) {
				$li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
			}
			$year = substr($sched->start_on_date, 0, 4);
			$month = substr($sched->start_on_date, 5, 2);
			$thisSched = $sched->schedule_id;
			if ((substr($sched->start_on_date, 0, 4) == util_getCurrentYearNum()) && (substr($sched->start_on_date, 5, 2) == util_getCurrentMonthNum())) {
				$li .= "<span id=\"item\" class=\"item-listing\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
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
			}
		}
    //else{
//                $li .= "<span id=\"item\" class=\"item-listing hide\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
//                if ($sched->type == 'manager') {
//                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
//                }
//                if ($sched->user_id == $USER->user_id) {
//                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
//                } else {
//                    $sched->loadUser();
//                    $li .= '<strong>' . $sched->toString() . '</strong> by ';
//
//                    if (!$sched->user->matchesDb) {
//                        $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
//                        if (!$del_user->matchesDb) {
//                            $li .= '<i>could not determine the user</i> ';
//                        } else {
//                            $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
//                        }
//                    } else {
//                        //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
//                        $li .= $sched->user->renderRich();
//                    }
//                }
//                $li .= "<ul class=\"unstyled\">\n";
//                foreach ($sched->reservations as $r) {
//                    //                    util_prePrintR($sched);
//                    $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
//                }
//                $li .= "</ul></li></span>\n";
//                echo $li;
//
//                $thisMonthCount++;
//            }
//        }
//        if ($thisMonthCount == $allCount) {
//            echo "<li class=\"no-reserv\">Nothing reserved for this month</li><br>";
//        }
//        $thisMonth = util_getCurrentMonthNum();
//        $thisYear = util_getCurrentYearNum();
//        echo "<div id='show-reservations-buttons'>";
//        echo "<a href=\"#\" class=\"show-this-month hide btn btn-medium btn-primary\" data-show-month=\"0\" data-thisMonth=\"$thisMonth\" data-thisYear=\"$thisYear\">Show Reservations for This Month</a>";
//        echo " ";
//        echo "<a href=\"#\" class=\"show-this-year btn btn-medium btn-primary\" data-show-this=\"1\" data-thisYear=\"$thisYear\">Show Reservations for This Year</a>";
//        echo " ";
//        echo "<a href=\"#\" class=\"show-all btn btn-medium btn-primary\" data-show-all=\"2\">Show All Reservations</a>";
    } else {
        echo "<li>There is nothing reserved.</li>";
    }

    ?>
</ul>
</div>

<!--calendar for displaying schedule-->
<div>
    <div id="monthly_calendar_view">
		<?php
        $month = util_getCurrentMonthNum();
        $year = util_getCurrentYearNum();

        echo draw_MonthlyCalendar($month, $year,$Requested_EqGroup->schedules);

        ?>
    </div>

    <div id="daily_calendar_view">

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
