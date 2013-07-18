<?php
class Audit_Model_Row_Form extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci seznam kategorii
	 * 
	 * @return Audit_Model_Rowset_Categories
	 */
	public function findCategories() {
		$tableCategories = new Audit_Model_FormsCategories();
		
		return $tableCategories->fetchAll(array("form_id = ?" => $this->_data["id"], "!is_deleted"), "position");
	}
}