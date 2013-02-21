<?php
class EqSubgroup extends Db_Linked
{
    public $fields = array('eq_subgroup_id','name','descr','ordering','flag_delete');
    public $primaryKeyField = 'eq_subgroup_id';    
    public $dbTable = 'eq_subgroups';


} 
?>