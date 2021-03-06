<?php
class Document_Model_Records extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_records";
	
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
	
	protected $_rowClass = "Document_Model_Row_Record";
	
	protected $_rowsetClass = "Document_Model_Rowset_Records";
	
	public function createSlot($name, $clientId, $subsidiaryId, $comment = null, $internalComment = null) {
		$retVal = $this->createRow(array(
				"client_id" => $clientId,
				"name" => $name,
				"subsidiary_id" => $subsidiaryId,
				"comment" => $comment,
				"comment_internal" => $internalComment
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	public function getDocumentation($clientId, $subsidiaryId = null, $withCentral = false, $categoryId = null) {
		// sestaveni zakladniho dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from(array("rec" => $this->_name));
		
		$select->where("rec.client_id = ?", $clientId)
						->order("rec.name");

		if (is_null($categoryId)) {
			$select->where("category_id is null");
		} else {
			$select->where("category_id = ?", $categoryId);
		}

		
		if (!is_null($subsidiaryId)) {
			// vyhodnoceni subid
			switch ($subsidiaryId) {
				case -1:
					// vraci se vse
					break;
					
				case 0:
					// vraci se pouze centralni
					$select->where("rec.subsidiary_id IS NULL ");
					break;
					
				default:
					// vraci se pobockova
					if ($withCentral) {
						$subSelect = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
						
						// zapis podminky pobocky a centralni
						$subSelect->where("rec.subsidiary_id = ?", $subsidiaryId)
									->orWhere("rec.subsidiary_id IS NULL");
						
						// zapis do puvodniho selectu
						$select->where(implode(" ", $subSelect->getPart(Zend_Db_Select::WHERE)));
					} else {
						$select->where("rec.subsidiary_id = ?", $subsidiaryId);
					}
					
					break;
					
			}
		}
		
		// jmena tabulek
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableFiles = new Document_Model_Files();
		
		$nameSubsidiaries = $tableSubsidiaries->info("name");
		$nameFiles = $tableFiles->info("name");
		
		// propojeni s pobockou
		$select->joinLeft(array("s" => $nameSubsidiaries), "rec.subsidiary_id = s.id_subsidiary", array("subsidiary_name" => new Zend_Db_Expr("CONCAT(subsidiary_street, ' ', subsidiary_town)")));
		
		// propojeni asociaci na soubory
		$select->joinLeft(array("file" => $nameFiles), "file.id = rec.file_id", array("filename" => "name", "fileid" => "id"));
		$select->joinLeft(array("file2" => $nameFiles), "file2.id = internal_file_id", array("i_filename" => "name", "i_fileid" => "id"));
		
		$result = $this->getAdapter()->query($select);
		
		return new Document_Model_Rowset_Documentations(array("data" => $result->fetchAll(), "table" => $this));
	}
}