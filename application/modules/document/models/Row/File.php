<?php
class Document_Model_Row_File extends Zend_Db_Table_Row_Abstract {
	
	const TYPE_NONE = 0;
	const TYPE_DOCUMENTATION = 1;
	const TYPE_LEGISLATIVE = 2;
	const TYPE_DOCUMENTATION_PO = 3;
	const TYPE_DOCUMENTATION_BOZP = 4;
	
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
		
		$mime = is_null($mimeType) ? "application/octet-stream" : $mimeType;
		
		return $tableVersions->createVersionFormFile($this, $path, $mime);
	}
	
	/**
	 * vytvori novou verzi souboru z retezce
	 *
	 * @param string $content obsah souboru
	 * @param string $mimeType mime typ
	 * @return Document_Model_Row_Version
	 */
	public function createVersionFromString($content, $mimeType = null) {
		$tableVersions = new Document_Model_Versions();
	
		$mime = is_null($mimeType) ? "application/octet-stream" : $mimeType;
	
		return $tableVersions->createVersionFromString($this, $content, $mime);
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
	 * vraci dokumentaci, kde je soubor pouzit
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function getDocumentations($cliendId = null) {
		// vygenerovani selectu
		$tableDocumentations = new Document_Model_Documentations();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$nameDocumentations = $tableDocumentations->info("name");
		$nameSubsidiaries = $tableSubsidiaries->info("name");
		
		$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$subSelect->where("file_id = ?", $this->_data["id"])
					->orWhere("internal_file_id = ?", $this->_data["id"]);
		
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$select->from($nameDocumentations)->where(implode("", $subSelect->getPart(Zend_Db_Select::WHERE)));
		
		if ($cliendId) $select->where("client_id = ?", $cliendId);
		
		$select->joinLeft($nameSubsidiaries, "subsidiary_id = id_subsidiary", 
				array("subsidiary_name" => new Zend_Db_Expr("CONCAT(subsidiary_town, ', ', subsidiary_street)")));
		
		return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll()));
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