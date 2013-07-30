<?php
require_once('cl_head.php');

/*
 * this script sends emails to all users that have a reservation coming up in the next 2 days. only one email is sent to
 * a user. that email contains three sections: consumer reservations (normal), manager reservations (manager only), and
 * reservations on groups the user manages (manager only).
 *
 * NOTE: since the look-ahead is 2 days and this runs 1/day, that means that people get 2 reminders about each reservation
 */

# TODO: support command line arg for date start (default to today) and range (default to 2 days)

# TODO: consider grouping reservation data by schedule id in the email message - i.e. group together all items that were reserved together; currently the system put's each item on its own line with its own display of the time block

$cur_date = date('Y-m-d');
$lookahead_interval = 2;

function reservationDataToHumanReadableString($rsv,$flag_include_name_of_user=false) {
    $start_dt = new DateTime($rsv['time_block_start_datetime']);
    $end_dt = new DateTime($rsv['time_block_end_datetime']);

    $time_range_string_pt1_base = $start_dt->format('Y/n/j g:i');
    $time_range_string_pt1_ap = '';
    $time_range_string_pt2 = $end_dt->format('g:i A');
    if (   ($start_dt->format('a') != $end_dt->format('a'))
        || ($start_dt->format('Y/n/j') != $end_dt->format('Y/n/j'))) {
        $time_range_string_pt1_ap = ' '.$start_dt->format('A');
    }
    if ($start_dt->format('Y') != $end_dt->format('Y')) {
        $time_range_string_pt2 = $end_dt->format('Y/n/j g:i A');
    } elseif ($start_dt->format('Y/n/j') != $end_dt->format('Y/n/j')) {
        $time_range_string_pt2 = $end_dt->format('n/j g:i A');
    }
    $time_range_string = $time_range_string_pt1_base.$time_range_string_pt1_ap.' - '.$time_range_string_pt2;

    $item_info = $rsv['item_name'].' (in '.$rsv['group_name'].' : '.$rsv['subgroup_name'].')';

    $user_info = '';
    if ($flag_include_name_of_user) {
        $user_info = ' reserved by '.$rsv['user_fname'].' '.$rsv['user_lname'].' ('.$rsv['username'].')';
    }

    return "$time_range_string $item_info$user_info";
}

# 1. get all the upcoming time blocks (cur time to cur time + 48 hours); for each time block, get the schedule, reservations, extended item info, and user info
$eq_reservation_sql =
"SELECT
    u.user_id AS user_id
   ,u.username AS username
   ,u.fname AS user_fname
   ,u.lname AS user_lname
   ,u.email AS user_email
   ,t.start_datetime AS time_block_start_datetime
   ,t.end_datetime AS time_block_end_datetime
   ,s.schedule_id AS schedule_id
   ,s.type AS schedule_type
   ,s.notes AS schedule_notes
   ,s.summary AS schedule_summary
   ,i.name AS item_name
   ,sg.name AS subgroup_name
   ,g.eq_group_id AS group_id
   ,g.name AS group_name
 FROM
   time_blocks AS t,
   reservations AS r,
   schedules AS s,
   users AS u,
   eq_items AS i,
   eq_subgroups AS sg,
   eq_groups AS g
 WHERE t.start_datetime >= '$cur_date' AND t.start_datetime <= DATE_ADD('$cur_date', INTERVAL $lookahead_interval DAY)
   AND s.schedule_id = t.schedule_id
   AND r.schedule_id = t.schedule_id
   AND i.eq_item_id = r.eq_item_id
   AND sg.eq_subgroup_id = i.eq_subgroup_id
   AND g.eq_group_id = sg.eq_group_id
   AND u.user_id = s.user_id
   AND t.flag_delete = 0
   AND r.flag_delete = 0
   AND s.flag_delete = 0
   AND u.flag_delete = 0
   AND i.flag_delete = 0
   AND sg.flag_delete = 0
   AND g.flag_delete = 0
 ORDER BY
    u.user_id
   ,t.start_datetime
   ,t.end_datetime
   ,s.type
   ,s.schedule_id
   ,i.ordering
   ,sg.ordering
   ,g.name
 ";

$eq_reservation_stmt = $DB->prepare($eq_reservation_sql);
$eq_reservation_stmt->execute();
Db_Linked::checkStmtError($eq_reservation_stmt);

