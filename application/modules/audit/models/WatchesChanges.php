<?php
class Audit_Model_WatchesChanges extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches_changes";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"watch" => array(
					"columns" => "watch_id",
					"refTableClass" => "Audit_Model_Watches",
					"refColumns" => "id"
					)
	);
}