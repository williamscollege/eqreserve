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
    <script type="text/javascript">
        $(document).ready(function () {
            // Toggle view vs edit
            $("#toggleEditMode").click(function () {
                // toggle form or plain-text
                $(".editing-control").toggleClass("hide");
                $(".view-control").toggleClass("hide");

                // toggle button label
                if ($("#toggleEditMode").attr('data-cur-mode') == 'view') {
                    $("#toggleEditMode").html('<i class="icon-white icon-ok"></i> View');
                    $("#toggleEditMode").attr('data-cur-mode','edit');
                }
                else {
                    $("#toggleEditMode").html('<i class="icon-white icon-pencil"></i> Edit');
                    $("#toggleEditMode").attr('data-cur-mode','view');
                }
            });
        });
    </script>

    <legend>Schedule of Reservations</legend>

    <a href="#" id="toggleEditMode" class="btn btn-medium btn-primary pull-right" data-cur-mode="view"><i class="icon-white icon-pencil"></i> Edit</a>

    <div class="control-group">
        <label class="control-label" for="reservations">Reservations on <strong><?php echo $SCHED->toString(); ?></strong></label>

        <p>
            <small class="view-control"><?php echo $SCHED->notes; ?></small>
            <div class="editing-control hide">
            <textarea id="sched-notes" name="sched-notes" class="notes-editing-region"><?php echo $SCHED->notes; ?></textarea>
            </div>
        </p>

        <?php
        if ($SCHED->type == 'manager') {
            echo '<p class="view-control text-warning"><strong>NOTE: These are management reservations</strong></p>';
        }

        if ($USER->managesEqGroup($SCHED->reservations[0]->eq_item->eq_group->eq_group_id)) {
        ?>
        <div class="editing-control hide text-warning">
            <input type="checkbox" name="sched-is-manager" id="sched-is-manager"<?php echo ($SCHED->type == 'manager')?' checked="checked"':''; ?>/> management reservations<br/><br/>
        </div>
        <?php
        }
        ?>
        <div class="controls">
            For <a href="equipment_group.php?eid=<?php echo $SCHED->reservations[0]->eq_item->eq_group->eq_group_id; ?>"><?php echo $SCHED->reservations[0]->eq_item->eq_group->name; ?></a> you have reserved:

            <ul id="reservations">
                <?php
                foreach ($SCHED->reservations as $r) {
                    echo '<li>';
                    echo '<a href="#" id="delete-reservation-'.$r->reservation_id.'" class="editing-control hide btn btn-medium btn-danger" data-for-reservation="'.$r->reservation_id.'"><i class="icon icon-trash"></i> </a> ';
                    echo $r->eq_item->eq_subgroup->name.': '.$r->toString()."</li>\n";
                }
                ?>
            </ul>
        </div>
    </div>

<?php
	require_once('foot.php');
?>