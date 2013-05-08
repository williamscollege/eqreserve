<?php
    require_once('/head_ajax.php');
	require_once('auth.cfg.php');

	#------------------------------------------------#
	$action        = (isset($_REQUEST["action"])) ? $_REQUEST["action"] : 0;
    $searchTerm = (isset($_REQUEST["searchTerm"])) ? $_REQUEST["searchTerm"] : 0;
    // NOTE: the search term is not sanitized!

    #------------------------------------------------#

    $results = [
        'status'=> 'failure'
    ];

    // no user access limitations, aside from the logged-in check provided by head_ajax
//echo '<pre>';
    //###############################################################
    if (($action == 'find') && (strlen($searchTerm) >= 3)) {
//        $results['status'] = 'success';
        // 1. locally matching users
        // 2. locally matching inst groups
        // 3. auth system based users
        // NOT auth system based inst groups - local inst groups are created as users log in, and it's (in our use cases) highly unlikely that someone would need to find an inst group for which a member user has not logged in to our system
        // 4. sort all results based on search match scoring-
        // search results ordering scoring:
        //  user basis: 5
        //  user username exact: +10
        //  user first name exact: +3
        //  user last name exact: +5
        //  user username substring: +2
        //  user first or last name substring: +0
        //  inst group basis: 1
        //  inst group exact name match: +10
        //  inst group name substring: +5


        $searchRes = [];

        //--------------------------------------------------------
        // IMPLEMENTATION 1. locally matching users
        $sql = "SELECT user_id, username, fname, lname, sortname, email, flag_is_banned
        FROM users
        WHERE (flag_delete = 0) AND (
        username LIKE CONCAT('%',:username,'%')
        OR fname LIKE CONCAT('%',:fname,'%')
        OR lname LIKE CONCAT('%',:lname,'%'))";
//echo "$sql\n\n";

        $stmt = $DB->prepare($sql);
        $stmt->execute([':username'=>$searchTerm,':fname'=>$searchTerm,':lname'=>$searchTerm]);
//print_r($stmt->errorInfo());
//echo "\n\n";

        $local_users_res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($local_users_res as &$lur) {
            $lur['matchValue'] = 5;
            if ($lur['flag_is_banned']) { $lur['matchValue'] = -100; }
            elseif ($lur['username']==$searchTerm)                       { $lur['matchValue'] += 10; }
            elseif ($lur['lname'] == $searchTerm)                    { $lur['matchValue'] += 5; }
            elseif ($lur['fname'] == $searchTerm)                    { $lur['matchValue'] += 3; }
            elseif (stripos($lur['username'],$searchTerm) !== false) { $lur['matchValue'] += 2; }
            elseif (stripos($lur['fname'],$searchTerm) !== false)    { $lur['matchValue'] += 0; }
            elseif (stripos($lur['lname'],$searchTerm) !== false)    { $lur['matchValue'] += 0; }
        }
//print_r($local_users_res);
//echo "\n\n";

        //--------------------------------------------------------
        // IMPLEMENTATION 2. locally matching inst groups
        $sql = "SELECT inst_group_id, `name`
        FROM inst_groups
        WHERE (flag_delete = 0) AND (
        `name` LIKE CONCAT('%',:name,'%'))";
//echo "$sql\n\n";

        $stmt = $DB->prepare($sql);
        $stmt->execute([':name'=>$searchTerm]);
//print_r($stmt->errorInfo());
//echo "\n\n";

        $local_inst_groups_res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($local_inst_groups_res as &$ligr) {
            $ligr['sortname'] = $ligr['name'];
            $ligr['matchValue'] = 1;
            if ($ligr['name']==$searchTerm) { $ligr['matchValue'] += 10; }
            else                            { $ligr['matchValue'] += 1; }
        }
//print_r($local_inst_groups_res);
//echo "\n\n";

        //--------------------------------------------------------
        // IMPLEMENTATION 3. auth system based users
        // TODO- implement auth system look up of users

        //--------------------------------------------------------
        // IMPLEMENTATION 4. sort all results based on search match scoring-
        $searchRes = array_merge($local_users_res,$local_inst_groups_res);
        usort($searchRes,function($a,$b){
            if ($a['matchValue'] == $b['matchValue']) {
                if ($a['sortname'] > $b['sortname']) { return 1; }
                if ($a['sortname'] < $b['sortname']) { return -1; }
                return 0;
            }
            if ($a['matchValue'] > $b['matchValue']) { return -1; }
            return 1;
        });
//print_r($searchRes);
//echo "\n\n";
        $results['searchResults'] = $searchRes;
        $results['status'] = 'success';
    }

    echo json_encode($results);

//echo '</pre>';
?>