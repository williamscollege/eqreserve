<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester.php';
require_once dirname(__FILE__) . '/../../util.php';


class TestOfUtil extends UnitTestCase {

	function setUp() {
	}
	
	function tearDown() {
	}


    function testGenRandomIdString() {
        
        $randomId = util_genRandomIdString(24);

        $this->assertEqual(24,strlen($randomId));
    }   

    function testWipeSession() {
        //session_start();
        $_SESSION['isAuthenticated'] = 'foo';
        $_SESSION['fingerprint'] = 'bar';
        $_SESSION['userdata'] = array('baz');

		$this->expectError("Cannot modify header information - headers already sent");

        util_wipeSession();

        $this->assertFalse(isset($_SESSION['isAuthenticated']));
        $this->assertFalse(isset($_SESSION['fingerprint']));
        $this->assertFalse(isset($_SESSION['userdata']));
    }   

    function testCheckAuthentication() {
        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = false;

        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = true;

        $this->assertTrue(util_checkAuthentication());
    }   

    function testCreateDbConnection() {
        
        $dbConn = util_createDbConnection(24);

        $this->assertEqual(get_class($dbConn),'PDO');
    }

    function testProcessTimeString() {
        $s = util_processTimeString('2013-03-09 15:05:00');

        $this->assertEqual($s['YYYY'],'2013');
        $this->assertEqual($s['MM'],'03');
        $this->assertEqual($s['DD'],'09');
        $this->assertEqual($s['hh'],'15');
        $this->assertEqual($s['mi'],'05');
        $this->assertEqual($s['ss'],'00');

        $this->assertEqual($s['Y'],'2013');
        $this->assertEqual($s['M'],'3');
        $this->assertEqual($s['D'],'9');
        $this->assertEqual($s['h'],'15');
        $this->assertEqual($s['hhap'],'03');
        $this->assertEqual($s['hap'],'3');
        $this->assertEqual($s['ap'],'PM');
        $this->assertEqual($s['m'],'5');
        $this->assertEqual($s['s'],'0');

        $this->assertEqual($s['date'],'2013/3/9');

        $s = util_processTimeString('2013-03-09 09:00:00');
        $this->assertEqual($s['hhap'],'09');
        $this->assertEqual($s['hap'],'9');
        $this->assertEqual($s['ap'],'AM');
        $this->assertEqual($s['mi'],'00');
        $this->assertEqual($s['m'],'0');
        $this->assertEqual($s['s'],'0');

        $s = util_processTimeString('2013-03-09 00:30:00');
        $this->assertEqual($s['ap'],'AM');
        $this->assertEqual($s['hh'],'00');
        $this->assertEqual($s['h'],'0');
        $this->assertEqual($s['hhap'],'12');
        $this->assertEqual($s['hap'],'12');
    }

    function testTimeRangeString() {
        $this->assertEqual(util_timeRangeString('2013-03-09 15:00:00','2013-03-09 15:30:00'),'2013/3/9 3:00-3:30 PM');
        $this->assertEqual(util_timeRangeString('2013-03-09 09:00:00','2013-03-09 15:30:00'),'2013/3/9 9:00 AM-3:30 PM');
        $this->assertEqual(util_timeRangeString('2013-03-09 15:00:00','2013-03-10 15:00:00'),'2013/3/9 3:00 PM-2013/3/10 3:00 PM');
    }

}
