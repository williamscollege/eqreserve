<?php
class CommPref extends Db_Linked
{
    public $fields = array('comm_pref_id','user_id','eq_group_id',
                           'flag_alert_on_upcoming_reservation','flag_contact_on_reserve_create','flag_contact_on_reserve_cancel');
    public $primaryKeyField = 'comm_pref_id';    
    public $dbTable = 'comm_prefs';


} 
?>