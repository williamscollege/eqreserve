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
//        print_r($_REQUEST);
//        exit;

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
}

echo json_encode($results);
?>