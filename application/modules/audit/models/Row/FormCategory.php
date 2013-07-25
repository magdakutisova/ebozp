<?php
class Audit_Model_Row_FormCategory extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci seznam otazek v kategorii
	 * @return Audit_Model_Rowset_FormsCategoriesQuestions
	 */
	public function findQuestions() {
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		
		return $tableQuestions->fetchAll("!is_deleted and group_id = " . $this->_data["id"], "position");
	}
}