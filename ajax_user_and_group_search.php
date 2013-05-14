<?php
    require_once('/head_ajax.php');
	require_once('auth.cfg.php');

	#------------------------------------------------#
	$action        = (isset($_REQUEST["action"])) ? $_REQUEST["action"] : 0;
    $searchTerm = (isset($_REQUEST["searchTerm"])) ? $_REQUEST["searchTerm"] : 0;
    $timingTag = (isset($_REQUEST["timingTag"])) ? $_REQUEST["timingTag"] : 0;
    // NOTE: the search term is not sanitized!

    #------------------------------------------------#

    $results = [
        'status'=> 'failure',
        'timingTag'=>$timingTag
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

        // WARNING: this section contains code/parameters that are highly institution-specific!
        // NOTE: not sure how to abstract this functionality - auth system and user search stuff is so institution-specific that trying to parameterize things in a general way would lead to MANY headaches; still, ideally this would go in some inst-specific file...
        $discard_chars = array(",", ".", "-", "*");
        $cleanedSearchTerm = str_replace($discard_chars, '', $searchTerm);
        $is_two_part_search_term = false;
        $term_parts = [];
        if (strpos($cleanedSearchTerm,' ') > 0) {
            $term_parts = explode(' ',$cleanedSearchTerm);
            $is_two_part_search_term = true;
        }

        $ldap_users_res = [];

        $ldap_host = AUTH_SEARCH_HOST;
        $ldap_port = AUTH_SEARCH_PORT;
        $ldap_search_user = AUTH_SEARCH_ACCT;
        $ldap_search_pw = AUTH_SEARCH_PASS;

        $search_filter = "(|(uid=*" . $cleanedSearchTerm . "*)(givenname=*" . $cleanedSearchTerm . "*)(sn=*" . $cleanedSearchTerm . "*))";
        if ($is_two_part_search_term) {
            $search_filter = "(&(givenname=*" . $term_parts[0] . "*)(sn=*" . $term_parts[1] . "*))";
        }
        $search_dn = "ou=people,o=williams";

        $ldap_link = ldap_connect($ldap_host, $ldap_port);
        if($ldap_link === false) {
            $results['notes'] = "Failed to connect to the LDAP server on host: [$ldap_host] port: [$ldap_port]";
            echo json_encode($results);
            exit;
        }
        ldap_set_option($ldap_link, LDAP_OPT_PROTOCOL_VERSION, 3);

        if(ldap_bind($ldap_link, $ldap_search_user, $ldap_search_pw) === false) {
            $results['notes'] = "Failed to connect to the LDAP server on host: [$ldap_host] port: [$ldap_port]";
            echo json_encode($results);
            exit;
        }

        $result_id    = ldap_search($ldap_link, $search_dn, $search_filter );      // Execute the LDAP search
        $result_count = ldap_count_entries($ldap_link, $result_id);                // Count the search results
        if($result_count > 0) {
            $result_entries = ldap_get_entries ($ldap_link, $result_id);
            for($i = 0; $i < $result_count; $i++)  {
                $entry = $result_entries[$i];
//                print_r($entry);
//                exit;
                $res_entry = [
                    'matchValue' => 3,
                    'user_id'=> 'new',
                    'username'=> array_key_exists('uid',$entry) ? $entry['uid'][0] : 'no username from auth system search',
                    'fname'=> array_key_exists('givenname',$entry) ? $entry['givenname'][0] : 'no first name from auth system search',
                    'lname'=> array_key_exists('sn',$entry) ? $entry['sn'][0] : 'no last name from auth system search',
                    'sortname'=> (array_key_exists('givenname',$entry) && array_key_exists('sn',$entry)) ? ($entry['givenname'][0].', '.$entry['sn'][0]) : 'no sortname created from auth system search',
                    'email'=> array_key_exists('mail',$entry) ? $entry['mail'][0] : 'no mail from auth system search',
                    'advisor'=> '',
                    'notes'=> '',
                    'flag_is_system_admin'=> 0,
                    'flag_is_banned'=> 0,
                    'flag_delete'=> 0
                ];

                if ($is_two_part_search_term) {
                    if ($res_entry['fname'] == $term_parts[0]) { $res_entry['matchValue'] += 8; }
                    if ($res_entry['lname'] == $term_parts[1]) { $res_entry['matchValue'] += 8; }
                    if (stripos($res_entry['fname'],$term_parts[0]) !== false)    { $res_entry['matchValue'] += 2; }
                    if (stripos($res_entry['lname'],$term_parts[1]) !== false)    { $res_entry['matchValue'] += 2; }
                }
                else {
                    if ($res_entry['username']==$cleanedSearchTerm)                       { $res_entry['matchValue'] += 10; }
                    elseif ($res_entry['lname'] == $cleanedSearchTerm)                    { $res_entry['matchValue'] += 5; }
                    elseif ($res_entry['fname'] == $cleanedSearchTerm)                    { $res_entry['matchValue'] += 3; }
                    elseif (stripos($res_entry['username'],$cleanedSearchTerm) !== false) { $res_entry['matchValue'] += 2; }
                    elseif (stripos($res_entry['fname'],$cleanedSearchTerm) !== false)    { $res_entry['matchValue'] += 0; }
                    elseif (stripos($res_entry['lname'],$cleanedSearchTerm) !== false)    { $res_entry['matchValue'] += 0; }
                }

                array_push($ldap_users_res,$res_entry);
            }
        }

        ldap_close($ldap_link);

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