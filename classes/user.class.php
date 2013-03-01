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

        $this->inst_groups = InstGroup::getInstGroupsForUser($this);
    }

    public function loadEgGroups() {
        $this->eq_groups = EqGroup::getEqGroupsForUser($this);
    }

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
		
        // TODO: handle inst groups here        
        $this->loadInstGroups();

        // cycle through auth inst groups
        // load each one, creating if necessary
        // if there's an auth inst group that's not in the users list, add it (and create the relevant link)

        // cycle through user inst groups
        // if one isn't in the auth groups, un-link it (but do not delete the group)

		return true;

	}
} 


?>