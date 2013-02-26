<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class LinkUsersInstGroups extends Db_Linked
{
    public static $fields = array('user_id','inst_group_id','flag_delete');
    public static $primaryKeyField = '';
    public static $dbTable = 'link_users_inst_groups';


}
?>