<?php
class EqItem extends Db_Linked
{
    public $fields = array('eq_item_id','eq_subgroup_id','name','descr','ordering','flag_delete');
    public $primaryKeyField = 'eq_item_id';    
    public $dbTable = 'eq_items';


} 
?>