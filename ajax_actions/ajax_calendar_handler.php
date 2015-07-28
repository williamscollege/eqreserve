<?php
    require_once('head_ajax.php');
    require_once('../calendar_util.php');
    require_once('../classes/eq_group.class.php');
    //require_once('../head_pre_output.php'); //DEV


    //Debugging purposes
    //util_prePrintR($_REQUEST);

    #------------------------------------------------#
    # Fetch AJAX values for month to month views
    # NOTE: condition ? when_true : when_false
    #------------------------------------------------#
    $eq_group_id = htmlentities((isset($_REQUEST["eq_group_id"])) ? util_quoteSmart($_REQUEST["eq_group_id"]) : 0);
// TO TEST: handler behavior when no eq group id given
// TO TEST: when bad eq group id given (version 1: none given; veriosn 2 bad data (e.g. 'a') version 3 valid syntax (e.g. 435) but group doesn't exist)
// TO TEST: when eq group has nothing scheduled
// TO TEST: when eq group has something scheduled, but for a time range not covered by the params passed in
// TO TEST: when eq group has something scheduled within the time range passed in

    //current month
    $baseMonth = htmlentities((isset($_REQUEST["month_num"])) ? util_quoteSmart($_REQUEST["month_num"]) : 0);
    //set at either -1 or 0
    $prev = htmlentities((isset($_REQUEST["prev"])) ? util_quoteSmart($_REQUEST["prev"]) : 0);
    //set at either 1 or 0
    $next = htmlentities((isset($_REQUEST["next"])) ? util_quoteSmart($_REQUEST["next"]) : 0);
    $year = htmlentities((isset($_REQUEST["year_num"])) ? util_quoteSmart($_REQUEST["year_num"]) : 0);

    //will have to fetch from the database here
    //In order to get items, should send in the schedule to get the items
    //******* working on it ******* //

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

    //fetched schedule should stay the same... do not have to find it every time we change months
//    if (count($Requested_EqGroup->schedules) > 0) {
//        foreach ($Requested_EqGroup->schedules as $sched) {
//            $all_sched[] = $sched;
//        }
//    }

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
//        $items = array("things","other things","more things");

        //next or previous month
        $month = (int)$baseMonth + (int)$next + (int)$prev;
        if (strlen($month) < 2) {
            $month = '0' . $month;
        }
        //find items again
        //skips to the next or previous year
        if($month<1){
            $year = $year - 1;
            $month = 12;
        }else if($month>12){
            $year = $year + 1;
            $month = 1;
        }
        //draws appropriate calendar
        echo draw_MonthlyCalendar($month, $year, $all_sched);
    }


?>