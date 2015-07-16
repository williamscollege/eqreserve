<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	class EqGroupPageReservationsTest extends WMSWebTestCase {

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

        function doBasicPageLoadAsserts() {
            $this->assertResponse(200);
            $this->assertNoPattern('/FAILED/i');
            $this->assertNoPattern('/ERROR/i'); //Seems to be a message that is getting in the way
        }

		//############################################################

		public function _loginAdmin() {
			# update test user to have system admin role
			makeAuthedTestUserAdmin($this->DB);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		public function _loginUser() {
			# user is a regular user (not admin!)
			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');

			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

        function TestOfReservationRulesDisplayed(){
            $this->_loginUser();
            $this->assertResponse(200);

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
            $this->assertResponse(200);

            $this->assertText('Reservation Time Restrictions');
            $this->assertText('Can be reserved for 15 minutes min., 60 minutes max., starting on the 0,15,30,45 hour for 15 minute intervals');
        }

        //To test application of reservation rules
        //STILL WORKING ON IT.
//        function TestOfCheckReservationRules(){
//            $this->_loginUser();
//            $this->assertResponse(200);
//
//            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
//            $this->assertResponse(200);
//
//            // asserts should check for programmatically accessible (by javascript) data about reservation rules
//            //CHECK THIS
//            $this->assertText("var reservationInfo = document.getElementById('print_ReservationTimeRestrictions');");
//
//            //check this info somehow with the information we need
        //}

		function TestOfDataExpectedForReservations() {
			$this->_loginUser();
			$this->assertResponse(200);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
			$this->assertResponse(200);

//			$this->assertText("testEqGroup1");
//
//			$this->assertText("testSubgroup1");
//			$this->assertText("testSubgroup2");
//			$this->assertText("testSubgroup3");
//			$this->assertText("testSubgroup4");
//
//			$this->assertText("testItem1");
//			$this->assertText("testItem2");
//			$this->assertText("testItem3");
//			$this->assertText("testItem4");
//			$this->assertText("testItem1");
//
//			$this->assertText("same priority as prev");
//			$this->assertText("same name, different subgroup");

            $this->assertText('Existing Reservations');
            $this->assertText('2013/3/22 10:00-10:15 AM by you');
            $this->assertText('[2013/3/26 10:00-10:30 AM], [2013/4/2 10:00-10:30 AM], [2013/4/9 10:00-10:30 AM] by you');
            $this->assertText('(MANAGEMENT) 2013/3/25 6:00-7:00 PM by you');
            $this->assertText('2013/3/26 6:00-7:00 PM by user removed from system: tu3F tu3L');

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=202');
            $this->assertResponse(200);

            $this->assertText('Existing Reservations');
            $this->assertText('(MANAGEMENT) 2013/3/25 6:00-7:00 PM by tu2F tu2L');
            $this->assertText('testUser2');
            $this->assertText('tu2@inst.edu');
            $this->assertText('Advised by: tu2Advisor');
            $this->assertText('Member of: testInstGroup2');
            $this->assertText('tu2 notes');
		}

        function TestOfCalendarHeadings(){
            $this->_loginUser();
            $this->assertResponse(200);

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
            $this->assertResponse(200);

            $this->assertText('Sunday');
            $this->assertText('Monday');
            $this->assertText('Tuesday');
            $this->assertText('Wednesday');
            $this->assertText('Thursday');
            $this->assertText('Friday');
            $this->assertText('Saturday');
        }

<<<<<<< HEAD
        function TestOfCalendarDivsPresent()
        {
            $this->_loginUser();

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');

            $this->doBasicPageLoadAsserts();

            $this->assertEltByIdHasAttrOfValue('monthly_calendar_view', 'id');
            $this->assertEltByIdHasAttrOfValue('daily_calendar_view', 'id');

            $this->assertEltByIdHasAttrOfValue('prev_nav', 'class', 'nav_elt_month_prev');
            $this->assertEltByIdHasAttrOfValue('month_display','class', 'month-name');
            $this->assertEltByIdHasAttrOfValue('next_nav','class', 'nav_elt_month_next');

            $this->assertEltByIdHasAttrOfValue('next_nav','data-monthnum', 'current-month'); //Need to find current month and year
            $this->assertEltByIdHasAttrOfValue('next_nav','data-yearnum', 'current-year');
            $this->assertEltByIdHasAttrOfValue('prev_nav','data-prev', '-1');
            $this->assertEltByIdHasAttrOfValue('next_nav','data-next', '1');
            $this->assertEltByIdHasAttrOfValue('day_lists','data-caldate'); //All the days

            //Idea: Check for the reservations
            //But: How to get the right month for the reservations to check?
=======
        function TestOfItemsInCalendar(){
            $this->_loginUser();
            $this->assertResponse(200);

            $this->('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
            $this->assertResponse(200);


>>>>>>> 716f9641427c5d92dbb21252c5aa567311e13b4b
        }

        // NOTE: can't figure out how to check that a given text is hidden / not visible
    }