<?php
	require_once('../classes/eq_group.class.php');

	require_once('/head_ajax.php');

	#------------------------------------------------#
	# Fetch AJAX values
	#------------------------------------------------#
	$strAction = htmlentities((isset($_REQUEST["ajaxVal_Action"])) ? util_quoteSmart($_REQUEST["ajaxVal_Action"]) : 0);
	$intGroupID = htmlentities((isset($_REQUEST["ajaxVal_GroupID"])) ? $_REQUEST["ajaxVal_GroupID"] : FALSE);
	$strName = htmlentities((isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : FALSE);
	$strDescription = htmlentities((isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : FALSE);
	$strStartMinute = htmlentities((isset($_REQUEST["ajaxVal_StartMinute"])) ? util_quoteSmart($_REQUEST["ajaxVal_StartMinute"]) : FALSE);
	$intMinDurationMinute = htmlentities((isset($_REQUEST["ajaxVal_MinDurationMinute"])) ? $_REQUEST["ajaxVal_MinDurationMinute"] : FALSE);
	$intMaxDurationMinute = htmlentities((isset($_REQUEST["ajaxVal_MaxDurationMinute"])) ? $_REQUEST["ajaxVal_MaxDurationMinute"] : FALSE);
	$intDurationIntervalMinutes = htmlentities((isset($_REQUEST["ajaxVal_DurationIntervalMinutes"])) ? $_REQUEST["ajaxVal_DurationIntervalMinutes"] : FALSE);


	#------------------------------------------------#
	# Set default return value
	#------------------------------------------------#
	$results = [
		'status' => 'failure',
		'note'   => ''
	];

	#------------------------------------------------#
	# Identify and process requested action
	#------------------------------------------------#
	//###############################################################
	if ($strAction == 'add-group') {
		$eg = EqGroup::getOneFromDb(['name' => $strName], $DB);

		if ($eg->matchesDb) {
			// error: matching record already exists
			echo json_encode($results);
			exit;
		}
		$eg->name  = $strName;
		$eg->descr = $strDescription;
		$eg->updateDb();

		$output = EqGroup::getOneFromDb(['name' => $strName], $DB);

		# Output HTML
		$results['status']       = 'success';
		$results['which_action'] = 'add-group';
		$results['html_output']  = '';

		$results['html_output'] .= "<li><a href=\"equipment_group.php?eid=" . $output->eq_group_id . "\" title=\"\">" . $output->name . "</a>: " . $output->descr . "</li>";
	}
	//###############################################################
	elseif ($strAction == 'save-group') {

		if (!$intGroupID) {
			// error: no value exists
			echo json_encode($results);
			exit;
		}

		$eg = EqGroup::getOneFromDb(['eq_group_id' => $intGroupID], $DB);

		if (!$eg->matchesDb) {
			// error: no matching record found
			echo json_encode($results);
			exit;
		}

		$doSave = FALSE;
		if (($strName) && ($strName != $eg->name)) {
			$eg->name = $strName;
			$doSave   = TRUE;
		}
		if (($strDescription) && ($strDescription != $eg->descr)) {
			$eg->descr = $strDescription;
			$doSave    = TRUE;
		}
		if ((($strStartMinute == '0') || ($strStartMinute)) && ($strStartMinute != $eg->start_minute)) {
			$eg->start_minute = $strStartMinute;
			$doSave           = TRUE;
		}
		if (($intMinDurationMinute) && ($intMinDurationMinute != $eg->min_duration_minutes)) {
			$eg->min_duration_minutes = $intMinDurationMinute;
			$doSave                   = TRUE;
		}
		if (($intMaxDurationMinute) && ($intMaxDurationMinute != $eg->max_duration_minutes)) {
			$eg->max_duration_minutes = $intMaxDurationMinute;
			$doSave                   = TRUE;
		}
		if (($intDurationIntervalMinutes) && ($intDurationIntervalMinutes != $eg->duration_chunk_minutes)) {
			$eg->duration_chunk_minutes = $intDurationIntervalMinutes;
			$doSave                     = TRUE;
		}

		if ($doSave) {
			$eg->updateDB();

			if (!$eg->matchesDb) {
				// error: no matching record found
				echo json_encode($results);
				exit;
			}
		}

		# Output
		$results['status'] = 'success';
	}
	//###############################################################
	else {
		$eg_id = isset($_REQUEST["eq_group"]) ? $_REQUEST["eq_group"] : FALSE;
		if (!$eg_id) {
			$results['note'] = 'no eg id';
			echo json_encode($results);
			exit;
		}
		$EQG = EqGroup::getOneFromDb(['eq_group_id' => $eg_id], $DB);
		if (!$EQG->matchesDb) {
			$results['note'] = 'no eg fetched';
			echo json_encode($results);
			exit;
		}

		if (!$USER->canManageEqGroup($EQG)) {
			echo json_encode($results);
			exit;
		}

		if ($strAction == 'null') {
			$results['status'] = 'success';
		}
		//###############################################################
		elseif ($strAction == 'removePermission') {

			$permission_ids = isset($_REQUEST["permission_ids"]) ? $_REQUEST["permission_ids"] : FALSE;

			if ($permission_ids) {
				foreach ($permission_ids as $permid) {
					$permission = Permission::getOneFromDb(['permission_id' => $permid], $DB);
					if ($permission->matchesDb) {
						$permission->flag_delete = TRUE;
						$permission->updateDB();
						if ($permission->matchesDb) {
							$USER->loadEqGroups();
							if (!$USER->canManageEqGroup($EQG)) {
								$permission              = Permission::getOneFromDb(['permission_id' => $permission_ids, 'flag_delete' => TRUE], $DB);
								$permission->flag_delete = FALSE;
								$permission->updateDB();
								$results['note'] .= "may not remove own manager access\n"; // unless user is system admin, in which case they'll be able to manage the group regardless of other perms
							}
							else {
								$results['status'] = 'success';
							}
						}
						else {
							$results['note'] .= "save failed\n";
						}
					}
					else {
						$results['note'] .= "permission fetch failed\n";
					}
				}
			}
			else {
				$results['note'] .= "permission ids invalid or missing\n";
			}
		}
		//###############################################################
		elseif ($strAction == 'addPermission') {

			$permission_type = isset($_REQUEST["permission_type"]) ? $_REQUEST["permission_type"] : FALSE;
			if (!$permission_type) {
				$results['note'] = 'no permission type';
				echo json_encode($results);
				exit;
			}
			$permission_entity_type = isset($_REQUEST["entity_type"]) ? $_REQUEST["entity_type"] : FALSE;
			if (!$permission_entity_type) {
				$results['note'] = 'no entity type';
				echo json_encode($results);
				exit;
			}
			$permission_entity_id = isset($_REQUEST["entity_id"]) ? $_REQUEST["entity_id"] : FALSE;
			if (!$permission_entity_id) {
				$results['note'] = 'no entity id';
				echo json_encode($results);
				exit;
			}
			$permission_username = isset($_REQUEST["username"]) ? $_REQUEST["username"] : FALSE;
			if (!$permission_username) {
				$results['note'] = 'no username';
				echo json_encode($results);
				exit;
			}

			$role_id = 0;
			if ($permission_type == 'consumer') {
				$role_id               = 2;
				$results['added_type'] = 'consumer';
			}
			elseif ($permission_type == 'manager') {
				$role_id               = 1;
				$results['added_type'] = 'manager';
			}
			if (!$role_id) {
				$results['note'] = 'no valid permission type';
				echo json_encode($results);
				exit;
			}

			if ($permission_entity_id == 'newFromAuthSource') {
				//            $results['note'] = 'TODO: new entities are not yet supported as a part of permission creation';
				//            echo json_encode($results);
				//            exit;
				// NOTE: there are two possibilities here.
				// First, check to see the indicated entity exists but has been deleted
				//  If it has/does, undelete them/it (for users, first make sure they're not banned - abort if they are)
				// Second, the user needs to be created from data in the auth system
				$new_user = User::getOneFromDb(['username' => $permission_username, 'flag_delete' => TRUE], $DB);
				if ($new_user->matchesDb) {
					$new_user->flag_delete = FALSE;
					$new_user->updateDb();
					if ($new_user->matchesDb) {
						$permission_entity_id = $new_user->user_id;
					}
					else {
						$results['note'] = 'failed to un-delete user ' . $permission_username;
						echo json_encode($results);
						exit;
					}
				}
				else {
					// create a new user from LDAP info based on the username
					//                $results['note'] = 'TODO: new entities are not yet supported as a part of permission creation';
					//                echo json_encode($results);
					//                exit;
					require_once('../auth.cfg.php');
					$auth_source_data = $AUTH->findOneUserByUsername($permission_username);
					if ($auth_source_data) {
						$new_user->username             = $auth_source_data['username'];
						$new_user->fname                = $auth_source_data['fname'];
						$new_user->lname                = $auth_source_data['lname'];
						$new_user->sortname             = $auth_source_data['sortname'];
						$new_user->email                = $auth_source_data['email'];
						$new_user->advisor              = '';
						$new_user->notes                = '';
						$new_user->flag_is_system_admin = FALSE;
						$new_user->flag_is_banned       = FALSE;
						$new_user->flag_delete          = FALSE;
						$new_user->updateDb();
						if ($new_user->matchesDb) {
							$permission_entity_id = $new_user->user_id;
						}
						else {
							$results['note'] = 'failed to create new user from auth data for ' . $permission_username;
							echo json_encode($results);
							exit;
						}
					}
					else {
						$results['note'] = 'failed to get auth data for ' . $permission_username;
						echo json_encode($results);
						exit;
					}
				}
			}

			$p = Permission::getOneFromDb(['entity_id' => $permission_entity_id, 'entity_type' => $permission_entity_type, 'role_id' => $role_id, 'eq_group_id' => $eg_id], $DB);
			if ($p->matchesDb) {
				$results['note'] = 'that access already exists';
				echo json_encode($results);
				exit;
			}

			$p = Permission::getOneFromDb(['entity_id' => $permission_entity_id, 'entity_type' => $permission_entity_type, 'role_id' => $role_id, 'eq_group_id' => $eg_id, 'flag_delete' => TRUE], $DB);
			if ($p->matchesDb) {
				$p->flag_delete = FALSE;
				$p->updateDb();
				if ($p->matchesDb) {

					$results['status']        = 'success';
					$results['permission_id'] = $p->permission_id;
					$results['entity_type']   = $p->entity_type;
					$results['entity_id']     = $p->entity_id;
					if ($p->entity_type == 'user') {
						$u = User::getOneFromDb(['user_id' => $p->entity_id], $DB);
						if ($u->matchesDb) {
							$results['name']     = $u->fname . ' ' . $u->lname;
							$results['username'] = $u->username;
							$results['email']    = $u->email;
						}
						else {
							$results['name'] = 'database error';
						}
					}
					else {
						$ig = InstGroup::getOneFromDb(['inst_group_id' => $p->entity_id], $DB);
						if ($ig->matchesDb) {
							$results['name'] = $ig->name;
						}
						else {
							$results['name'] = 'database error';
						}
					}
					//                $results[''] = ;
					//                $results[''] = ;
				}
				else {
					$results['note'] = 'problem updating the database to un-delete an existing record';
				}
				echo json_encode($results);
				exit;
			}

			$p->entity_id   = $permission_entity_id;
			$p->entity_type = $permission_entity_type;
			$p->role_id     = $role_id;
			$p->eq_group_id = $eg_id;
			$p->flag_delete = FALSE;

			$p->updateDb();
			if ($p->matchesDb) {
				$results['status']        = 'success';
				$results['permission_id'] = $p->permission_id;
				$results['entity_type']   = $p->entity_type;
				$results['entity_id']     = $p->entity_id;
				if ($p->entity_type == 'user') {

					$u = User::getOneFromDb(['user_id' => $p->entity_id], $DB);

					if ($u->matchesDb) {
						// Ensure that a comm_pref exists for every group for which this user has access
						$u->updateCommPrefs();

						$results['name']     = $u->fname . ' ' . $u->lname;
						$results['username'] = $u->username;
						$results['email']    = $u->email;
					}
					else {
						$results['name'] = 'database error';
					}
				}
				else {
					$ig = InstGroup::getOneFromDb(['inst_group_id' => $p->entity_id], $DB);
					if ($ig->matchesDb) {

						$im = InstMembership::getAllFromDb(['inst_group_id' => $ig->inst_group_id], $DB);
						foreach ($im as $user) {
							// Ensure that a comm_pref exists for every group for which this user has access
							$user->updateCommPrefs();
						}

						$results['name'] = $ig->name;
					}
					else {
						$results['name'] = 'database error';
					}
				}
			}
			else {
				$results['note'] = 'problem adding a record to the database';
			}
		}
	}
	//###############################################################


	#------------------------------------------------#
	# Debugging output
	#------------------------------------------------#
	//	echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();


	#------------------------------------------------#
	# Return JSON array
	#------------------------------------------------#
	echo json_encode($results);
	exit;

?>
