<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfEqItem extends WMSUnitTestCaseDB
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

            $i7 = new EqItem(['name' => 'i7', 'eq_subgroup_id'=>301, 'ordering'=>7, 'DB' => $this->DB]);
            $i8 = new EqItem(['name' => 'i6', 'eq_subgroup_id'=>302, 'ordering'=>6, 'DB' => $this->DB]);


            $c12 = EqItem::cmp($i1, $i2);
            $c32 = EqItem::cmp($i3, $i2);
            $c11 = EqItem::cmp($i1, $i1);  // identical

            $c45 = EqItem::cmp($i4, $i5); // same name, different ordering
            $c46 = EqItem::cmp($i4, $i6); // same ordering, different name

            $c78 = EqItem::cmp($i7, $i8); // different subgroups


            $this->assertEqual($c12, -1);
            $this->assertEqual($c32, 1);
            $this->assertEqual($c11, 0);

            $this->assertEqual($c45, -1);
            $this->assertEqual($c46, -1);

            $this->assertEqual($c78, -1);
        }


        //############################################################################
        // instance method tests

        public function TestOfLoadEgSubgroup() {
            $ei = EqItem::getOneFromDb(['eq_item_id'=>401],$this->DB);
            $this->assertEqual($ei->name,'testItem1');
            $this->assertNull($ei->eq_subgroup);

            // testing this
            $ei->loadEqSubgroup();

            $this->assertTrue(is_object($ei->eq_subgroup));
            $this->assertEqual(get_class($ei->eq_subgroup),'EqSubgroup');
            $this->assertEqual($ei->eq_subgroup->name,'testSubgroup1');
        }


        public function TestOfLoadEqGroup() {
            $ei = EqItem::getOneFromDb(['eq_item_id'=>401],$this->DB);
            $this->assertEqual($ei->name,'testItem1');
            $this->assertNull($ei->eq_group);

            // testing this
            $ei->loadEqGroup();

            $this->assertTrue(is_object($ei->eq_group));
            $this->assertEqual(get_class($ei->eq_group),'EqGroup');
            $this->assertEqual($ei->eq_group->name,'testEqGroup1');
        }

	}


?>