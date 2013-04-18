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

            $("#sched-notes").blur(function () {
                alert('TODO: implement sched notes save (check for change - implement change tracker)');
            });

            $("#sched-is-manager-btn").click(function () {
                // toggle form or plain-text
                var curType = $("#resv-current-type").html();
                if (curType == 'management') {
                    $("#resv-current-type").html('regular')
                    $("#resv-other-type").html('management')
                }
                else {
                    $("#resv-current-type").html('management')
                    $("#resv-other-type").html('regular')
                }
                $('#sched-is-manager-btn i.signifier').toggleClass('hide');
                alert('TODO: implement schedule is-manager toggle');
            });

            $(".delete-reservation-btn").click(function () {
                alert('TODO: implement delete a reservation');
            });

            $("#deleteEntireScheduleBtn").click(function () {
                alert('TODO: implement delete entire schedule');
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
            echo '<p class="view-control text-warning"><i class="icon-wrench"></i> <strong>This is a management schedule!</strong></p>';
        }
        else {
            echo '<p class="view-control text-info"><i class="icon-user"></i> <strong>This is a regular user schedule</strong></p>';
        }
        if ($USER->managesEqGroup($SCHED->reservations[0]->eq_item->eq_group->eq_group_id)) {
        ?>
        <div class="editing-control hide text-warning">
            <a href="#" id="sched-is-manager-btn" class="btn btn-medium btn-warning">
             <i class="icon-wrench signifier<?php echo ($SCHED->type == 'manager')?'':' hide';?>"></i>
             <i class="icon-user signifier<?php echo ($SCHED->type == 'manager')?' hide':'';?>"></i>
             This is a <strong><span id="resv-current-type"><?php echo ($SCHED->type == 'manager')?'management':'regular';?></span></strong> schedule;
             make it a <strong><span id="resv-other-type"><?php echo ($SCHED->type == 'manager')?'regular':'management';?></span></strong> schedule instead</span></a>
            <br/><br/>
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
                    echo '<a href="#" id="delete-reservation-'.$r->reservation_id.'" class="editing-control hide btn btn-medium btn-danger delete-reservation-btn" data-for-reservation="'.$r->reservation_id.'"><i class="icon icon-trash"></i> </a> ';
                    echo $r->eq_item->eq_subgroup->name.': '.$r->toString()."</li>\n";
                }
                ?>
            </ul>
        </div>
    </div>

    <br/><br/>
    <a href="#" id="deleteEntireScheduleBtn" class="editing-control hide btn btn-medium btn-danger"><i class="icon-trash"></i> DELETE ENTIRE SCHEDULE AND RESERVATIONS</a>

<?php
	require_once('foot.php');
?>