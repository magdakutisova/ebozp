<?php
class Application_Model_DbTable_TaskComment extends Zend_Db_Table_Abstract {
	
	protected $_name = "task_comment";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"user" => array(
					"columns" => "user_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
					),
			
			"task" => array(
					"columns" => "task_id",
					"refTableClass" => "Application_Model_DbTable_Task",
					"refColumns" => "id"
					)
			);
}