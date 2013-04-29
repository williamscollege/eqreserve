<div class="class="form-horizontal">

    <legend class="pull-left row-fluid">Existing Reservations</legend><br clear="all"/>

    <ul id="equipmentGroups">
        <?php
        $USER->loadSchedules();
        if (count($USER->schedules) > 0) {
            foreach ($USER->schedules as $sched) {
                echo $sched->toListItemLinked();
            }
        }
        else {
            echo "<li>You do not have anything reserved.</li>";
        }
        ?>
    </ul>

</div>