<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/inst_group.class.php';
require_once dirname(__FILE__) . '/eq_group.class.php';

class User extends Db_Linked
{
    public static $fields = array('user_id','username','fname','lname','sortname','email','advisor','notes','flag_is_banned','flag_delete');
    public static $primaryKeyField = 'user_id';    
    public static $dbTable = 'users';

    public $inst_groups;
    public $eq_groups;

    public function __construct($initsHash) {
		parent::__construct($initsHash);
	
		// now do custom stuff
		// e.g. automatically load all accesibility info associated with the user

        $this->inst_groups = [];
        $this->eq_groups = [];

        if ($this->user_id) {
            $this->loadInstGroups();
            $this->loadEqGroups();
        }

		$this->flag_is_banned = false;
    }

    public function loadInstGroups() {
//		echo "myuser_id=".$this->user_id;
//		if ($this->user_id == 1) {trigger_error('cannot load inst groups for a user where user_id=1');}

		if (! $this->user_id) {
//			echo "myuser_id=".$this->user_id;
//			if ($this->user_id == 1) {trigger_error('NO VALUE: cannot load inst groups for a user where user_id=nothing');}

            trigger_error('cannot load inst groups for a user with no user_id');
            return;
        }

        $this->inst_groups = InstGroup::getInstGroupsForUser($this);
    }

    public function loadEqGroups() {
        if (! $this->user_id) {
            trigger_error('cannot load equipment groups for a user with no user_id');
            return;
        }
        $this->eq_groups = EqGroup::getAllEqGroupsForNonAdminUser($this);
    }

	public function updateDbFromAuth($auth) {
//echo "doing db update<br/>\n";
//$this->refreshFromDb();

        // if we're passed in an array of auth data, convert it to an object
        if (is_array($auth)) {
            $a = new Auth_Base();
            $a->username = $auth['username'];
            $a->fname = $auth['firstname'];
            $a->lname = $auth['lastname'];
            $a->email = $auth['email'];
            $a->inst_groups = array_slice($auth['inst_groups'],0);
            $auth = $a;
        }

//		print_r($auth);

		// test for basic invalid data
		if ($auth->fname == '') { return false;}
		if ($auth->lname == '') { return false;}
		if ($auth->email == '') { return false;}
		
		// update info if changed
		if ($this->fname != $auth->fname) { $this->fname = $auth->fname; }		// $this->__set('fname',$auth->fname)
		if ($this->lname != $auth->lname) { $this->lname = $auth->lname; }		
		if ($this->email != $auth->email) { $this->email = $auth->email; }

//User::getOneFromDb(['username'=>$this->username],$this->dbConnection)
		$this->updateDb();
//echo "TESTUSERIDUPDATED=" . $this->user_id . "<br>";

		#$this->user_id =
        // get the user's current inst groups and the corresponding array of inst group names
        $initialInstGroups = InstGroup::getInstGroupsForUser($this);
        $userInstGroupNames = array_map(function($e){return $e->name;},$initialInstGroups);

        // determine the differences between the user inst groups and the auth inst groups
        $extraUserInstGroupNames = array_diff($userInstGroupNames,$auth->inst_groups);
        $extraAuthInstGroupNames = array_diff($auth->inst_groups,$userInstGroupNames);

//print_r($extraUserInstGroupNames);
//print_r($extraAuthInstGroupNames);

        // if there are differences, handle them...
        if ((count($extraUserInstGroupNames) > 0) || (count($extraAuthInstGroupNames) > 0)) {

            // remove extras (i.e. user group that aren't in the auth list)
            foreach ($initialInstGroups as $ig) {
                if (in_array($ig->name,$extraUserInstGroupNames)) {
                    $ig->unlinkUser($this);
                }
            }

            // add new ones (i.e. auth list groups that the user doesn't have)
            foreach ($extraAuthInstGroupNames as $newGroupName) {
                $groupToAddToUser = InstGroup::getOneFromDb(['name'=>$newGroupName],$this->dbConnection);

                // check if the group didn't exist in the DB
                if (! $groupToAddToUser->matchesDb) {
//                    echo "handling new group creation for $newGroupName\n";
                    $groupToAddToUser->name = $newGroupName;
                    $groupToAddToUser->flag_delete = false;
//print_r($groupToAddToUser);
                    $groupToAddToUser->updateDb();
                }
                // else check if the group was prevriously deleted
                elseif ($groupToAddToUser->flag_delete) {
//                    echo "handling group undelete";
                    $groupToAddToUser->flag_delete = false;
                    $groupToAddToUser->updateDb();
                }

//      echo "handle linking user: \n";
                $groupToAddToUser->linkUser($this);

            }

            $this->loadInstGroups();
        }
        else { //...otherwise the current groups are OK, so assign them to this user object
            $this->inst_groups = $initialInstGroups;
        }

		return true;

	}
} 


?>