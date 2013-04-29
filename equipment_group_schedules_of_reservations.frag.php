<div class="class="form-horizontal">

    <legend class="pull-left row-fluid">Existing Reservations</legend><br clear="all"/>

    <ul id="equipmentGroups">
        <?php
        $Requested_EqGroup->loadSchedules();
        if (count($Requested_EqGroup->schedules) > 0) {
            foreach ($Requested_EqGroup->schedules as $sched) {

                $li = Db_Linked::listItemTag();
                if ($sched->type == 'manager') {
                    $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
                }
                if ($sched->user_id == $USER->user_id) {
                    $li .= '<strong><a href="schedule.php?schedule='.$sched->schedule_id.'"> '.$sched->toString().'</a></strong><br/>';
                }
                else {
                    $sched->loadUser();
                    $li .= '<strong>'.$sched->toString().'</strong> by ';

                    if (! $sched->user->matchedDb) {
                        $li .= '<i>user removed from system</i>';
                    }
                    else {
                        $li .= $sched->user->fname.' '.$sched->user->lname.'(TODO: add link/hover stuff)<br/>';
                    }
                }
                $li .= "<ul>\n";
                foreach ($sched->reservations as $r) {
                    $li .= '<li>'.$r->eq_item->eq_subgroup->name.': '.$r->eq_item->name."</li>\n";
                }
                $li .= "</ul></li>\n";

                echo $li;
            }
        }
        else {
            echo "<li>There is nothing reserved.</li>";
        }
        ?>
    </ul>

</div>