<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/inst_group.class.php';

class User extends Db_Linked
{
    public static $fields = array('user_id','username','fname','lname','sortname','email','advisor','notes','flag_is_banned','flag_delete');
    public static $primaryKeyField = 'user_id';    
    public static $dbTable = 'users';

    public $inst_groups;

    public function __construct($initsHash) {
		parent::__construct($initsHash);
	
		// now do custom stuff
		// e.g. automatically load all accesibility info associated with the user

        if ($this->user_id) {
            $this->loadInstGroups();
        }
    }

    public function loadInstGroups() {
        if (! $this->user_id) {
            trigger_error('cannot load inst groups for a user with no user_id');
            return;
        }
        $this->inst_groups = [];
        $getInstGroupsSql = "SELECT ig.inst_group_id, ig.name, ig.flag_delete 
                             FROM ".InstGroup::$dbTable." AS ig, link_users_inst_groups AS link 
                             WHERE link.user_id = ".$this->user_id." AND ig.inst_group_id = link.inst_group_id
                                AND link.flag_delete = 0 AND ig.flag_delete = 0";
        $getInstGroupsStmt = $this->dbConnection->prepare($getInstGroupsSql);
        $getInstGroupsStmt->execute();
        while ($row = $getInstGroupsStmt->fetch()) {
            $ig = new InstGroup(['DB'=>$this->dbConnection,'inst_group_id'=>$row['inst_group_id'],'name'=>$row['name'],'flag_delete'=>0]);
            array_push($this->inst_groups,$ig);
        }
    }

//    public function loadEgGroups() {
//        $this->eq_groups = EqGroups::loadEqGroupsForUser($this);
//    }

	public function updateDbFromAuth($auth) {
/*
		foreach (User::$fields as $f) {
			if ($auth->$f != $this->$f) {
				$this->$f = $auth->$f;
			}
		}
*/
		// test for basic invalid data
		if ($auth->fname == '') { return false;}
		if ($auth->lname == '') { return false;}
		if ($auth->email == '') { return false;}
		
		// update info if changed
		if ($this->fname != $auth->fname) { $this->fname = $auth->fname; }		// $this->__set('fname',$auth->fname)
		if ($this->lname != $auth->lname) { $this->lname = $auth->lname; }		
		if ($this->email != $auth->email) { $this->email = $auth->email; }

		$this->updateDb();
		
		return true;

		// TODO: handle inst groups here		
	}
} 


?>