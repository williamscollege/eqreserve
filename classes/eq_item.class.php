<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class EqItem extends Db_Linked
{
    public static $fields = array('eq_item_id','eq_subgroup_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_item_id';    
    public static $dbTable = 'eq_items';

    //##################################################
    // static functions

    public static function cmp($a,$b) {
        if ($a->ordering == $b->ordering) {
            if ($a->name == $b->name) {
                return 0;
            }
            return ($a->name < $b->name) ? -1 : 1;
        }
        return ($a->ordering < $b->ordering) ? -1 : 1;
    }

    //##################################################
    // instance functions

} 
?>