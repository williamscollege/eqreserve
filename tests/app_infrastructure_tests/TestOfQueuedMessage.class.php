<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
//	require_once dirname(__FILE__) . '/../../classes/role.class.php';


	class TestOfQueuedMessage extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}

        // static tests

		public function testQueuedMessageFactory(){
            $qm = QueuedMessage::factory($this->DB,Auth_Base::$TEST_EMAIL,'this is a subject/summary','this is the full message body');

            $this->assertEqual($qm->delivery_type,QueuedMessage::$DEFAULT_DELIVERY_TYPE);
            $this->assertEqual($qm->target,Auth_Base::$TEST_EMAIL);
            $this->assertEqual($qm->summary,'this is a subject/summary');
            $this->assertEqual($qm->body,'this is the full message body');

            $qm->updateDb();

            $this->assertTrue($qm->matchesDb);
		}

        public function testQueuedMessageFetchMessagesReadyForDelivery() {
            $qms = QueuedMessage::fetchMessagesReadyForDelivery($this->DB);

            $this->assertEqual(count($qms),2);

            $new_qm = QueuedMessage::factory($this->DB,Auth_Base::$TEST_EMAIL,'this is a subject/summary','this is the full message body');
            $new_qm->updateDb();

            $qms = QueuedMessage::fetchMessagesReadyForDelivery($this->DB);
            $this->assertEqual(count($qms),3);
        }

        // instance tests
        public function testQueuedMessageTrackAction(){
            $qm = QueuedMessage::factory($this->DB,Auth_Base::$TEST_EMAIL,'this is a subject/summary','this is the full message body');
            $this->assertEqual($qm->action_status,'CREATED');
            $this->assertPattern('/CREATED:/',$qm->action_notes);
            $this->assertNoPattern('/FAILURE:/',$qm->action_notes);
            $this->assertNoPattern('/SUCCESS:/',$qm->action_notes);

            $qm->trackAction('FAILURE','a test failure');
            $this->assertEqual($qm->action_status,'FAILURE');
            $this->assertPattern('/'.(new DateTime())->format('Y-m-d').'/',$qm->action_datetime);
            $this->assertPattern('/CREATED:/',$qm->action_notes);
            $this->assertPattern('/FAILURE:/',$qm->action_notes);
            $this->assertNoPattern('/SUCCESS:/',$qm->action_notes);

            $qm->trackAction('SUCCESS','a test success');
            $this->assertEqual($qm->action_status,'SUCCESS');
            $this->assertPattern('/'.(new DateTime())->format('Y-m-d').'/',$qm->action_datetime);
            $this->assertPattern('/CREATED:/',$qm->action_notes);
            $this->assertPattern('/FAILURE:/',$qm->action_notes);
            $this->assertPattern('/SUCCESS:/',$qm->action_notes);
        }

        public function testQueuedMessageValidateForDelivery(){
            $qm = QueuedMessage::factory($this->DB,Auth_Base::$TEST_EMAIL,'this is a subject/summary','this is the full message body');

            $qm->delivery_type = 'foo';
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_DELIVERY_TYPE);
            $qm->delivery_type = QueuedMessage::$DEFAULT_DELIVERY_TYPE;


            $qm->target = 'foo';
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_TARGET_EMAIL);
            $qm->target = Auth_Base::$TEST_EMAIL;


            $qm->summary = '';
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_EMPTY);
            $qm->summary ='this is a subject/summary';

            $qm->summary = ' ';
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_EMPTY);
            $qm->summary ='this is a subject/summary';

            $qm->summary = "\t";
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_EMPTY);
            $qm->summary ='this is a subject/summary';

            $qm->summary = "a\nb";
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_MULTILINE);
            $qm->summary ='this is a subject/summary';

            $qm->summary = "ab\r";
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_MULTILINE);
            $qm->summary ='this is a subject/summary';

            $qm->summary = "\nab";
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_SUMMARY_MULTILINE);
            $qm->summary ='this is a subject/summary';


            $qm->body = '';
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_BODY_EMPTY);
            $qm->body ='this is the full message body';

            $qm->body = "\n\n";
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_BAD_BODY_EMPTY);
            $qm->body ='this is the full message body';


            $qm->flag_is_delivered = true;
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_ALREADY_SENT);
            $qm->flag_is_delivered = false;


            $qm->flag_delete = true;
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_NO_ACTION_ON_DELETED_MESSAGES);
            $qm->flag_delete = false;


            $qm->hold_until_datetime = date_add(new DateTime(),new DateInterval('P10D'))->format('Y-m-d H:i:s');
            $this->assertFalse($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'FAILURE');
            $this->assertEqual($qm->validate_message,QueuedMessage::$LOG_MSG_HELD);
            $qm->hold_until_datetime = '';


            $this->assertTrue($qm->validateForDelivery());
            $this->assertEqual($qm->validate_status,'SUCCESS');
        }

        public function testQueuedMessageAttemptDelivery(){
            $qm = QueuedMessage::factory($this->DB,Auth_Base::$TEST_EMAIL,'this is a subject','this is the full message body');
            $qm->updateDb();
            $this->assertFalse($qm->flag_is_delivered);
            $this->assertEqual($qm->action_status,'CREATED');

            // send a known valid message
            $this->assertTrue($qm->attemptDelivery());

            $this->assertTrue($qm->flag_is_delivered);
            $this->assertEqual($qm->action_status,'SUCCESS');
            global $MAILER;
            $this->assertPattern('/'.$qm->target.'/',$MAILER->delivery_notes);
            $this->assertPattern('/'.$qm->summary.'/',$MAILER->delivery_notes);
            $this->assertPattern('/'.$qm->body.'/',$MAILER->delivery_notes);

            // verify it updated the DB correctly
            $qm2 = QueuedMessage::getOneFromDb(['queued_message_id'=>$qm->queued_message_id],$this->DB);
            $this->assertTrue($qm2->flag_is_delivered);
            $this->assertEqual($qm2->action_status,'SUCCESS');

            // attempt to send a known bad message (missing target)
            $qm3 = QueuedMessage::factory($this->DB,'','this is a subject','this is the full message body');
            $this->assertFalse($qm3->attemptDelivery());
            $this->assertFalse($qm3->flag_is_delivered);
            $this->assertEqual($qm3->action_status,'FAILURE');

            // verify that updated the DB correctly
            $qm4 = QueuedMessage::getOneFromDb(['queued_message_id'=>$qm3->queued_message_id],$this->DB);
            $this->assertFalse($qm4->flag_is_delivered);
            $this->assertEqual($qm4->action_status,'FAILURE');

        }
    }

?>