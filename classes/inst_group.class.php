<?php
require_once dirname(__FILE__) . '/db_linked.class.php';


class InstGroup extends Db_Linked
{
    public static $fields = array('inst_group_id','name','flag_delete');
    public static $primaryKeyField = 'inst_group_id';    
    public static $dbTable = 'inst_groups';

    public static function getInstGroupsForUser($user) {
        $getInstGroupsSql = "SELECT ig.inst_group_id, ig.name, ig.flag_delete 
                             FROM ".InstGroup::$dbTable." AS ig, link_users_inst_groups AS link 
                             WHERE link.user_id = ".$user->user_id." AND ig.inst_group_id = link.inst_group_id
                                AND link.flag_delete = 0 AND ig.flag_delete = 0";
        $getInstGroupsStmt = $user->dbConnection->prepare($getInstGroupsSql);
        $getInstGroupsStmt->execute();
        $groups = [];
        while ($row = $getInstGroupsStmt->fetch()) {
            $ig = new InstGroup(['DB'=>$user->dbConnection,'inst_group_id'=>$row['inst_group_id'],'name'=>$row['name'],'flag_delete'=>0]);
            array_push($groups,$ig);
        }
        return $groups;
    }

} 
?>