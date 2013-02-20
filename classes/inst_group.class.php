<?php
class InstGroup extends Db_Linked
{
    public $fields = array('inst_group_id','name','flag_delete');
    public $primaryKeyField = 'inst_group_id';    
    public $dbTable = 'inst_groups';


} 
?>