<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class EqSubgroup extends Db_Linked
{
    public static $fields = array('eq_subgroup_id','eq_group_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_subgroup_id';    
    public static $dbTable = 'eq_subgroups';

    public static function cmp($a,$b) {
        if ($a->ordering == $b->ordering) {
            return 0;
        }
        return ($a->ordering < $b->ordering) ? -1 : 1;
    }

    public static function cmpAlphabetical($a,$b) {
        if ($a->name == $b->name) {
            return 0;
        }
        return ($a->name < $b->name) ? -1 : 1;
    }

} 
?>