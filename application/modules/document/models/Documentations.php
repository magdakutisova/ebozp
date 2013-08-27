<?php
class Document_Model_Documentations extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_documentations";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => "id_client"
			),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refColumns" => "id_subsidiary"
			),
			
			"file" => array(
					"columns" => "file_id",
					"refTableClass" => "Document_Model_Files",
					"refColumns" => "id"
					),
			
			"internal" => array(
					"columns" => "internal_file_id",
					"refTableClass" => "Document_Model_Files",
					"refColumns" => "id"
					)
	);
	
	protected $_rowClass = "Document_Model_Row_Documentation";
	
	protected $_rowsetClass = "Document_Model_Rowset_Documentations";
	
	public function createSlot($name, $clientId, $subsidiaryId) {
		$retVal = $this->createRow(array(
				"client_id" => $clientId,
				"name" => $name,
				"subsidiary_id" => $subsidiaryId
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	public function getDocumentation($clientId, $subsidiaryId = null) {
		// sestaveni zakladniho dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from(array("doc" => $this->_name));
		
		$select->where("doc.client_id = ?", $clientId);
		
		if (!is_null($subsidiaryId)) {
			// vyhodnoceni subid
			switch ($subsidiaryId) {
				case -1:
					break;
					
				case 0:
					$select->where("doc.subsidiary_id IS NULL ");
					break;
					
				default:
					$select->where("doc.subsidiary_id = ?", $subsidiaryId);
					break;
					
			}
		}
		
		// jmena tabulek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableFiles = new Document_Model_Files();
		
		$nameSubsidiaries = $tableSubsidiaries->info("name");
		$nameFiles = $tableFiles->info("name");
		
		// propojeni s pobockou
		$select->joinLeft(array("s" => $nameSubsidiaries), "doc.subsidiary_id = s.id_subsidiary", array("subsidiary_name"));
		
		// propojeni asociaci na soubory
		$select->joinLeft(array("file" => $nameFiles), "file.id = doc.file_id", array("filename" => "name", "fileid" => "id"));
		$select->joinLeft(array("file2" => $nameFiles), "file2.id = internal_file_id", array("i_filename" => "name", "i_fileid" => "id"));
		
		$result = $this->getAdapter()->query($select);
		
		return new Document_Model_Rowset_Documentations(array("data" => $result->fetchAll(), "table" => $this));
	}
}