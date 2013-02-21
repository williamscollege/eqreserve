<?php
class TimeBlockGroup extends Db_Linked
{
    public static $fields = array('time_block_group_id','type','user_id','notes','flag_delete');
    public static $primaryKeyField = 'time_block_group_id';    
    public static $dbTable = 'time_block_groups';


} 
?>