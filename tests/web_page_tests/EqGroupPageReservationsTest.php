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
            $this->assertText('Can be reserved for 15 minutes min, 60 minutes max, starting on the 0,15,30,45 hour for 15 minute intervals');
        }

        //To test application of reservation rules
        function TestOfCheckReservationRules(){
            $this->_loginUser();
            $this->assertResponse(200);

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
            $this->assertResponse(200);

            //check that the options available to the user are within time restrictions
            $this->assertNoPattern("/<option value='5M'>/");
            $this->assertPattern("/<option value='15M'>/");
            $this->assertPattern("/<option value='30M'>/");
            $this->assertPattern("/<option value='45M'>/");
            $this->assertPattern("/<option value='60M'>/");
            $this->assertNoPattern("/<option value='2D'>/");
        }

		function TestOfDataExpectedForReservations() {
			$this->_loginUser();
			$this->assertResponse(200);

			$this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');
			$this->assertResponse(200);

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

        function TestOfCalendarDivsPresent()
        {
            $this->_loginUser();

            $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/equipment_group.php?eid=201');

            $this->assertPattern('<div id="monthly_calendar_view">');
            $this->assertPattern('<div id="daily_calendar_view">');
            $this->assertEltByIdHasAttrOfValue('prev_nav', 'class', 'nav_elt_month_prev');
            $this->assertEltByIdHasAttrOfValue('month_display', 'class', 'month-name');
            $this->assertEltByIdHasAttrOfValue('next_nav', 'class', 'nav_elt_month_next');

            $currentyear = util_getCurrentYearNum();
            $currentmonth = util_getCurrentMonthNum();
            $this->assertEltByIdHasAttrOfValue('next_nav', 'data-monthnum', $currentmonth); //Need to find current month and year
            $this->assertEltByIdHasAttrOfValue('next_nav', 'data-yearnum', $currentyear);
            $this->assertEltByIdHasAttrOfValue('prev_nav', 'data-prev', '-1');
            $this->assertEltByIdHasAttrOfValue('next_nav', 'data-next', '1');
            $this->assertEltByIdHasAttrOfValue('day_lists', 'data-daynum'); //All the days
        }

    }