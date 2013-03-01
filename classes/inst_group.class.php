<?php
require_once dirname(__FILE__) . '/db_linked.class.php';


class InstGroup extends Db_Linked
{
    public static $fields = array('inst_group_id','name','flag_delete');
    public static $primaryKeyField = 'inst_group_id';    
    public static $dbTable = 'inst_groups';

    /////////////////////

    // links the given user to this group; makes the given user a member of this group
    public function linkUser($u) {
        $linkInstGroupToUserSql = "INSERT INTO link_users_inst_groups VALUES (".$u->user_id.",".$this->inst_group_id.",0)";
        $linkInstGroupToUserStmt = $this->dbConnection->prepare($linkInstGroupToUserSql);
        $linkInstGroupToUserStmt->execute();
        $u->loadInstGroups();
    }

    // unlinks the given user from this group; makes the given user a NOT member of this group
    public function unlinkUser($u) {
        $unlinkInstGroupFromUserSql = "UPDATE link_users_inst_groups SET flag_delete = 1 WHERE user_id=".$u->user_id." AND inst_group_id=".$this->inst_group_id;
        $unlinkInstGroupFromUserStmt = $this->dbConnection->prepare($unlinkInstGroupFromUserSql);
        $unlinkInstGroupFromUserStmt->execute();
        $u->loadInstGroups();
    }

    // returns an array of all users that are members of this group
    public function getAllUsers() {
        $getUserIdsSql = "SELECT user_id FROM link_users_inst_groups WHERE inst_group_id=".$this->inst_group_id." AND flag_delete = 0";
        $getUserIdsStmt = $this->dbConnection->prepare($getUserIdsSql);
        $getUserIdsStmt->execute();
        $users = [];
        while ($row = $getUserIdsStmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($users,User::loadOneFromDb($row,$this->dbConnection));
        }
        return $users;
    }

    /////////////////////

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