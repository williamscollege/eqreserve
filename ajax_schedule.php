<?php
	require_once('/classes/schedule.class.php');
	require_once('/classes/reservation.class.php');
	require_once('/classes/time_block.class.php');

	require_once('/head_ajax.php');

	#------------------------------------------------#
    $schedule      = (isset($_REQUEST["schedule"])) ? $_REQUEST["schedule"] : 0;
	$action        = (isset($_REQUEST["scheduleAction"])) ? $_REQUEST["scheduleAction"] : 0;
    $actionVal     = htmlentities( (isset($_REQUEST["actionVal"])) ? $_REQUEST["actionVal"] : 0 );

    $results = [
        'status'=> 'failure'
    ];


    // make sure the specified schedule exists
    $SCHED = Schedule::getOneFromDb(['schedule_id'=>$schedule],$DB);
    if (! ($SCHED->matchesDb)) {
        echo json_encode($results);
        exit;
    }

    // check user access to the schedule
    if (! ((($USER->flag_is_system_admin) || ($USER->user_id == $SCHED->user_id)))) {
        echo json_encode($results);
        exit;
    }

    //###############################################################
    if ($action == 'updateNotes') {
        $SCHED->notes = $actionVal;
        $SCHED->updateDb();
        if ($SCHED->matchesDb) {
            $results['status'] = 'success';
        }
    }
    //###############################################################
    elseif ($action == 'updateType') {
        if ($actionVal == 'manager') {
            $SCHED->type = 'manager';
        }
        elseif ($actionVal == 'consumer') {
            $SCHED->type = 'consumer';
        }
        else {
            echo json_encode($results);
            exit;
        }
        $SCHED->updateDb();
        if ($SCHED->matchesDb) {
            $results['status'] = 'success';
        }
    }
    //###############################################################
    elseif ($action == 'deleteSchedule') {
        $SCHED->loadTimeBlocks();
        $SCHED->loadReservations();
        foreach ($SCHED->time_blocks as $tb) {
            $tb->flag_delete = true;
            $tb->updateDb();
        }
        foreach ($SCHED->reservations as $r) {
            $r->flag_delete = true;
            $r->updateDb();
        }
        $SCHED->flag_delete = true;
        $SCHED->updateDb();
        if ($SCHED->matchesDb) {
            $results['status'] = 'success';
        }
    }

    echo json_encode($results);
?>