<?php
require_once('head_ajax.php');
require_once('../calendar_util.php');

//Debugging purposes
//util_prePrintR($_REQUEST);

#------------------------------------------------#
# Fetch AJAX values for month to month views
# NOTE: condition ? when_true : when_false
#------------------------------------------------#
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
//$eq_items = htmlentities((isset($_REQUEST["items"])) ? util_quoteSmart($_REQUEST["items"]) : 0);

#------------------------------------------------#
# Carry out corresponding actions for views
#------------------------------------------------#
if($clickedDay!=0){
    $items = array("things","other things","more things");

    //draw appropriate calendar
    echo draw_SingleDayCalendar($clickedMonth, $clickedDay, $items);
}elseif($baseMonth!=0){
    $items = array("things","other things","more things");

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
    echo draw_MonthlyCalendar($month, $year, $items);
}
?>