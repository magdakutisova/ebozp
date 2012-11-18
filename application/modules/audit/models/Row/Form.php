<?php
class Audit_Model_Row_Form extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci rodicovsky dotaznik
	 * 
	 * @return Questionary_Model_Row_Questionary
	 */
	public function getQuestionary() {
		return $this->findParentRow("Questionary_Model_Questionaries", "questionary");
	}
}