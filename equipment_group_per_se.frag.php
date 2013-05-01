<?php

# admin or manager: is allowed to edit fields
if ($USER->flag_is_system_admin || $is_group_manager) {
?>
<legend class="pull-left row-fluid">Equipment Group
    <a href="#" id="toggleGroupSettings" class="btn btn-medium btn-primary"><i class="icon-white icon-pencil"></i> Edit</a></legend>

<div id="managerEdit" class="hide">
    <form action="ajax_edit_eq_group.php" class="form-horizontal" id="formEditGroup" name="formEditGroup" method="post">
        <input type="hidden" id="groupID" value="<?php echo $Requested_EqGroup->eq_group_id; ?>" />

        <div class="control-group">
            <label class="control-label" for="groupName">Group</label>

            <div class="controls">
                <input type="text" id="groupName" class="input-large" name="groupName" value="<?php echo $Requested_EqGroup->name; ?>" placeholder="Name of group" maxlength="200" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="groupDescription">Description</label>

            <div class="controls">
                <textarea rows="3" id="groupDescription" class="input-large" name="groupDescription" placeholder="Description of group"><?php echo $Requested_EqGroup->descr; ?></textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="groupManagers">Managed by</label>

            <div class="controls">

                <?php
                echo join(" ",
                    array_map(function ($m) {
                        $txt      = '';
                        $id       = 0;
                        $for_type = get_class($m);
                        if (get_class($m) == 'User') {
                            $id  = $m->user_id;
                            $txt = "$m->fname $m->lname ($m->email)";
                        }
                        else {
                            $id  = $m->inst_group_id;
                            $txt = "[$m->name]";
                        }
                        return "<button type=\"button\" class=\"btn btn-inverse btn-small\" title=\"$txt\" data-for-type=\"$for_type\" data-for-id=\"$id\">$txt <i class=\"icon-remove icon-white\"></i></button>";
                    }, $Requested_EqGroup->managers)
                );
                ?>

                <button type="button" class="btn btn-success btn-small" title="Add Manager"><i class="icon-plus-sign icon-white"></i> Add
                    Manager
                    <i class="icon-plus-sign icon-white"></i></button>

                <?php
                ?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="groupManagers">Reservable by</label>

            <div class="controls">
                <i>use CTRL and/or SHIFT to select more than one</i></i><br />
                <select name="consumers-select" id="consumers-select" class="user-select" size="12" multiple="multiple">
                    <?php
                    echo join(" ",
                        array_map(function ($c) {
                            $txt      = '';
                            $id       = 0;
                            $for_type = get_class($c);
                            if (get_class($c) == 'User') {
                                $id  = $c->user_id;
                                $txt = "$c->fname $c->lname ($c->email)";
                            }
                            else {
                                $id  = $c->inst_group_id;
                                $txt = "[$c->name]";
                            }
                            return "<option title=\"$txt\" data-for-type=\"$for_type\" data-for-id=\"$id\">$txt</option>";
                        }, $Requested_EqGroup->consumers)
                    );
                    ?>
                </select><br /><br />
                <button type="button" class="btn btn-danger btn-small" title="Remove Selected"><i class="icon-minus-sign icon-white"></i> Remove
                    Selected
                    <i class="icon-minus-sign icon-white"></i></button>
                <button type="button" class="btn btn-success btn-small" title="Add User"><i class="icon-plus-sign icon-white"></i> Add User
                    <i class="icon-plus-sign icon-white"></i></button>
            </div>
        </div>

        <legend>Reservation Rules</legend>

        <div class="control-group">
            <label class="control-label" for="goStartMinute">Start time (minutes)</label>

            <div class="controls">
                <?php
                $defaultStartMinute = [
                    ""           => "Select or Edit",
                    "00"         => "hourly (00)",
                    "0,30"       => "half hours (00,30)",
                    "0,15,30,45" => "quarter hours (00,15,30,45)"
                ];
                ?>
                <select id="goStartMinute" class="span2">
                    <?php
                    foreach ($defaultStartMinute as $key => $val) {
                        echo "<option value=\"$key\">$val</option>\n";
                    }
                    ?>
                </select>
                <i class="icon-arrow-right"></i>
                <input type="text" id="startMinute" class="input-medium" name="startMinute" value="<?php echo $Requested_EqGroup->start_minute; ?>" placeholder="Minutes (with commas)" maxlength="200" />
                Reservations must start and end on one of these minutes of the hour.
            </div>
        </div>
        <?php
        $defaultDuration = [
            ""    => "Select or Edit",
            15    => "15 minutes",
            30    => "30 minutes",
            45    => "45 minutes",
            60    => "1 hour",
            120   => "2 hours",
            240   => "4 hours",
            480   => "8 hours",
            960   => "16 hours",
            1440  => "24 hours",
            2880  => "2 days",
            10080 => "1 week",
            20160 => "2 weeks",
            80640 => "4 weeks"
        ];
        ?>
        <div class="control-group">
            <label class="control-label" for="goMinDurationMinutes">Min duration (minutes)</label>

            <div class="controls">
                <select id="goMinDurationMinutes" class="span2">
                    <?php
                    foreach ($defaultDuration as $key => $val) {
                        echo "<option value=\"$key\">$val</option>\n";
                    }
                    ?>
                </select>
                <i class="icon-arrow-right"></i>
                <input type="text" id="minDurationMinutes" class="input-mini" name="minDurationMinutes" value="<?php echo $Requested_EqGroup->min_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
                The minimum length of time that can be reserved.
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="goMaxDurationMinutes">Max duration (minutes)</label>

            <div class="controls">
                <select id="goMaxDurationMinutes" class="span2">
                    <?php
                    foreach ($defaultDuration as $key => $val) {
                        echo "<option value=\"$key\">$val</option>\n";
                    }
                    ?>
                </select>
                <i class="icon-arrow-right"></i>
                <input type="text" id="maxDurationMinutes" class="input-mini" name="maxDurationMinutes" value="<?php echo $Requested_EqGroup->max_duration_minutes; ?>" placeholder="Duration" maxlength="6" />
                The maximum length of time that can be reserved.
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="goDurationIntervalMinutes">Duration unit (minutes)</label>

            <div class="controls">
                <select id="goDurationIntervalMinutes" class="span2">
                    <?php
                    foreach ($defaultDuration as $key => $val) {
                        echo "<option value=\"$key\">$val</option>\n";
                    }
                    ?>
                </select>
                <i class="icon-arrow-right"></i>
                <input type="text" id="durationIntervalMinutes" class="input-mini" name="durationIntervalMinutes" value="<?php echo $Requested_EqGroup->duration_chunk_minutes; ?>" placeholder="Duration" maxlength="6" />
                The interval unit duration of time that can be reserved.
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="btnSubmitEditGroup"></label>

            <div class="controls">
                <button type="submit" id="btnSubmitEditGroup" class="btn btn-success" data-loading-text="Saving...">Save</button>
                <button type="button" id="btnCancelEditGroup" class="btn btn-link btn-cancel">Cancel</button>
            </div>
        </div>
    </form>
</div>
<?php
}

