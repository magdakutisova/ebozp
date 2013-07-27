<?php
class Audit_Model_WatchesDiscussed extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches_discussed";
	
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