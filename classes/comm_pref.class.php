<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class CommPref extends Db_Linked {
		public static $fields = array('comm_pref_id', 'user_id', 'eq_group_id',
			'flag_alert_on_upcoming_reservation', 'flag_contact_on_reserve_create', 'flag_contact_on_reserve_cancel');
		public static $primaryKeyField = 'comm_pref_id';
		public static $dbTable = 'comm_prefs';

        function toHTML() {
            $ret = '<ul class="inline">';
            $ret .= '<li>Reminder on upcoming reservations: '.(($this->flag_alert_on_upcoming_reservation)?'YES':'NO').'</li>';
            $ret .= '<li>Alert on reservation created: '.(($this->flag_contact_on_reserve_create)?'YES':'NO').'</li>';
            $ret .= '<li>Alert on reservation cancelled: '.(($this->flag_contact_on_reserve_cancel)?'YES':'NO').'</li>';
            $ret .= '</ul>';

            return $ret;
        }

        function toHTMLForm() {
            $ret = '<ul class="inline">';
            $ret .= '<li>Reminder on upcoming reservations: <input type="checkbox" name="reminder_comm_pref_'.$this->comm_pref_id.'" data-for-comm-pref="'.$this->comm_pref_id.'"'.(($this->flag_alert_on_upcoming_reservation)?' checked="checked"':'').'/></li>';
            $ret .= '<li>Alert on reservation created: <input type="checkbox" name="alert_create_comm_pref_'.$this->comm_pref_id.'" data-for-comm-pref="'.$this->comm_pref_id.'"'.(($this->flag_contact_on_reserve_create)?' checked="checked"':'').'/></li>';
            $ret .= '<li>Alert on reservation cancelled: <input type="checkbox" name="alert_cancel_comm_pref_'.$this->comm_pref_id.'" data-for-comm-pref="'.$this->comm_pref_id.'"'.(($this->flag_contact_on_reserve_cancel)?' checked="checked"':'').'/></li>';
            $ret .= '</ul>';

            return $ret;
        }

	}

?>