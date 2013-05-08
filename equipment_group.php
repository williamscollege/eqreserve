<?php
	$pageTitle = 'Edit Equipment Group';
	require_once('head.php');
	require_once('/classes/eq_group.class.php');

	if (!$IS_AUTHENTICATED) { // this may be redundant w/ the checking in head.php
		exit;
	}


	// fetch querystring
	if (!array_key_exists('eid', $_REQUEST)) {
		util_redirectToAppHome('failure', 20);
	}
	$eid = intval($_REQUEST["eid"]);

	// declare variables
	$Requested_EqGroup = [];
	$is_group_access = FALSE;
	$is_group_manager = FALSE;

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

	$Requested_EqGroup->loadSchedules();

	# get list of all managers for this group
	$Requested_EqGroup->loadPermissions();
	$Requested_EqGroup->loadManagers();
	$Requested_EqGroup->loadConsumers();

	//    $managers  = [];
	//    $consumers = [];
	//    $manager_user_ids = [];
	//    $manager_inst_group_ids = [];
	//    $consumer_user_ids = [];
	//    $consumer_inst_group_ids = [];
	//    foreach ($Requested_EqGroup->permissions as $perm) {
	//        if ($perm->role_id == 1) {
	//            if ($perm->entity_type == 'user') {
	//                array_push($manager_user_ids,$perm->entity_id);
	//            }
	//            elseif ($perm->entity_type == 'inst_group') {
	//                array_push($manager_inst_group_ids,$perm->entity_id);
	//            }
	//        }
	//        else {
	//            if ($perm->entity_type == 'user') {
	//                array_push($consumer_user_ids,$perm->entity_id);
	//            }
	//            elseif ($perm->entity_type == 'inst_group') {
	//                array_push($consumer_inst_group_ids,$perm->entity_id);
	//            }
	//        }
	//    }
	//    if (count($manager_user_ids) > 0) {
	//        $manager_users = User::getAllFromDb(['user_id'=>$manager_user_ids],$DB);
	//        $managers = array_merge($managers,$manager_users);
	//    }
	//    if (count($manager_inst_group_ids) > 0) {
	//        $manager_inst_groups = InstGroup::getAllFromDb(['inst_group_id'=>$manager_inst_group_ids],$DB);
	//        $managers = array_merge($managers,$manager_inst_groups);
	//    }
	//    if (count($consumer_user_ids) > 0) {
	//        $consumer_users = User::getAllFromDb(['user_id'=>$consumer_user_ids],$DB);
	//        $consumers = array_merge($consumers,$consumer_users);
	//    }
	//    if (count($consumer_inst_group_ids) > 0) {
	//        $consumer_inst_groups = InstGroup::getAllFromDb(['inst_group_id'=>$consumer_inst_group_ids],$DB);
	//        $consumers = array_merge($consumers,$consumer_inst_groups);
	//    }

?>
	<script type="text/javascript" src="js/equipment_group.js"></script>
<?php
	include('equipment_group_per_se.frag.php');
	echo "<br/>\n";
	include('equipment_group_subgroups_and_items.frag.php');
	echo "<br/>\n";
	include('equipment_group_schedules_of_reservations.frag.php');
	echo "<br/>\n";
	require_once('foot.php');
?>