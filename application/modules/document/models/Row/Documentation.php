<?php
class Document_Model_Row_Documentation extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci radek s informacemi o klientovi
	 * 
	 * @return Zend_Db_Table_Row
	 */
	public function getClient() {
		return $this->findParentRow(new Application_Model_DbTable_Client(), "client");
	}
	
	/**
	 * vraci radek obsahujici pobocku
	 * 
	 * @return Zend_Db_Table_Row
	 */
	public function getSubsidiary() {
		return $this->findParentRow(new Application_Model_DbTable_Subsidiary(), "subsidiary");
	}
}