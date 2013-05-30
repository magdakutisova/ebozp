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
	
	/**
	 * vraci vsechny soubory uzivatel s poctem pouziti v adresarich
	 * 
	 * @param int $userId id uzivatele
	 * @return Document_Model_Rowset_Files
	 */
	public function getByUserExtended($userId) {
		$tableAssocs = new Document_Model_DirectoriesFiles();
		$adapter = $this->getAdapter();
		$nameAssocs = $adapter->quoteIdentifier($tableAssocs->info("name"));
		$nameFiles = $adapter->quoteIdentifier($this->_name);
		
		$sql = "select $nameFiles.*, count(directory_id) as cnt from $nameFiles left join $nameAssocs on file_id = id where user_id = $userId group by id order by name";
		$result = $adapter->query($sql)->fetchAll();
		
		return new Document_Model_Rowset_Files(array("table" => $this, "rowClass" => $this->_rowClass, "data" => $result));
	}
	
	/**
	 * vraci vsechny soubory uzivatele ktere nejsou v zadnem adresari
	 * 
	 * @param int $userId id uzivatele
	 * @return Document_Model_Rowset_Files
	 */
	public function getTrash($userId) {
		$tableAssocs = new Document_Model_DirectoriesFiles();
		$adapter = $this->getAdapter();
		$nameFiles = $adapter->quoteIdentifier($this->_name);
		$nameAssocs = $adapter->quoteIdentifier($tableAssocs->info("name"));
		
		$sql = "select $nameFiles.* from $nameFiles where id not in (select file_id from $nameAssocs) and user_id = " . $userId;
		$result = $adapter->query($sql)->fetchAll();
		
		return new Document_Model_Rowset_Files(array("table" => $this, "data" => $result, "rowClass" => $this->_rowClass));
	}
}