<?php
    require_once('head_ajax.php');
    require_once('../calendar_util.php');
    require_once('../classes/eq_group.class.php');


    //Debugging purposes
    //util_prePrintR($_REQUEST);

    #------------------------------------------------#
    # Fetch AJAX values for month to month views
    # NOTE: condition ? when_true : when_false
    #------------------------------------------------#
    $eq_group_id = htmlentities((isset($_REQUEST["eq_group_id"])) ? util_quoteSmart($_REQUEST["eq_group_id"]) : 0);
// TO TEST: handler behavior when no eq group id given
// TO TEST: when eq group has nothing scheduled
// TO TEST: when eq group has somethign scheduled, but for a time range not covered by the params passed in
// TO TEST: when eq group has something scheduled within the time range passed in

    //current month
    $baseMonth = htmlentities((isset($_REQUEST["month_num"])) ? util_quoteSmart($_REQUEST["month_num"]) : 0);
    //set at either -1 or 0
    $prev = htmlentities((isset($_REQUEST["prev"])) ? util_quoteSmart($_REQUEST["prev"]) : 0);
    //set at either 1 or 0

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
        $items = array("things","other things","more things");

        //draw appropriate calendar
        echo draw_SingleDayCalendar($clickedMonth, $clickedDay, $items);
    }elseif($baseMonth!=0){
//        $items = array("things","other things","more things");

        //next or previous month
        $month = (int)$baseMonth + (int)$next + (int)$prev;

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