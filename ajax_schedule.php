<?php
	require_once('/classes/schedule.class.php');
	require_once('/classes/reservation.class.php');
	require_once('/classes/time_block.class.php');

	require_once('/head_ajax.php');

	#------------------------------------------------#
	$action        = (isset($_POST["action"])) ? $_POST["action"] : 0;
//	$strDescription = (isset($_POST["ajaxVal_GroupDescription"])) ? util_quoteSmart($_POST["ajaxVal_GroupDescription"]) : 0;

    $results = [
        'status': 'fail'
    ];

    echo json_encode($results);
?>