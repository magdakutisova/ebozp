<?php

class Application_Model_DbTable_PositionHasEmployee extends Zend_Db_Table_Abstract {

	protected $_name = "position_has_employee";

	protected $_primary = array("id_employee", "id_position");

	protected $_sequence = false;

	protected $_referenceMap = array(
			"employee" => array(
				"columns" => "id_employee", 
				"refTableClass" => "Application_Model_DbTable_Employee", 
				"refColumns" => "id_employee"
			),
			"position" => array(
				"columns" => "id_position", 
				"refTableClass" => "Application_Model_DbTable_Position", 
				"refColumns" => "id_position"
			),
			
		);
}