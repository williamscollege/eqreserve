<?php
require_once('/classes/eq_group.class.php');
require_once('/head_ajax.php');

/*
Debugging:
echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();
*/

$action = (isset($_REQUEST["ajaxVal_action"])) ? $_REQUEST["ajaxVal_action"] : false;

if ($action == 'saveEqGroup') {
    $intEqGroupID               = htmlentities( (isset($_REQUEST["ajaxVal_ID"])) ? $_REQUEST["ajaxVal_ID"] : false );
    $strName                    = htmlentities( (isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : false );
    $strDescription             = htmlentities( (isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : false );
    $strStartMinute             = htmlentities( (isset($_REQUEST["ajaxVal_StartMinute"])) ? util_quoteSmart($_REQUEST["ajaxVal_StartMinute"]) : false );
    $intMinDurationMinute       = htmlentities( (isset($_REQUEST["ajaxVal_MinDurationMinute"])) ? $_REQUEST["ajaxVal_MinDurationMinute"] : false );
    $intMaxDurationMinute       = htmlentities( (isset($_REQUEST["ajaxVal_MaxDurationMinute"])) ? $_REQUEST["ajaxVal_MaxDurationMinute"] : false );
    $intDurationIntervalMinutes = htmlentities( (isset($_REQUEST["ajaxVal_DurationIntervalMinutes"])) ? $_REQUEST["ajaxVal_DurationIntervalMinutes"] : false );

    if (! $intEqGroupID) { return false; exit; }

    $eg = EqGroup::getOneFromDb(['eq_group_id' => $intEqGroupID], $DB);

    if (!$eg->matchesDb) { // error: matching record DOES NOT already exist
        return false;
        exit;
    }

    $doSave = false;
    if (($strName) && ($strName != $eg->name)) { $eg->name = $strName; $doSave = true; }
    if (($strDescription) && ($strDescription != $eg->descr)) { $eg->descr = $strDescription; $doSave = true; }
    if ((($strStartMinute=='0')||($strStartMinute)) && ($strStartMinute != $eg->start_minute)) { $eg->start_minute = $strStartMinute; $doSave = true; }
    if (($intMinDurationMinute) && ($intMinDurationMinute != $eg->min_duration_minutes)) { $eg->min_duration_minutes = $intMinDurationMinute; $doSave = true; }
    if (($intMaxDurationMinute) && ($intMaxDurationMinute != $eg->max_duration_minutes)) { $eg->max_duration_minutes = $intMaxDurationMinute; $doSave = true; }
    if (($intDurationIntervalMinutes) && ($intDurationIntervalMinutes != $eg->duration_chunk_minutes)) { $eg->duration_chunk_minutes = $intDurationIntervalMinutes; $doSave = true; }

    if ($doSave) {
        $eg->updateDB();

        if (!$eg->matchesDb) { // error: matching record DOES NOT already exist
            return false;
            exit;
        }
    }

    # Output HTML (for ajax success/fail checker)
    echo "success!";
    exit;
}
else {
    $results = array('status' => 'failure','note'=>'');

    $eg_id = isset($_REQUEST["eq_group"]) ? $_REQUEST["eq_group"] : false;
    if (! $eg_id) { $results['note'] = 'no eg id'; echo json_encode($results); exit; }
    $EQG = EqGroup::getOneFromDb(['eq_group_id' => $eg_id], $DB);
    if (!$EQG->matchesDb) { $results['note'] = 'no eg fetched'; echo json_encode($results); exit; }

    if (! $USER->canManageEqGroup($EQG)) { echo json_encode($results); exit; }

    if ($action == 'null') {
        $results['status'] = 'success';
    }
    elseif ($action == 'removePermission') {

        $permission_ids = isset($_REQUEST["permission_ids"]) ? $_REQUEST["permission_ids"] : false;

        if ($permission_ids) {
            foreach ($permission_ids as $permid) {
                $permission = Permission::getOneFromDb(['permission_id'=>$permid],$DB);
                if ($permission->matchesDb) {
                    $permission->flag_delete = true;
                    $permission->updateDB();
                    if ($permission->matchesDb) {
                        $USER->loadEqGroups();
                        if (! $USER->canManageEqGroup($EQG)) {
                            $permission = Permission::getOneFromDb(['permission_id'=>$permission_ids,'flag_delete'=>true],$DB);
                            $permission->flag_delete = false;
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
    elseif ($action == 'addPermission') {
        $permission_type = isset($_REQUEST["permission_type"]) ? $_REQUEST["permission_type"] : false;
        if (! $permission_type) { $results['note'] = 'no permission type'; echo json_encode($results); exit; }
        $permission_entity_type = isset($_REQUEST["entity_type"]) ? $_REQUEST["entity_type"] : false;
        if (! $permission_entity_type) { $results['note'] = 'no entity type'; echo json_encode($results); exit; }
        $permission_entity_id = isset($_REQUEST["entity_id"]) ? $_REQUEST["entity_id"] : false;
        if (! $permission_entity_id) { $results['note'] = 'no entity id'; echo json_encode($results); exit; }
        $permission_username = isset($_REQUEST["username"]) ? $_REQUEST["username"] : false;
        if (! $permission_username) { $results['note'] = 'no username'; echo json_encode($results); exit; }

        $role_id = 0;
        if ($permission_type == 'consumer') {
            $role_id = 2;
            $results['added_type'] = 'consumer';
        }
        elseif ($permission_type == 'manager') {
            $role_id = 1;
            $results['added_type'] = 'manager';
        }
        if (! $role_id) {
            $results['note'] = 'no valid permission type'; echo json_encode($results); exit;
        }

        if ($permission_entity_id == 'newFromAuthSource') {
//            $results['note'] = 'TODO: new entities are not yet supported as a part of permission creation';
//            echo json_encode($results);
//            exit;
            // NOTE: there are two possibilities here.
            // First, check to see the indicated entity exists but has been deleted
            //  If it has/does, undelete them/it (for users, first make sure they're not banned - abort if they are)
            // Second, the user needs to be created from data in the auth system
            $new_user = User::getOneFromDb(['username'=>$permission_username,'flag_delete'=>true],$DB);
            if ($new_user->matchesDb) {
                $new_user->flag_delete = false;
                $new_user->updateDb();
                if ($new_user->matchesDb) {
                    $permission_entity_id = $new_user->user_id;
                }
                else {
                    $results['note'] = 'failed to un-delete user '.$permission_username;
                    echo json_encode($results);
                    exit;
                }
            }
            else {
                // create a new user from LDAP info based on the username
                $results['note'] = 'TODO: new entities are not yet supported as a part of permission creation';
                echo json_encode($results);
                exit;
            }
        }

        $p = Permission::getOneFromDb(['entity_id'=>$permission_entity_id,'entity_type'=>$permission_entity_type,'role_id'=>$role_id,'eq_group_id'=>$eg_id],$DB);
        if ($p->matchesDb) {
            $results['note'] = 'that access already exists';
            echo json_encode($results);
            exit;
        }

        $p = Permission::getOneFromDb(['entity_id'=>$permission_entity_id,'entity_type'=>$permission_entity_type,'role_id'=>$role_id,'eq_group_id'=>$eg_id,'flag_delete'=>true],$DB);
        if ($p->matchesDb) {
            $p->flag_delete = false;
            $p->updateDb();
            if ($p->matchesDb) {

                $results['status'] = 'success';
                $results['permission_id'] = $p->permission_id;
                $results['entity_type'] = $p->entity_type;
                $results['entity_id'] = $p->entity_id;
                if ($p->entity_type == 'user') {
                    $u = User::getOneFromDb(['user_id'=>$p->entity_id],$DB);
                    if ($u->matchesDb) {
                        $results['name'] = $u->fname.' '.$u->lname;
                        $results['username'] = $u->username;
                        $results['email'] = $u->email;
                    }
                    else {
                        $results['name'] = 'database error';
                    }
                }
                else {
                    $ig = InstGroup::getOneFromDb(['inst_group_id'=>$p->entity_id],$DB);
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

        $p->entity_id = $permission_entity_id;
        $p->entity_type = $permission_entity_type;
        $p->role_id = $role_id;
        $p->eq_group_id = $eg_id;
        $p->flag_delete = false;

        $p->updateDb();
        if ($p->matchesDb) {
            $results['status'] = 'success';
            $results['permission_id'] = $p->permission_id;
            $results['entity_type'] = $p->entity_type;
            $results['entity_id'] = $p->entity_id;
            if ($p->entity_type == 'user') {
                $u = User::getOneFromDb(['user_id'=>$p->entity_id],$DB);
                if ($u->matchesDb) {
                    $results['name'] = $u->fname.' '.$u->lname;
                    $results['username'] = $u->username;
                    $results['email'] = $u->email;
                }
                else {
                    $results['name'] = 'database error';
                }
            }
            else {
                $ig = InstGroup::getOneFromDb(['inst_group_id'=>$p->entity_id],$DB);
                if ($ig->matchesDb) {
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

echo json_encode($results);
?>