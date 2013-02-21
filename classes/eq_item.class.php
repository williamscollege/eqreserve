<?php
class EqItem extends Db_Linked
{
    public static $fields = array('eq_item_id','eq_subgroup_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_item_id';    
    public static $dbTable = 'eq_items';


} 
?>