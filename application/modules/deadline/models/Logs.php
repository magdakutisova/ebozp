<?php
class Deadline_Model_Logs extends Zend_Db_Table_Abstract {
	
	protected $_name = "deadline_logs";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"deadline" => array(
					"columns" => "deadline_id",
					"refTableClass" => "Deadline_Model_Deadlines",
					"refColumns" => "id"
					)
			);
	
	protected $_rowClass = "Deadline_Model_Row_Log";
	
	protected $_rowsetClass = "Deadline_Model_Rowset_Logs";
}