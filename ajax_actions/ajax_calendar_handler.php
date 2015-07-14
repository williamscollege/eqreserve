<?php
require_once('head_ajax.php');
require_once('../calendar_util.php');

//util_prePrintR($_REQUEST);

#------------------------------------------------#
# Fetch AJAX values
#------------------------------------------------#
//For month to month view
//current month
$baseMonth = htmlentities((isset($_REQUEST["month_num"])) ? util_quoteSmart($_REQUEST["month_num"]) : 0);
//set at either -1 or 0
$prev = htmlentities((isset($_REQUEST["prev"])) ? util_quoteSmart($_REQUEST["prev"]) : 0);
//set at either 1 or 0
$next = htmlentities((isset($_REQUEST["next"])) ? util_quoteSmart($_REQUEST["next"]) : 0);
$year = htmlentities((isset($_REQUEST["year_num"])) ? util_quoteSmart($_REQUEST["year_num"]) : 0);

//For month to day view
$clickedDay = htmlentities((isset($_REQUEST["caldate"])) ? util_quoteSmart($_REQUEST["caldate"]) : 0);
$clickedMonth = htmlentities((isset($_REQUEST["calmonth"])) ? util_quoteSmart($_REQUEST["calmonth"]) : 0);

if($clickedDay!=0){
//    print_r($clickedDay);

    $items = array("things","other things","more things");
    echo draw_SingleDayCalendar($clickedMonth, $clickedDay, $items);
}elseif($baseMonth!=0){
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
    // condition ? when_true : when_false

    //draws appropriate calendar
    echo draw_MonthlyCalendar($month, $year);
}
?>