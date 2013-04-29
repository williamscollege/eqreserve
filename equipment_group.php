<?php
	$pageTitle = 'Edit Equipment Group';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');

	if (! $IS_AUTHENTICATED) { // this may be redundant w/ the checking in head.php
        exit;
    }


    // fetch querystring
    if (!array_key_exists('eid', $_REQUEST)) {
        util_redirectToAppHome('failure', 20);
    }
    $eid = intval($_REQUEST["eid"]);

    // declare variables
    $Requested_EqGroup = [];
    $is_group_access   = FALSE;
    $is_group_manager  = FALSE;

    if ($USER->flag_is_system_admin) {
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => $eid], $DB);
        # security: ensure querystring is valid and user has access to that record
        if ($Requested_EqGroup->matchesDb) {
            $is_group_access  = TRUE;
            $is_group_manager = TRUE;
            //			echo "<pre>Requested_EqGroup:"; print_r($Requested_EqGroup); echo "</pre>";
        }
    }
    else {
        // does user have permission to access this group?
        $UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
        foreach ($UserEqGroups as $ueg) {
            if ($ueg->permission->eq_group_id == $eid) {
                // set flag: is this allowed to access this group?
                $is_group_access = TRUE;

                // create group object for easier manipulation
                $Requested_EqGroup = $ueg;

                // set flag: is group manager?
                if ($Requested_EqGroup->permission->role_id == 1) {
                    $is_group_manager = TRUE;
                }
            }
        }
    }

    // security: redirect if does not belong here
    if (!$is_group_access) {
        util_redirectToAppHome('failure', 50);
    }

    # get list of all managers for this group
    $Requested_EqGroup->loadPermissions();

    $managers  = [];
    $consumers = [];
    foreach ($Requested_EqGroup->permissions as $perm) {
        if ($perm->role_id == 1) {
            if ($perm->entity_type == 'user') {
                array_push($managers, User::getOneFromDb(['user_id' => $perm->entity_id], $DB));
            }
            elseif ($perm->entity_type == 'inst_group') {
                array_push($managers, InstGroup::getOneFromDb(['inst_group_id' => $perm->entity_id], $DB));
            }
        }
        else {
            if ($perm->entity_type == 'user') {
                array_push($consumers, User::getOneFromDb(['user_id' => $perm->entity_id], $DB));
            }
            elseif ($perm->entity_type == 'inst_group') {
                array_push($consumers, InstGroup::getOneFromDb(['inst_group_id' => $perm->entity_id], $DB));
            }
        }
    }

    ?>
    Remove this later: <a href="#" class="check">is form valid?</a><br>

    <script type="text/javascript" src="js/equipment_group.js"></script>


    <?php include('equipment_group_per_se.frag.php'); ?>

    <br />

    <?php include('equipment_group_subgroups_and_items.frag.php'); ?>

    <br />

    <?php include('equipment_group_schedules_of_reservations.frag.php'); ?>

    <?php
    require_once('foot.php');
?>