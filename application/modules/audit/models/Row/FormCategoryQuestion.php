<?php
class Audit_Model_Row_FormCategoryQuestion extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci kategorii do ktere otzka nalezi
	 * 
	 * @return Audit_Model_Row_FormCategory
	 */
	public function getCategory() {
		return $this->findParentRow("Audit_Model_FormsCategories");
	}
}