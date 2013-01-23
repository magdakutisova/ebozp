<?php
class Audit_Model_ChecksMistakes extends Zend_Db_Table_Abstract {
	
	/*
	 * konstanty pro oznacovani akce asociaci
	 */
	const DO_NOTHING = 0;
	const DO_REMOVE = 1;
	const DO_MARK = 2;
	const DO_NEW = 3;
	
	protected $_name = "audit_checks_mistakes";
	
	protected $_sequence = true;
	
	protected $_primary = array("check_id", "mistake_id");
	
	protected $_referenceMap = array(
			"check" => array(
					"columns" => "check_id",
					"refTableClass" => "Audit_Model_Checks",
					"refColumns" => "id"
			),
			
			"mistake" => array(
					"columns" => "mistake_id",
					"refTableClass" => "Audit_Model_AuditsRecordsMistakes",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_CheckMistake";
	
	protected $_rowsetClass = "Audit_Model_Rowset_ChecksMistakes";
}