<?php
class Document_Model_Row_File extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * pripoji dokument do adresare
	 * 
	 * @param Document_Model_Row_Directory $directory
	 */
	public function attach(Document_Model_Row_Directory $directory) {
		$tableAssoc = new Document_Model_DirectoriesFiles();
		
		$tableAssoc->insert(array(
				"file_id" => $this->_data["id"],
				"directory_id" => $directory->id
		));
	}
	
	/**
	 * vytvori novou verzi souboru
	 * 
	 * @param string $path cesta k uploadovanemu tempu
	 * @param string $mimeType mime typ
	 * @return Document_Model_Row_Version
	 */
	public function createVersionFromFile($path, $mimeType = null) {
		$tableVersions = new Document_Model_Versions();
		
		$mime = is_null($mimeType) ? $this->mime : $mimeType;
		
		return $tableVersions->createVersionFormFile($this, $path, $mime);
	}
	
	/**
	 * vraci seznam adresaru, ve kterych se dokument nachazi
	 * 
	 * @return Document_Model_Rowset_Directories
	 */
	public function directories() {
		$tableAssocs = new Document_Model_DirectoriesFiles();
		$tableDirectories = new Document_Model_Directories();
		$adapter = $tableAssocs->getAdapter();
		
		$nameAssocs = $adapter->quoteIdentifier($tableAssocs->info("name"));
		$nameDirectories = $adapter->quoteIdentifier($tableDirectories->info("name"));
		
		$sql = "select $nameDirectories.* from $nameDirectories inner join $nameAssocs on id = directory_id where file_id = " . $this->_data["id"];
		$result = $adapter->query($sql)->fetchAll();
		
		return new Document_Model_Rowset_Directories(array(
				"data" => $result,
				"table" => $tableDirectories,
				"rowClass" => "Document_Model_Row_Directory"
		));
	}
	
	/**
	 * vraci seznam verzi souboru
	 * 
	 * @return Document_Model_Rowset_Versions
	 */
	public function versions() {
		$tableVersions = new Document_Model_Versions();
		
		return $tableVersions->fetchAll("file_id = " . $this->_data["id"], "id");
	}
}