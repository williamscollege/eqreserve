<?php
	require_once('/classes/schedule.class.php');
	require_once('/classes/reservation.class.php');
	require_once('/classes/time_block.class.php');

	require_once('/head_ajax.php');

	#------------------------------------------------#
    $schedule      = (isset($_REQUEST["schedule"])) ? $_REQUEST["schedule"] : 0;
	$action        = (isset($_REQUEST["scheduleAction"])) ? $_REQUEST["scheduleAction"] : 0;
    $actionVal     = htmlentities( (isset($_REQUEST["actionVal"])) ? $_REQUEST["actionVal"] : 0 );

    #------------------------------------------------#

    function doDeleteEntireSchedule($s) {
        $s->loadTimeBlocks();
        $s->loadReservations();
        foreach ($s->time_blocks as $tb) {
            $tb->flag_delete = true;
            $tb->updateDb();
            if (! $tb->matchesDb) {
                doRevertDeleteEntireSchedule($s);
                return false;
            }
        }
        foreach ($s->reservations as $r) {
            $r->flag_delete = true;
            $r->updateDb();
            if (! $r->matchesDb) {
                doRevertDeleteEntireSchedule($s);
                return false;
            }
        }
        $s->flag_delete = true;
        $s->updateDb();
        if (! $s->matchesDb) {
            doRevertDeleteEntireSchedule($s);
            return false;
        }
        return true;
    }

    function doRevertDeleteEntireSchedule($s) {
        foreach ($s->time_blocks as $tb) {
            $tb->flag_delete = false;
            $tb->updateDb();
        }
        foreach ($s->reservations as $r) {
            $r->flag_delete = false;
            $r->updateDb();
        }
        $s->flag_delete = false;
        $s->updateDb();
    }

    #------------------------------------------------#

    $results = [
        'status'=> 'failure'
    ];

    if (! $schedule) {
        echo json_encode($results);
        exit;
    }

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
        $SCHED->notes = htmlentities($actionVal);
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
        if (doDeleteEntireSchedule($SCHED)) {
            $results['status'] = 'success';
        }
    }
    //###############################################################
    elseif ($action == 'deleteEqItem') {
        $SCHED->loadReservations();
        if (count($SCHED->reservations) == 1) {
            if ($SCHED->reservations[0]->eq_item_id == $actionVal) {
                if (doDeleteEntireSchedule($SCHED)) {
                    $results['status'] = 'success';
                }
            }
        }
        else {
            $RESV = Reservation::getOneFromDb(['schedule_id'=>$SCHED->schedule_id,'eq_item_id'=>$actionVal],$DB);
            if ($RESV->matchesDb) {
                $RESV->flag_delete = true;
                $RESV->updateDb();
                if ($RESV->matchesDb) {
                    $results['status'] = 'success';
                }
            }
        }
    }
    //###############################################################
    elseif ($action == 'deleteReservation') {
        $SCHED->loadReservations();
        if (count($SCHED->reservations) == 1) {
            if ($SCHED->reservations[0]->reservation_id == $actionVal) {
                if (doDeleteEntireSchedule($SCHED)) {
                    $results['status'] = 'success';
                }
            }
        }
        else {
            $RESV = Reservation::getOneFromDb(['reservation_id'=>$actionVal],$DB);
            if ($RESV->matchesDb) {
                $RESV->flag_delete = true;
                $RESV->updateDb();
                if ($RESV->matchesDb) {
                    $results['status'] = 'success';
                }
            }
        }
    }
    //###############################################################
    elseif ($action == 'deleteTimeBlock') {
        $SCHED->loadTimeBlocks();
        if (count($SCHED->time_blocks) == 1) {
            if ($SCHED->time_blocks[0]->time_block_id == $actionVal) {
                if (doDeleteEntireSchedule($SCHED)) {
                    $results['status'] = 'success';
                }
            }
        }
        else {
            $TB = TimeBlock::getOneFromDb(['time_block_id'=>$actionVal],$DB);
            if ($TB->matchesDb) {
                $TB->flag_delete = true;
                $TB->updateDb();
                if ($TB->matchesDb) {
                    $results['status'] = 'success';
                }
            }
        }
    }

    echo json_encode($results);
?>