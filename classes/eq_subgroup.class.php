<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/eq_group.class.php';

class EqSubgroup extends Db_Linked
{
    public static $fields = array('eq_subgroup_id','eq_group_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_subgroup_id';    
    public static $dbTable = 'eq_subgroups';


    public $eq_group;
    public $eq_items;

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
        if (    (! $a->eq_group_id)
            ||  (! $b->eq_group_id)
            ||  ($a->eq_group_id == $b->eq_group_id)
        ) {
            if ($a->ordering == $b->ordering) {
                return self::cmpAlphabetical($a,$b);
            }
            return ($a->ordering < $b->ordering) ? -1 : 1;
        }

        # else
        $aLoad = true;
        $bLoad = true;
        if (! isset($a->eq_group)) {$aLoad = $a->loadEqGroup();}                
        if (! isset($b->eq_group)) {$bLoad = $b->loadEqGroup();}
        if ($aLoad && $bLoad) {
            return EqGroup::cmp($a->eq_group,$b->eq_group);
        }
        trigger_error("could not load groups of the subgroups $a->name and $b->name for comparison",E_USER_ERROR);
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

    public function loadEqItems() {
        $this->eq_items = EqItem::getAllFromDb(['eq_subgroup_id'=>$this->eq_subgroup_id,'flag_delete'=>false],$this->dbConnection);
        foreach ($this->eq_items as $ei) {
            $ei->eq_subgroup = $this;
            if (isset($this->eq_group)) {
                $ei->eq_group = $this->eq_group;
            }
        }
        return true;
    }
} 
?>