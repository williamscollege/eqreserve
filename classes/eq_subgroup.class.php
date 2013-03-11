<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/eq_group.class.php';

class EqSubgroup extends Db_Linked
{
    public static $fields = array('eq_subgroup_id','eq_group_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_subgroup_id';    
    public static $dbTable = 'eq_subgroups';


    public $eq_group;

	public function __construct($initsHash) {
		parent::__construct($initsHash);

		// now do custom stuff
		// e.g. automatically load all accesibility info associated with the user

		if(! array_key_exists('ordering',$initsHash)){
			$this->ordering = 1;
		}

	}

    //##################################################
    // instance functions

    public static function cmp($a,$b) {
        if ($a->ordering == $b->ordering) {
            return self::cmpAlphabetical($a,$b);
        }
        return ($a->ordering < $b->ordering) ? -1 : 1;
    }

    public static function cmpAlphabetical($a,$b) {
        if ($a->name == $b->name) {
            return 0;
        }
        return ($a->name < $b->name) ? -1 : 1;
    }

    //##################################################
    // instance functions

    public function loadEqGroup() {
        if (is_numeric($this->eq_group_id)) {
            $this->eq_group = EqGroup::getOneFromDb(['eq_group_id'=>$this->eq_group_id],$this->dbConnection);
            return $this->eq_group->matchesDb;
        }
        return false;
    }
} 
?>