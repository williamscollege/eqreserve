<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/inst_membership.class.php';


class InstGroup extends Db_Linked
{
    public static $fields = array('inst_group_id','name','flag_delete');
    public static $primaryKeyField = 'inst_group_id';    
    public static $dbTable = 'inst_groups';

    /////////////////////

    // links the given user to this group; makes the given user a member of this group
    public function linkUser($u) {
        $m = InstMembership::getOneFromDb(['user_id'=>$u->user_id,'inst_group_id'=>$this->inst_group_id],$this->dbConnection);

        if (! $m->matchesDb) {
            $m = new InstMembership(['user_id'=>$u->user_id,'inst_group_id'=>$this->inst_group_id,'flag_delete'=>false,'DB'=>$this->dbConnection]);
            $m->updateDb();
        } 
        elseif ($m->flag_delete) {
            $m->flag_delete = false;
            $m->updateDb();
        }
        $u->loadInstGroups();
    }

    // unlinks the given user from this group; makes the given user a NOT member of this group
    public function unlinkUser($u) {
        $m = InstMembership::getOneFromDb(['user_id'=>$u->user_id,'inst_group_id'=>$this->inst_group_id,'flag_delete'=>false],$this->dbConnection);
        if ($m->matchesDb) {
            $m->flag_delete = true;
            $m->updateDb();
        }
        $u->loadInstGroups();
    }

    // returns an array of all users that are members of this group
    public function getAllUsers() {
        $memberships = InstMembership::getAllFromDb(['inst_group_id'=>$this->inst_group_id,'flag_delete'=>false],$this->dbConnection);
        if (count($memberships) <= 0) {
            return [];
        }
        $userIds = array_map(function($e){return $e->user_id;},$memberships);
        return User::getAllFromDb(['user_id'=>$userIds,'flag_delete'=>false],$this->dbConnection);
    }

    /////////////////////

    public static function getInstGroupsForUser($user) {
        $memberships = InstMembership::getAllFromDb(['user_id'=>$user->user_id,'flag_delete'=>false],$user->dbConnection);
        if (count($memberships) <= 0) {
            return [];
        }
        $instGroupIds = array_map(function($e){return $e->inst_group_id;},$memberships);
        return InstGroup::getAllFromDb(['inst_group_id'=>$instGroupIds,'flag_delete'=>false],$user->dbConnection);
    }


} 
?>