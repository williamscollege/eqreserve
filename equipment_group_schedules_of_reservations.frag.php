<div class="class=" form-horizontal">

<legend class="pull-left row-fluid">Existing Reservations</legend><br clear="all" />
<ul id="reservationSchedules" class="unstyled">
	<?php
		$show_del_control = ($USER->flag_is_system_admin || $is_group_manager);

		if (count($Requested_EqGroup->schedules) > 0) {
			foreach ($Requested_EqGroup->schedules as $sched) {

				$li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
				if ($show_del_control) {
					$li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
				}

				if ($sched->type == 'manager') {
					$li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
				}
				if ($sched->user_id == $USER->user_id) {
					$li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
				}
				else {
					$sched->loadUser();
					$li .= '<strong>' . $sched->toString() . '</strong> by ';

					if (!$sched->user->matchesDb) {
						$del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
                        if (!$del_user->matchesDb) {
                            $li .= '<i>could not determine the user</i> ';
                        } else {
    						$li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
                        }
					}
					else {
//                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
                        $li .= $sched->user->renderRich();
					}
				}
				$li .= "<ul class=\"unstyled\">\n";
				foreach ($sched->reservations as $r) {
					$li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
				}
				$li .= "</ul></li>\n";

				echo $li;
			}
		}
		else {
			echo "<li>There is nothing reserved.</li>";
		}
	?>
</ul>
</div>

<!--calendar for displaying schedule-->
<div>
    <div id="monthly_calendar_view" >
        <?php
            $month = '7';
            $year = '2015';
            echo draw_MonthlyCalendar($month, $year);
        ?>
    </div>

    <div id="daily_calendar_view" style = "display:none">
        <?php
            $items = array("things","other things","more things");
            echo draw_SingleDayCalendar(7,2015,5,$items);
        ?>
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
