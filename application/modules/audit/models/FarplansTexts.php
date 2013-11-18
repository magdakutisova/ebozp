<?php
class Audit_Model_FarplansTexts extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_farplans_texts";
	
	protected $_sequence = true;
	
	protected $_primary = array("id");
	
	protected $_refrenceMap = array(
			"category" => array(
					"columns" => "category_id",
					"refTableClass" => "Audit_Model_FarplansCategories",
					"refColumns" => "id"
					)
			);
}