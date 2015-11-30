<div id="existingReservationsContainer" class="form-horizontal">

<legend class="pull-left row-fluid">Existing Reservations
    <span id = "reservations_view">
        <a href="#" class="show_reservation_list btn btn-medium btn-primary">List View</a>
        <a href="#" class="show_reservation_calendar btn btn-medium btn-primary">Monthly Calendar View</a>
        <a href="#" class="show_reservation_report btn btn-medium btn-primary">Report</a>
    </span>

    <div id="calendar-legend">
     <b>legend:</b> <span id="mgt-reservation-legend-item" class="legend-box time-slot-in-use-mgt">manager reservation</span>  <span class="legend-box time-slot-in-use">user reservation</span>
    </div>
</legend>

<br clear="all" />
<ul id="reservationSchedules" class="schedule unstyled hide">
    <?php
    $schedule_transpose = [];

    $show_del_control = ($USER->flag_is_system_admin || $is_group_manager);
    if (count($Requested_EqGroup->schedules) > 0) {
        $allCount = count($Requested_EqGroup->schedules);
        $thisMonthCount = 0;
        foreach ($Requested_EqGroup->schedules as $sched) {

            $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
	    
	    // add metadata about schedule type
	    $li = substr($li,0,-1); // remove trailing '>'
	    if ($sched->type == 'manager') {
	       $li .= ' class="list-items-mgr">';
	    } else {
	       $li .= ' class="list-items-user">';
	    }

            //If want to show the delete button
            if ($show_del_control) {
                $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
            }

            $year = substr($sched->start_on_date, 0, 4);
            $month = substr($sched->start_on_date, 5, 2);
            $thisSched = $sched->schedule_id;

	    // NOTE: this structure currently has a lot of unnecessary code duplication (or, in some cases, insufficient duplication - the first section has stuff that really should be repeated in the second section). However, time crunch means that's not getting refactored right now.

            //Default: only list the reservations in the current year and month
            if ($year == util_getCurrentYearNum() && $month == util_getCurrentMonthNum()) {
                $li .= "<span id=\"item\" class=\"item-listing\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
	    } else {
                $li .= "<span id=\"item\" class=\"item-listing hide\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
                $thisMonthCount++;
	    }

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
		$user_label = 'you';
                if ($sched->user_id == $USER->user_id) {
                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $output . '</a></strong> by you<br/>';
                } else {
                    $sched->loadUser();
                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1">' . $output . '</a></strong> by ';

                    if (!$sched->user->matchesDb) {
                        $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
                        if (!$del_user->matchesDb) {
         		    $user_label = '<i>could not determine the user</i> ';
			    $li .= $user_label;
                        } else {
			    $user_label = '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
                            $li .= $user_label;
                        }
                    } else {
                        //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
                        //Allows hover
			$user_label = $sched->user->renderRich();
                        $li .= $user_label;
                    }
                }
                $li .= "<ul class=\"unstyled\">\n";

                //Print out the corresponding reservations
                foreach ($sched->reservations as $r) {
		    if ($r->eq_item->name) {
		        $item_label = $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name;
                        $li .= '<li>' . $item_label . "</li>\n";
			if (! array_key_exists($item_label,$schedule_transpose)) {
			   $schedule_transpose[$item_label] = [];
			}
	                if ($sched->type == 'manager') {
   	                  //$li .= ' class="list-items-mgr">';
			  array_push($schedule_transpose[$item_label],"<span class=\"list-items-mgr\">$output - $user_label</span>");
	    		} else {
			  array_push($schedule_transpose[$item_label],"$output - $user_label");
			}
		    }
                }
                $li .= "</ul></li></span>\n";
                echo $li;

        #     }else{
        #         $li .= "<span id=\"item\" class=\"item-listing hide\" data-sched=\"$thisSched\" data-year=\"$year\" data-month=\"$month\">";
        #         if ($sched->type == 'manager') {
        #             $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
        #         }

        #         //shorten the shown reservation if it is a repeated schedule
        #         if ($sched->frequency_type == 'monthly' || $sched->frequency_type == 'weekly') {
        #             $output = $sched->summary;
        #         } else {
        #             $output = $sched->toString();
        #         }

        #         if ($sched->user_id == $USER->user_id) {
        #             $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $output . '</a></strong> by you<br/>';
        #         } else {
        #             $sched->loadUser();
        #             $li .= '<strong>' . $output . '</strong> by ';

        #             if (!$sched->user->matchesDb) {
        #                 $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
        #                 if (!$del_user->matchesDb) {
        #                     $li .= '<i>could not determine the user</i> ';
        #                 } else {
        #                     $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
        #                 }
        #             } else {
        #                 //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
        #                 $li .= $sched->user->renderRich();
        #             }
        #         }
        #         $li .= "<ul class=\"unstyled\">\n";
        #         foreach ($sched->reservations as $r) {
        #             //                    util_prePrintR($sched);
	# 	    if ($r->eq_item->name) {
        #                 $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
        #             }
        #         }
        #         $li .= "</ul></li></span>\n";
        #         echo $li;

        #         $thisMonthCount++;
        #     }
        }

	echo "<!--";
	util_prePrintR($schedule_transpose);
	echo "-->";


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

<ul id="reservations-report" class="report hide">
<?php
if (count($schedule_transpose) < 1) {
   echo "  <li>No reservations for any equipment in this group</li>\n";
} else {
  ksort($schedule_transpose);
  foreach ($schedule_transpose as $item_label => $res_ar) {
    echo "  <li>$item_label";
    echo "    <ul>\n";
    $recent_first_res = array_reverse($res_ar);
    foreach ($recent_first_res as $res_elt) {
      echo "      <li>$res_elt</li>\n";
    }
    echo "    </ul>\n";
    echo "  </li>\n";
  }
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

</div>
