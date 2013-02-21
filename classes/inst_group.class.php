<?php
class InstGroup extends Db_Linked
{
    public static $fields = array('inst_group_id','name','flag_delete');
    public static $primaryKeyField = 'inst_group_id';    
    public static $dbTable = 'inst_groups';


} 
?>