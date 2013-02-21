<?php
class EqSubgroup extends Db_Linked
{
    public static $fields = array('eq_subgroup_id','name','descr','ordering','flag_delete');
    public static $primaryKeyField = 'eq_subgroup_id';    
    public static $dbTable = 'eq_subgroups';


} 
?>