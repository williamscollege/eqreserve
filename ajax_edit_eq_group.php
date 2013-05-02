<?php
	require_once('/classes/eq_group.class.php');
	require_once('/head_ajax.php');

/*
    Debugging:
    echo "<pre>"; print_r($_REQUEST); echo "</pre>"; exit();
*/

    $action                     = (isset($_REQUEST["ajaxVal_action"])) ? $_REQUEST["ajaxVal_action"] : 0;
    if (! $action) {
        return false;
        exit;
    }

    $results = array(
        'status' => 'failure'
    );

    if ($action == 'saveEqGroup') {
        $intEqGroupID               = htmlentities( (isset($_REQUEST["ajaxVal_ID"])) ? $_REQUEST["ajaxVal_ID"] : 0 );
        $strName                    = htmlentities( (isset($_REQUEST["ajaxVal_Name"])) ? util_quoteSmart($_REQUEST["ajaxVal_Name"]) : 0 );
        $strDescription             = htmlentities( (isset($_REQUEST["ajaxVal_Description"])) ? util_quoteSmart($_REQUEST["ajaxVal_Description"]) : 0 );
        $strStartMinute             = htmlentities( (isset($_REQUEST["ajaxVal_StartMinute"])) ? util_quoteSmart($_REQUEST["ajaxVal_StartMinute"]) : 0 );
        $intMinDurationMinute       = htmlentities( (isset($_REQUEST["ajaxVal_MinDurationMinute"])) ? $_REQUEST["ajaxVal_MinDurationMinute"] : 0 );
        $intMaxDurationMinute       = htmlentities( (isset($_REQUEST["ajaxVal_MaxDurationMinute"])) ? $_REQUEST["ajaxVal_MaxDurationMinute"] : 0 );
        $intDurationIntervalMinutes = htmlentities( (isset($_REQUEST["ajaxVal_DurationIntervalMinutes"])) ? $_REQUEST["ajaxVal_DurationIntervalMinutes"] : 0 );

        $eg = EqGroup::getOneFromDb(['eq_group_id' => $intEqGroupID], $DB);

        if (!$eg->matchesDb) { // error: matching record DOES NOT already exist
            return false;
            exit;
        }

        $eg->name                   = $strName;
        $eg->descr                  = $strDescription;
        $eg->start_minute           = $strStartMinute;
        $eg->min_duration_minutes   = $intMinDurationMinute;
        $eg->max_duration_minutes   = $intMaxDurationMinute;
        $eg->duration_chunk_minutes = $intDurationIntervalMinutes;

        $eg->updateDB();

        if (!$eg->matchesDb) { // error: matching record DOES NOT already exist
            return false;
            exit;
        }

        # Output HTML (for ajax success/fail checker)
        echo "success!";
    }
    elseif ($action == 'removePermission') {
        $permission_id = isset($_REQUEST["permission_id"]) ? $_REQUEST["permission_id"] : 0;

        if ($permission_id) {
            $permission = Permission::getOneFromDb(['permission_id'=>$permission_id],$DB);
            if ($permission->matchesDb) {

//                $USER->canManageEqGroup
                //&& ($USER->flag_is_system_admin ||

                $permission->flag_delete = true;
                $permission->updateDB();
                if ($permission->matchesDb) {
                    $results['status'] = 'success';
                }
            }
        }
        echo json_encode($results);
    }

?>