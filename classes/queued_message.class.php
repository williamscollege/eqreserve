<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class QueuedMessage extends Db_Linked {
		public static $fields = array('queued_message_id', 'delivery_type', 'flag_is_delivered', 'hold_until_datetime',
            'target', 'summary', 'body',
            'action_datetime', 'action_status', 'action_notes',
            'flag_delete');
		public static $primaryKeyField = 'queued_message_id';
		public static $dbTable = 'queued_messages';
	}

?>