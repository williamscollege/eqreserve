<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/eq_subgroup.class.php';


	class TestOfEqSubgroup extends UnitTestCaseDB
	{

		function setUp() {
		}

		function tearDown() {
		}

		public function TestOfCmp(){
			$sg1 = new EqSubgroup(['name'=>'nLater','ordering'=>1, 'DB'=>$this->DB]);
            $sg2 = new EqSubgroup(['name'=>'nEarlier','ordering'=>2, 'DB'=>$this->DB]);

            $cmp = EqSubgroup::cmp($sg1,$sg2);

			$this->assertNotNull($cmp);
			$this->assertEqual($cmp, -1);

            $cmp = EqSubgroup::cmp($sg2,$sg1);
			$this->assertEqual($cmp, 1);
		}

        public function TestOfCmpAlphabetical(){
            $sg1 = new EqSubgroup(['name'=>'nLater','ordering'=>1, 'DB'=>$this->DB]);
            $sg2 = new EqSubgroup(['name'=>'nEarlier','ordering'=>2, 'DB'=>$this->DB]);

            $cmp = EqSubgroup::cmpAlphabetical($sg1,$sg2);

            $this->assertNotNull($cmp);
            $this->assertEqual($cmp, 1);

            $cmp = EqSubgroup::cmpAlphabetical($sg2,$sg1);
            $this->assertEqual($cmp, -1);
        }

	}

?>