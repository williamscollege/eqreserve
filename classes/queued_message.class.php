<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

    function __callbackRedirectValidateForDelivery($m) {
        return $m->validateForDelivery();
    }

	class QueuedMessage extends Db_Linked {
		public static $fields = array('queued_message_id', 'delivery_type', 'flag_is_delivered', 'hold_until_datetime',
            'target', 'summary', 'body',
            'action_datetime', 'action_status', 'action_notes',
            'flag_delete');
		public static $primaryKeyField = 'queued_message_id';
		public static $dbTable = 'queued_messages';

        public static $ALLOWED_DELIVERY_TYPES = ['email'];
        public static $DEFAULT_DELIVERY_TYPE = 'email';

        public static $LOG_MSG_BAD_DELIVERY_TYPE = 'delivery type is invalid';
        public static $LOG_MSG_BAD_TARGET_EMAIL = 'the email address is not valid';
        public static $LOG_MSG_BAD_SUMMARY_EMPTY = 'message summary may not be empty/false/blank';
        public static $LOG_MSG_BAD_SUMMARY_MULTILINE = 'message summary must be only a single line';
        public static $LOG_MSG_BAD_BODY_EMPTY = 'message body may not be empty/false/blank';
        public static $LOG_MSG_ALREADY_SENT = 'message has already been sent';
        public static $LOG_MSG_NO_ACTION_ON_DELETED_MESSAGES = 'no actions allowed on deleted messages';
        public static $LOG_MSG_HELD = 'the message is being held';

        //-------------------------------------------------------------------------------------

        public $validate_status = '';
        public $validate_message = '';

        //-------------------------------------------------------------------------------------

        public static function factory($db,$target,$summary,$body,$type='email') {
            $qm = new QueuedMessage(['DB'=>$db
                                     ,'delivery_type' => $type
                                     ,'flag_is_delivered' => false
                                     ,'hold_until_datetime' => ''
                                     ,'target' => $target
                                     ,'summary' => $summary
                                     ,'body' => $body
                                     ,'action_datetime' => (new DateTime())->format('Y-m-d H:i:s')
                                     ,'action_status' => 'CREATED'
                                     ,'action_notes' => 'CREATED: at '.(new DateTime())->format('Y-m-d H:i:s')
                                     ,'flag_delete' => false
                                    ]);
            return $qm;
        }



        public static function fetchMessagesReadyForDelivery($db,$asOfDateTime='') {
            if (! $asOfDateTime) { $asOfDateTime = (new DateTime())->format('Y-m-d H:i:s'); } // default to current datetime

            $qms = QueuedMessage::getAllFromDb(['flag_delete'=>false
                                               ,'hold_until_datetime <='=>$asOfDateTime
                                               ,'delivery_type'=>QueuedMessage::$ALLOWED_DELIVERY_TYPES
                                               ,'flag_is_delivered'=>false
                                               ,'target !='=>''
                                               ,'summary !='=>''
                                               ,'body !='=>''
                                                ],$db);

            $filtered_qms = array_filter($qms,'__callbackRedirectValidateForDelivery');

            return $filtered_qms;
        }

        //-------------------------------------------------------------------------------------

        public function trackAction($status,$note) {
            $this->action_datetime = (new DateTime())->format('Y-m-d H:i:s');
            $this->action_status = $status;
            $this->action_notes .= "\n$status: at ".$this->action_datetime." - $note";
        }

        public function validateForDelivery() {
            if (! in_array($this->delivery_type,QueuedMessage::$ALLOWED_DELIVERY_TYPES)) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_BAD_DELIVERY_TYPE;
                return false;
            }

            if ($this->delivery_type == 'email') {
                if (!filter_var($this->target, FILTER_VALIDATE_EMAIL)) {
                    $this->validate_status = 'FAILURE';
                    $this->validate_message = QueuedMessage::$LOG_MSG_BAD_TARGET_EMAIL;
                    return false;
                }
            }

            if (! trim($this->summary)) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_BAD_SUMMARY_EMPTY;
                return false;
            }

            if ((strpos($this->summary,"\n") !== false)  ||  (strpos($this->summary,"\r") !== false)) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_BAD_SUMMARY_MULTILINE;
                return false;
            }

            if (! trim($this->body)) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_BAD_BODY_EMPTY;
                return false;
            }

            if ($this->flag_is_delivered) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_ALREADY_SENT;
                return false;
            }

            if ($this->flag_delete) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_NO_ACTION_ON_DELETED_MESSAGES;
                return false;
            }

            if (($this->hold_until_datetime) && ($this->hold_until_datetime > (new DateTime())->format('Y-m-d H:i:s'))) {
                $this->validate_status = 'FAILURE';
                $this->validate_message = QueuedMessage::$LOG_MSG_HELD;
                return false;
            }

            $this->validate_status = 'SUCCESS';
            $this->validate_message = '';
            return true;
        }

        function attemptDelivery() {
            $this->action_datetime = (new DateTime())->format('Y-m-d H:i:s');
            if (! $this->validateForDelivery()) {
                $this->trackAction($this->validate_status,$this->validate_message);
                $this->updateDb();
                return false;
            }
            global $MAILER;
            if ($MAILER->send($this)) {
                $this->trackAction('SUCCESS','message sent via '.$MAILER->label);
                $this->flag_is_delivered = true;
                $this->updateDb();
                return true;
            } else
            {
                $this->trackAction('FAILURE','message could not be sent via '.$MAILER->label);
                $this->updateDb();
                return false;
            }
        }
	}

?>