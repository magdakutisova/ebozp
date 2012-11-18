<?php
class Audit_Model_Row_Category extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * najde a vraci podrizene kategorie
	 * 
	 * @param string $order razeni vysledku
	 * @return Audit_Model_Rowset_Categories
	 */
	public function getChildren($order = null) {
		// vygenerovani selectu s razenim
		$table = $this->getTable();
		$select = null;
		
		if ($order) {
			$select = $table->select(false)->order($order);
		}
		
		// vraceni vysledku
		return $this->findDependentRowset($table, "parent", $select);
	}
	
	/**
	 * vraci rodicovskou kategorii
	 * 
	 * @return Audit_Model_Row_Category
	 */
	public function getParent() {
		return $this->findParentRow($this->getTable(), "parent");
	}
	
	/**
	 * vraci true, pokud je kategorie zanorena
	 * 
	 * @return bool
	 */
	public function hasParent() {
		return $this->parent_id ? true : false;
	}
}