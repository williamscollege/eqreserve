<?php
    require_once('head_ajax.php');
    require_once('../calendar_util.php');
    require_once('../classes/eq_group.class.php');
    //require_once('../head_pre_output.php'); //DEV


    //Debugging purposes
    //util_prePrintR($_REQUEST);

    #------------------------------------------------#
    # Fetch AJAX values for reservation views
    #------------------------------------------------#
    $show_all = htmlentities((isset($_REQUEST["show_all"])) ? util_quoteSmart($_REQUEST["show_all"]) : 0);
    $show_this = htmlentities((isset($_REQUEST["show_this"])) ? util_quoteSmart($_REQUEST["show_this"]) : 0);
    $show_month = htmlentities((isset($_REQUEST["show_month"])) ? util_quoteSmart($_REQUEST["show_month"]) : 0);

    //causes weird toggling (del button will show even if unclick edit equipment group)
    $show_del_control_admin = htmlentities((isset($_REQUEST["show_del_control_admin"])) ? util_quoteSmart($_REQUEST["show_del_control_admin"]) : 0);
    $show_del_control_manager = htmlentities((isset($_REQUEST["show_del_control_manager"])) ? util_quoteSmart($_REQUEST["show_del_control_manager"]) : 0);

    #------------------------------------------------#
    # Fetch AJAX values for month to month views
    # NOTE: condition ? when_true : when_false
    #------------------------------------------------#
    //Requested eqgroup id to use to get the schedule
    $eq_group_id = htmlentities((isset($_REQUEST["eq_group_id"])) ? util_quoteSmart($_REQUEST["eq_group_id"]) : 0);
    $Eq_Group = EqGroup::getOneFromDb(['eq_group_id' => $eq_group_id], $DB);

    if(strval($eq_group_id)=='0'){
        $result['notes'] = 'Missing equipment group ID';
        echo json_encode($result);
        exit;
    }elseif(intval($eq_group_id)>0){
        if(!($Eq_Group->matchesDb)){
            $result['notes'] = 'Equipment group does not exist';
            echo json_encode($result);
            exit;
        }
    }else{
        $result['notes'] = 'Invalid equipment group ID';
        echo json_encode($result);
        exit;
    }

    $Eq_Group->loadSchedules();

    //current month
    $baseMonth = htmlentities((isset($_REQUEST["month_num"])) ? util_quoteSmart($_REQUEST["month_num"]) : 0);
    //set at either -1 or 0
    $prev = htmlentities((isset($_REQUEST["prev"])) ? util_quoteSmart($_REQUEST["prev"]) : 0);
    //set at either 1 or 0
    $next = htmlentities((isset($_REQUEST["next"])) ? util_quoteSmart($_REQUEST["next"]) : 0);
    $year = htmlentities((isset($_REQUEST["year_num"])) ? util_quoteSmart($_REQUEST["year_num"]) : 0);

    #------------------------------------------------#
    # Fetch AJAX values for month to day views
    #------------------------------------------------#
    $clickedDay = htmlentities((isset($_REQUEST["caldate"])) ? util_quoteSmart($_REQUEST["caldate"]) : 0);
    $clickedMonth = htmlentities((isset($_REQUEST["calmonth"])) ? util_quoteSmart($_REQUEST["calmonth"]) : 0);
    //pass in schedules (array)

    //change day to dd form
    if (strlen($clickedDay) < 2) {
        $clickedDay = '0' . $clickedDay;
    }
    $clickedDate_yyyymmdd = $year . '-' . $clickedMonth . '-' . $clickedDay;

    #------------------------------------------------#
    # Carry out corresponding actions for views
    #------------------------------------------------#

    if($clickedDay!=0){

        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => $eq_group_id], $DB);
        $Requested_EqGroup->loadSchedules();
        $Requested_EqGroup->loadEqItems();

        //util_prePrintR(array_map(function($elt){return $elt->name;},$Requested_EqGroup->eq_items));

        //util_prePrintR(array_map(function($elt){return $elt->schedule_id;},$Requested_EqGroup->schedules));

        //stores schedules on this day
        $day_sched = [];
        $items = [];
        $reserved_item_names = [];
        $reservations = [];
        //load all the schedules for the clicked day
        foreach ($Requested_EqGroup->schedules as $sched) {
            $sched->loadReservations();
            if ( ($sched->start_on_date == $clickedDate_yyyymmdd) || ($sched->end_on_date == $clickedDate_yyyymmdd) ) {
                array_push($day_sched, $sched);
            }
        }
        //gets every item of the eq group and ensures uniqueness
        foreach ($Requested_EqGroup->eq_items as $item) {
            $reserved_item_names[$item->name] = 1;
        }
        $items = array_keys($reserved_item_names);

        //draw appropriate calendar
        echo draw_SingleDayCalendar($clickedMonth, $clickedDay, $items, $day_sched);
    }elseif($baseMonth!=0){
        //use util functions to find prev or next month?
        //next or previous month
        $month = (int)$baseMonth + (int)$next + (int)$prev;

        //skips to the next or previous year
        if($month<1){
            $year = $year - 1;
            $month = 12;
        }else if($month>12){
            $year = $year + 1;
            $month = 1;
        }
        //draws appropriate calendar
        echo draw_MonthlyCalendar($month, $year, $Eq_Group->schedules);
    }
    //
