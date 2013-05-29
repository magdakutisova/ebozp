<?php
class Document_Model_Files extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_files";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"user" => array(
					"columns" => "user_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
			)
	);
	
	protected $_rowClass = "Document_Model_Row_File";
	
	protected $_rowsetClass = "Document_Model_Rowset_Files";
	
	/**
	 * vytvori zaznam o souboru
	 * 
	 * @param string $name jmeno souboru
	 * @param string $mime mime typ
	 * @param int $userId identifikacni cislo uzivatele
	 * @return Document_Model_Row_File
	 */
	public function createFile($name, $mime, $userId) {
		$retVal = $this->createRow(array(
				"user_id" => $userId,
				"name" => $name
		));
		
		$retVal->save();
		
		return $retVal;
	}
}