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

    $show_del_control_admin = htmlentities((isset($_REQUEST["show_del_control_admin"])) ? util_quoteSmart($_REQUEST["show_del_control_admin"]) : 0);
    $show_del_control_manager = htmlentities((isset($_REQUEST["show_del_control_manager"])) ? util_quoteSmart($_REQUEST["show_del_control_manager"]) : 0);

    #------------------------------------------------#
    # Fetch AJAX values for month to month views
    # NOTE: condition ? when_true : when_false
    #------------------------------------------------#
    //Requested eqgroup id to use to get the schedule
    $eq_group_id = htmlentities((isset($_REQUEST["eq_group_id"])) ? util_quoteSmart($_REQUEST["eq_group_id"]) : 0);
    $Eq_Group = EqGroup::getOneFromDb(['eq_group_id' => $eq_group_id], $DB);

    //Check eq_group_id values
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
    util_prePrintR($year);

    #------------------------------------------------#
    # Fetch AJAX values for month to day views
    #------------------------------------------------#
    $baseDay = htmlentities((isset($_REQUEST["day_num"])) ? util_quoteSmart($_REQUEST["day_num"]) : 0);
    //pass in schedules (array)


    #------------------------------------------------#
    # Carry out corresponding actions for views
    #------------------------------------------------#

    if(($baseDay!=0)){
        $baseDay = (int)$baseDay + (int)$next + (int)$prev;

        //skips to the next or previous month
        if($baseDay<1){
            $baseMonth=$baseMonth-1;
            if($baseMonth<1){
                $year = $year - 1;
                $baseMonth = 12;
            }
            $baseDay = date('t', mktime(0, 0, 0, $baseMonth, 1, $year));
        }else if($baseDay>date('t', mktime(0, 0, 0, $baseMonth, 1, $year))){
            $baseMonth = $baseMonth + 1;
            if($baseMonth>12){
                $year = $year + 1;
                $baseMonth = 1;
            }
            $baseDay = 1;
        }

        //change day to dd form
        if (strlen($baseDay) < 2) {
            $baseDay = '0' . $baseDay;
        }

        if (strlen($baseMonth) < 2) {
            $baseMonth = '0' . $baseMonth;
        }

        $clickedDate_yyyymmdd = $year . '-' . $baseMonth . '-' . $baseDay;

        $Eq_Group->loadEqItems();

        //util_prePrintR(array_map(function($elt){return $elt->name;},$Requested_EqGroup->eq_items));

        //util_prePrintR(array_map(function($elt){return $elt->schedule_id;},$Requested_EqGroup->schedules));

        //stores schedules on this day
        $day_sched = [];
        $items = [];
        $reserved_item_names = [];
        $reservations = [];

        //load all the schedules using time blocks to account for repeated reservations for the clicked day
        foreach($Eq_Group->schedules as $sched){
            foreach($sched->time_blocks as $tb){
//                util_prePrintR($sched);
//                util_prePrintR($tb);
                util_prePrintR($clickedDate_yyyymmdd);
                if((substr($tb->start_datetime,0,10)==$clickedDate_yyyymmdd)||(substr($tb->end_datetime,0,10)==$clickedDate_yyyymmdd)){
                    array_push($day_sched,$sched);
                }
            }
        }
        //gets every item of the eq group and ensures uniqueness
        //same item different subgroup should still be a different item
        foreach($Eq_Group->eq_subgroups as $subgroup) {
            foreach ($subgroup->eq_items as $item) {
                $id = $item->eq_item_id;
                $name = $subgroup->name . ": " . $item->name;
                $items[$id] = $name;
            }
        }
        util_prePrintR($day_sched);

        //draw appropriate calendar
        echo draw_SingleDayCalendar($baseMonth, $baseDay, $year, $items, $day_sched);
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
?>