# Show this to all authenticated users
echo "<div id=\"managerView\">\n";
echo "Group: <span id=\"print_groupName\">" . $Requested_EqGroup->name . "</span><br />\n";
echo "Description: <span id=\"print_groupDescription\">" . $Requested_EqGroup->descr . "</span><br />\n";
echo "Managed by: ";
echo join(', ',
    array_map(function ($m) {
            if (get_class($m) == 'User') {
                return "$m->fname $m->lname";
            }
            return "[$m->name]";
        },
        $Requested_EqGroup->managers)
);
echo "<br />\n";
echo "<legend>Reservation Rules</legend>\n";
echo "Start times <span class=\"label label-inverse\" title=\"Reservations must start and end on one of these minutes of the hour\"><span id=\"print_startMinute\">" . $Requested_EqGroup->start_minute . "</span> minutes</span><br />\n";
echo "Min duration <span class=\"label label-inverse\" title=\"The minimum length of time that can be reserved\"><span id=\"print_minDurationMinutes\">" . $Requested_EqGroup->min_duration_minutes . "</span></span><br />\n";
echo "Max duration <span class=\"label label-inverse\" title=\"The maximum length of time that can be reserved\"><span id=\"print_maxDurationMinutes\">" . $Requested_EqGroup->max_duration_minutes . "</span></span><br />\n";
echo "Duration unit <span class=\"label label-inverse\" title=\"The time reserved must be an even multiple of this - this is the smallest about by which a reservation duration may be altered\"><span id=\"print_durationIntervalMinutes\">" . $Requested_EqGroup->duration_chunk_minutes . "</span></span><br />\n";
echo "</div>";

?>