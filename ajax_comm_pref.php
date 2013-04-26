<?php
	require_once('/classes/schedule.class.php');
	require_once('/classes/reservation.class.php');
	require_once('/classes/time_block.class.php');

	require_once('/head_ajax.php');

	#------------------------------------------------#
    $comm_pref      = (isset($_REQUEST["comm_pref"])) ? $_REQUEST["comm_pref"] : 0;
	$action        = (isset($_REQUEST["commPrefAction"])) ? $_REQUEST["commPrefAction"] : 0;
    $actionValParam= (isset($_REQUEST["actionVal"])) ? $_REQUEST["actionVal"] : 0;

    $actionVal = true;
    if (($actionValParam == 0) || ($actionValParam == 'false') || ($actionValParam == '')) {
        $actionVal = false;
    }
    #------------------------------------------------#

    $results = [
        'status'=> 'failure'
    ];

    // make sure the specified schedule exists
    $CP = CommPref::getOneFromDb(['comm_pref_id'=>$comm_pref],$DB);
    if (! ($CP->matchesDb)) {
        echo json_encode($results);
        exit;
    }

    // check user access to the schedule
    if (! ((($USER->flag_is_system_admin) || ($USER->user_id == $CP->user_id)))) {
        echo json_encode($results);
        exit;
    }

    //###############################################################
    if ($action == 'setReminder') {
        $CP->flag_alert_on_upcoming_reservation = $actionVal;
        $CP->updateDb();
        if ($CP->matchesDb) {
            $results['status'] = 'success';
        }
    }
    //###############################################################
    elseif ($action == 'setAlertCreate') {
        $CP->flag_contact_on_reserve_create = $actionVal;
        $CP->updateDb();
        if ($CP->matchesDb) {
            $results['status'] = 'success';
        }
    }
    //###############################################################
    elseif ($action == 'setAlertCancel') {
        $CP->flag_contact_on_reserve_cancel = $actionVal;
        $CP->updateDb();
        if ($CP->matchesDb) {
            $results['status'] = 'success';
        }
    }

    echo json_encode($results);
?>