# 2. build up the user reservation info hash - cycle through the eq reservation data
/*
 * user_id :
 *      user_id
 *      username
 *      name
 *      email
 *      list of ids of managed eq groups
 *      consumer reservations :
 *          * time_block_id :
 *              begin_time
 *              end time
 *              eq group name
 *              eq_subgroup_name
 *              eq item names (text)
 *      manager reservations :
 *          * time_block_id :
 *              begin_time
 *              end time
 *              eq group name
 *              eq_subgroup_name
 *              eq item names (text)
 *      reservations on managed groups
 *          * time_block_id :
 *              user full name
 *              user username
 *              user email
 *              begin_time
 *              end time
 *              eq group name
 *              eq_subgroup_name
 *              eq item names (text)
 */

$groups_reservations_hash = [];
$users_info_hash = [];
$last_user_data = ['user_id' => 0];
while ($row = $eq_reservation_stmt->fetch(PDO::FETCH_ASSOC)) {

    # initialize the users info data structure if need be
    if (! array_key_exists($row['user_id'],$users_info_hash)) {
        $users_info_hash[$row['user_id']] = [
            'user_id' => $row['user_id']
            ,'username' => $row['username']
            ,'name' => $row['name']
            ,'email' => $row['email']
            ,'managed_group_ids' => []
            ,'consumer_reservations' => []
            ,'manager_reservations' => []
            ,'reservations_on_managed_groups' => []
        ];

        # get the list of the groups that the user manages
        $u = User::getOneFromDb(['user_id'=>$row[user_id]],$DB);
        $u->loadEqGroups();
        foreach ($u->eq_groups AS $u_eqg) {
            if ($u->canManageEqGroup($u_eqg)) {
                array_push($users_info_hash[$row['user_id']]['managed_group_ids'],$u_eqg->eq_group_id);
            }
        }
    }

    # append the eq reservation data to the appropriate list in the user structure
    if ($row['schedule_type'] == 'manager') {
        array_push($users_info_hash[$row['user_id']]['manager_reservations'],$row);
    } else
    {
        array_push($users_info_hash[$row['user_id']]['consumer_reservations'],$row);
    }

    # initialize the groups info data structure if need be
    if (! array_key_exists($row['group_id'],$groups_reservations_hash)) {
        $groups_reservations_hash[$row['group_id']] = [];
    }

    # append the eq reservation data to the appropriate group list
    array_push($groups_info_has[$row['group_id']],$row);
}

# match the group reservation data up with the users that manage those groups
foreach ($users_info_hash as $uid=>$udata) {
    foreach ($udata['managed_group_ids'] as $managed_gid) {
        foreach ($groups_reservations_hash[$managed_gid] as $grdata) {
            array_push($udata['reservations_on_managed_groups'],$grdata);
        }
    }
}


# 3. build and send the emails
/*
 * cycle through hash ids; build each email from that hash entry; sort each reservation group by begin time; make the email; send it and sleep for a moment to avoid overwhelming the mail server
 */
$from = 'equipment_reservation-no-reply@williams.edu';
$subject = "[EqReserve] $cur_date upcoming equipment reservations";

foreach ($users_info_hash as $uid=>$udata) {
    $body = "
Hello ".$udata['fname'],."

This is a reminder from the equipment reservation system about item reservations in the next $lookahead_interval days.";
    # add consumer reservation section if needed
    if (count($udata['consumer_reservations']) > 0) {
        $body .= "

        Equipment you have reserved for your use:";
        foreach ($udata['consumer_reservations'] as $rsv) {
            $body .= "
                    ".reservationDataToHumanReadableString($rsv);
        }
    }

    # add manager reservation section if needed
    if (count($udata['manager_reservations']) > 0) {
        $body .= "

        Equipment you have reserved for management/maintenance purposes:";
        foreach ($udata['manager_reservations'] as $rsv) {
            $body .= "
                    ".reservationDataToHumanReadableString($rsv);
        }
    }

    # add section for reservations in managed groups if needed
    if (count($udata['reservations_on_managed_groups']) > 0) {
        $body .= "

        Reservations other people have on equipment that you manage:";
        foreach ($udata['reservations_on_managed_groups'] as $rsv) {
            $body .= "
                    ".reservationDataToHumanReadableString($rsv,true);
        }
    }
    
    # TODO: actually send the email (the parts are built now
}