//    /********* SIMPLIFICATION WOULD BE NICE ***********/
//    //if show all the reservations
//    if(intval($show_all)==2){
//        if(count($Eq_Group->schedules) > 0) {
//            foreach ($Eq_Group->schedules as $sched) {
//                $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
//                if (intval($show_del_control_admin) || intval($show_del_control_manager)) {
//                    $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
//                }
//                if ($sched->type == 'manager') {
//                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
//                }
//                if ($sched->user_id == $USER->user_id) {
//                    $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
//                }
//                else {
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
//                    }
//                    else {
//                        //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
//                        $li .= $sched->user->renderRich();
//                    }
//                }
//                $li .= "<ul class=\"unstyled\">\n";
//                foreach ($sched->reservations as $r) {
//                    //                    util_prePrintR($sched);
//                    $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
//                }
//                $li .= "</ul></li>\n";
//
//                echo $li;
//            }
//            echo "<div id='show-reservations-buttons'>";
//            echo '<a href="#" class="show-this-month btn btn-medium btn-primary" data-show-month="0">Show Reservations for This Month</a>';
//            echo " ";
//            echo '<a href="#" class="show-this-year btn btn-medium btn-primary" data-show-this="1">Show Reservations for This Year</a>';
//            echo "</div>";
//        }
//        else {
//            echo "<li>There is nothing reserved.</li>";
//        }
//        //else if show this year
//    }elseif((intval($show_this))==1){
//        if(count($Eq_Group->schedules) > 0) {
//            $allCount = count($Eq_Group->schedules);
//            $thisYearCount = 0;
//            foreach ($Eq_Group->schedules as $sched) {
//                $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
//                if ($show_del_control) {
//                    $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
//                }
//                if((substr($sched->start_on_date,0,4)==util_getCurrentYearNum())){
//                    if ($sched->type == 'manager') {
//                        $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
//                    }
//                    if ($sched->user_id == $USER->user_id) {
//                        $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
//                    }
//                    else {
//                        $sched->loadUser();
//                        $li .= '<strong>' . $sched->toString() . '</strong> by ';
//
//                        if (!$sched->user->matchesDb) {
//                            $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
//                            if (!$del_user->matchesDb) {
//                                $li .= '<i>could not determine the user</i> ';
//                            } else {
//                                $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
//                            }
//                        }
//                        else {
//    //                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
//                            $li .= $sched->user->renderRich();
//                        }
//                    }
//                    $li .= "<ul class=\"unstyled\">\n";
//                    foreach ($sched->reservations as $r) {
//    //                    util_prePrintR($sched);
//                        $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
//                    }
//                    $li .= "</ul></li>\n";
//
//                    echo $li;
//                }else{
//                    $thisYearCount++;
//                }
//            }
//            if($thisYearCount==$allCount){
//                echo "<li>Nothing reserved for this year</li><br>";
//            }
//            echo "<div id='show-reservations-buttons'>";
//            echo '<a href="#" class="show-this-month btn btn-medium btn-primary" data-show-month="0">Show Reservations for This Month</a>';
//            echo " ";
//            echo '<a href="#" class="show-all btn btn-medium btn-primary" data-show-all="2">Show All Reservations</a>';
//            echo "</div>";
//        }
//        //else if show this month
//    }elseif((intval($show_month)==0)) {
//        if (count($Eq_Group->schedules) > 0) {
//            $allCount = count($Eq_Group->schedules);
//            $thisMonthCount = 0;
//            foreach ($Eq_Group->schedules as $sched) {
//                $li = Db_Linked::listItemTag('list-of-schedule-' . $sched->schedule_id);
//                if ($show_del_control) {
//                    $li .= '<a id="delete-schedule-' . $sched->schedule_id . '" class="editing-control hide btn btn-mini btn-danger delete-schedule-btn" data-for-schedule="' . $sched->schedule_id . '"><i class="icon-trash icon-white"></i></a> ';
//                }
//                if ((substr($sched->start_on_date, 0, 4) == util_getCurrentYearNum()) && (substr($sched->start_on_date, 5, 2) == util_getCurrentMonthNum())) {
//                    if ($sched->type == 'manager') {
//                        $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
//                    }
//                    if ($sched->user_id == $USER->user_id) {
//                        $li .= '<strong><a href="schedule.php?schedule=' . $sched->schedule_id . '&returnToEqGroup=1"> ' . $sched->toString() . '</a></strong> by you<br/>';
//                    } else {
//                        $sched->loadUser();
//                        $li .= '<strong>' . $sched->toString() . '</strong> by ';
//
//                        if (!$sched->user->matchesDb) {
//                            $del_user = User::getOneFromDb(['user_id' => $sched->user_id, 'flag_delete' => true], $DB);
//                            if (!$del_user->matchesDb) {
//                                $li .= '<i>could not determine the user</i> ';
//                            } else {
//                                $li .= '<i>user removed from system: ' . $del_user->fname . ' ' . $del_user->lname . '</i> ';
//                            }
//                        } else {
////                        $li .= $sched->user->fname . ' ' . $sched->user->lname . '(TODO: add link/hover stuff)<br/>';
//                            $li .= $sched->user->renderRich();
//                        }
//                    }
//                    $li .= "<ul class=\"unstyled\">\n";
//                    foreach ($sched->reservations as $r) {
////                    util_prePrintR($sched);
//                        $li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
//                    }
//                    $li .= "</ul></li>\n";
//
//                    echo $li;
//                } else {
//                    $thisMonthCount++;
//                }
//            }
//            if ($thisMonthCount == $allCount) {
//                echo "<li>Nothing reserved for this month</li><br>";
//            }
//            echo "<div id='show-reservations-buttons'>";
//            echo '<a href="#" class="show-this-year btn btn-medium btn-primary" data-show-this="1">Show Reservations for This Year</a>';
//            echo " ";
//            echo '<a href="#" class="show-all btn btn-medium btn-primary" data-show-all="2">Show All Reservations</a>';
//            echo "</div>";
//        }
//    }
?>