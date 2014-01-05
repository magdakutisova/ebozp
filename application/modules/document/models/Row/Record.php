<?php
class Document_Model_Row_Record extends Zend_Db_Table_Row_Abstract {
	
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

    /**
     * vraci radek obsahujici interni verzi souboru
     * pokud interni verze neni nastavena, vraci NULL
     *
     * @return Document_Model_Row_File
     */
    public function getInternal() {
        return $this->findParentRow(new Document_Model_Files(), "internal");
    }

    /**
     * vraci radek obsahujici verejnou verzi souboru
     * pokud neni verejna verze nastavena, vraci NULL
     *
     * @return Document_Model_Row_File
     */
    public function getPublic() {
        return $this->findParentRow(new Document_Model_Files(), "file");
    }
}
