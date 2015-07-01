<?php
	$pageTitle = 'Institution Group';
	require_once('head_pre_output.php');

    $ig_ids = array_map(function($ig){return $ig->inst_group_id;},$USER->inst_groups);
    if ((! $USER->flag_is_system_admin) && (! in_array($_REQUEST['inst_group'],$ig_ids))) {
        util_redirectToAppHome('failure', 51);
    }

    $INST_GROUP = InstGroup::getOneFromDb(['inst_group_id' => $_REQUEST['inst_group']],$DB);
    $INST_GROUP->loadEqGroups();

    require_once('head_output.php');
?>

    <legend><?php echo $INST_GROUP->name; ?></legend>

    <div class="control-group">
        <label class="control-label" for="equipmentGroups">Equipment Groups</label>

        <div class="controls">
            <ul class="unstyled" id="equipmentGroups">
                <?php
                if (count($INST_GROUP->eq_groups) > 0) {
                    foreach ($INST_GROUP->eq_groups as $igeg) {
                        echo $igeg->toListItemLinked();
                    }
                }
                else {
                    echo "<li>$INST_GROUP->name does not have access to any equipment groups.</li>";
                }
                ?>
            </ul>
        </div>
    </div>



<?php
	require_once('foot.php');
?>