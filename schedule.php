<?php
	$pageTitle = 'Schedule and Reservations';
	require_once('head.php');

//    $ig_ids = array_map(function($ig){return $ig->inst_group_id;},$USER->inst_groups);
//    if (! in_array($_REQUEST['inst_group'],$ig_ids)) {
//        util_redirectToAppHome('failure', 51);
//    }

    $SCHED = Schedule::getOneFromDb(['schedule_id' => $_REQUEST['schedule']],$DB);
    if ((! $USER->flag_is_system_admin) && ($SCHED->user_id != $USER->user_id)) {
        util_redirectToAppHome('failure', 52);
    }

    $SCHED->loadReservationsDeeply();
?>

    <legend>Schedule of Reservations</legend>


    <div class="control-group">
        <label class="control-label" for="reservations">Reservations on <strong><?php echo $SCHED->toString(); ?></strong></label>

        <p><small><?php echo $SCHED->notes; ?></small></p>

        <?php
        if ($SCHED->type == 'manager') {
            echo '<p class="text-warning"><strong>NOTE: This is a manager reservation</strong></p>';
        }
        ?>

        <div class="controls">
            For <a href="equipment_group.php?eid=<?php echo $SCHED->reservations[0]->eq_item->eq_group->eq_group_id; ?>"><?php echo $SCHED->reservations[0]->eq_item->eq_group->name; ?></a> you have reserved:

            <ul id="reservations">
                <?php
                foreach ($SCHED->reservations as $r) {
                    echo '<li>'.$r->eq_item->eq_subgroup->name.': '.$r->toString()."</li>\n";
                }
                ?>
            </ul>
        </div>
    </div>



<?php
	require_once('foot.php');
?>