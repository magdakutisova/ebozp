<?php
class Deadline_Model_Row_Category extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * nacte predka.
	 * pokud je kategorie korenova, pak vraci NULL
	 * 
	 * @return Deadline_Model_Row_Category
	 */
	public function findParent() {
		if ($this->parent_id) {
			return $this->findParentRow($this->getTable(), "parent");
		} else {
			return null;
		}
	}
	
	/**
	 * vraci seznam potomku
	 */
	public function children() {
		return $this->_table->fetchAll(array("parent_id = ?" => $this->id), "name");
	}
}
