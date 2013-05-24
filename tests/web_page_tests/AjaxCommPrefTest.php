<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxCommPrefTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

    function signIn() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

    function getToAcctMgtPage() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
        $this->clickLink(TESTINGUSER);
    }

    //############################################################

    function testCommPrefAjaxUserCanNotAccessOthersCommPref() {
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=106&commPrefAction=setReminder&actionVal=false');

        $this->assertPattern('/"status":"failure"/');

        $s = CommPref::getOneFromDb(['comm_pref_id'=>106],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertTrue($s->flag_alert_on_upcoming_reservation);
    }

    function testCommPrefAjaxInvalidAction() {
        $initialCommPref = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertTrue($initialCommPref->matchesDb);
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=superpowers&actionVal=foo123bar');

        $this->assertPattern('/"status":"failure"/');
        $s = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertEqual($s->flag_alert_on_upcoming_reservation,$initialCommPref->flag_alert_on_upcoming_reservation);
        $this->assertEqual($s->flag_contact_on_reserve_create,$initialCommPref->flag_contact_on_reserve_create);
        $this->assertEqual($s->flag_contact_on_reserve_cancel,$initialCommPref->flag_contact_on_reserve_cancel);
    }

    function testCommPrefSetReminder() {
        $initialCommPref = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertTrue($initialCommPref->matchesDb);
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setReminder&actionVal=1');

        $s = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertNotEqual($s->flag_alert_on_upcoming_reservation,$initialCommPref->flag_alert_on_upcoming_reservation);
        $this->assertEqual($s->flag_contact_on_reserve_create,$initialCommPref->flag_contact_on_reserve_create);
        $this->assertEqual($s->flag_contact_on_reserve_cancel,$initialCommPref->flag_contact_on_reserve_cancel);
    }

    function testCommPrefSetAlertCreate() {
        $initialCommPref = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertTrue($initialCommPref->matchesDb);
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setAlertCreate&actionVal=1');

        $s = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertEqual($s->flag_alert_on_upcoming_reservation,$initialCommPref->flag_alert_on_upcoming_reservation);
        $this->assertNotEqual($s->flag_contact_on_reserve_create,$initialCommPref->flag_contact_on_reserve_create);
        $this->assertEqual($s->flag_contact_on_reserve_cancel,$initialCommPref->flag_contact_on_reserve_cancel);
    }

    function testCommPrefSetAlertCancel() {
        $initialCommPref = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertTrue($initialCommPref->matchesDb);
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=101&commPrefAction=setAlertCancel&actionVal=1');

        $s = CommPref::getOneFromDb(['comm_pref_id'=>101],$this->DB);
        $this->assertEqual($s->flag_alert_on_upcoming_reservation,$initialCommPref->flag_alert_on_upcoming_reservation);
        $this->assertEqual($s->flag_contact_on_reserve_create,$initialCommPref->flag_contact_on_reserve_create);
        $this->assertNotEqual($s->flag_contact_on_reserve_cancel,$initialCommPref->flag_contact_on_reserve_cancel);
    }

    function testCommPrefAjaxAdminCanAccessOthersCommPref() {
        $u = User::getOneFromDb(['username'=>TESTINGUSER],$this->DB);
        $u->flag_is_system_admin = true;
        $u->updateDb();
        $this->assertTrue($u->matchesDb);
        $initialCommPref = CommPref::getOneFromDb(['comm_pref_id'=>106],$this->DB);
        $this->assertTrue($initialCommPref->flag_alert_on_upcoming_reservation);

        $this->getToAcctMgtPage();


        $this->get('http://localhost/eqreserve/ajax_actions/ajax_comm_pref.php?comm_pref=106&commPrefAction=setReminder&actionVal=false');


        $this->assertPattern('/"status":"success"/');
        $s = CommPref::getOneFromDb(['comm_pref_id'=>106],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertFalse($s->flag_alert_on_upcoming_reservation);
    }


}