<?php
class EqGroup extends Db_Linked
{
    public static $fields = array('eq_group_id','name','descr',
                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
                           'flag_delete');
    public static $primaryKeyField = 'eq_group_id';    
    public static $dbTable = 'eq_groups';


}

?>