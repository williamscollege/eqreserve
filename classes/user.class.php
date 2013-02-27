<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class User extends Db_Linked
{
    public static $fields = array('user_id','username','fname','lname','sortname','email','advisor','notes','flag_is_banned','flag_delete');
    public static $primaryKeyField = 'user_id';    
    public static $dbTable = 'users';

    public function __construct($initsHash) {
		parent::__construct($initsHash);
		
		// now do custom stuff
		// e.g. automatically load all accesibility info assocaited with the user
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
		
		return true;

		// TODO: handle inst groups here		
	}
} 


?>