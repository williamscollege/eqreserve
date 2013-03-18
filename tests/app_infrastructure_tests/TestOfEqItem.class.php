<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
    require_once dirname(__FILE__) . '/dataForTesting.php';

	class TestOfEqItem extends UnitTestCaseDB
	{

		function setUp() {
            createTestData_EqGroups($this->DB);
            createTestData_EqSubgroups($this->DB);
            createTestData_EqItems($this->DB);
		}

		function tearDown() {
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
            removeTestData_EqItems($this->DB);
		}

		//###################################
		// basic db interaction



        //############################################################################
        // static method tests

        public function TestOfEqItemCmp()
        {
            $i1 = new EqItem(['name' => 'i1', 'ordering'=>1, 'DB' => $this->DB]);
            $i2 = new EqItem(['name' => 'i2', 'ordering'=>2, 'DB' => $this->DB]);
            $i3 = new EqItem(['name' => 'i3', 'ordering'=>3, 'DB' => $this->DB]);
            $i4 = new EqItem(['name' => 'i4', 'ordering'=>4, 'DB' => $this->DB]);
            $i5 = new EqItem(['name' => 'i4', 'ordering'=>5, 'DB' => $this->DB]);
            $i6 = new EqItem(['name' => 'i5', 'ordering'=>4, 'DB' => $this->DB]);

            $c12 = EqItem::cmp($i1, $i2);
            $c32 = EqItem::cmp($i3, $i2);
            $c11 = EqItem::cmp($i1, $i1);

            $c45 = EqItem::cmp($i4, $i5);
            $c46 = EqItem::cmp($i4, $i6);

            $this->assertEqual($c12, -1);
            $this->assertEqual($c32, 1);
            $this->assertEqual($c11, 0);
            $this->assertEqual($c45, -1);
            $this->assertEqual($c46, -1);
        }


        //############################################################################
        // instance method tests

	}


?>