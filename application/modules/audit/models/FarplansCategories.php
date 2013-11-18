<?php
class Audit_Model_FarplansCategories extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_farplans_categories";
	
	protected $_sequence = true;
	
	protected $_primary = array("id");
	
	protected $_refrenceMap = array(
			"farplan" => array(
					"columns" => "farplan_id",
					"refTableClass" => "Audit_Model_Farplans",
					"refColumns" => "id"
					)
			);
}