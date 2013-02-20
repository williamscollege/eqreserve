<?php
class TimeBlockGroup extends Db_Linked
{
    public $fields = array('time_block_group_id','type','user_id','notes','flag_delete');
    public $primaryKeyField = 'time_block_group_id';    
    public $dbTable = 'time_block_groups';


} 
?>