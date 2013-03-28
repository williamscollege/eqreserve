<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class EqItem extends Db_Linked
{
    public static $fields = array('eq_item_id','eq_subgroup_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_item_id';    
    public static $dbTable = 'eq_items';

    public $eq_group;
    public $eq_subgroup;

    //##################################################
    // static functions

    public static function cmp($a,$b) {
        if (   ($a->eq_subgroup_id == $b->eq_subgroup_id)
            || (! $a->eq_subgroup_id)
            || (! $b->eq_subgroup_id)
        ) {
            if ($a->ordering == $b->ordering) {
                if ($a->name == $b->name) {
                    return 0;
                }
                return ($a->name < $b->name) ? -1 : 1;
            }
            return ($a->ordering < $b->ordering) ? -1 : 1;
        }

        # else
        $aLoad = true;
        $bLoad = true;
        if (! isset($a->eq_subgroup)) {$aLoad = $a->loadEqSubgroup();}                
        if (! isset($b->eq_subgroup)) {$bLoad = $b->loadEqSubgroup();}
        if ($aLoad && $bLoad) {
            return EqSubgroup::cmp($a->eq_subgroup,$b->eq_subgroup);
        }
        trigger_error("could not load subgroups of the items $a->name and $b->name for comparison",E_USER_ERROR);
    }

    //##################################################
    // instance functions

    public function loadEqSubgroup() {
        if (is_numeric($this->eq_subgroup_id)) {
            $this->eq_subgroup = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>$this->eq_subgroup_id,'flag_delete'=>false],$this->dbConnection);
            return $this->eq_subgroup->matchesDb;
        }
        return false;
    }

    public function loadEqGroup() {
        if (! isset($this->eq_subgroup)) {
            if (! $this->loadEqSubgroup()) { return false; }
        }
        if (! isset($this->eq_subgroup->eq_group)) {
            if (! $this->eq_subgroup->loadEqGroup()) { return false; }
        }
        $this->eq_group = $this->eq_subgroup->eq_group;
        return false;
    }


} 
?>