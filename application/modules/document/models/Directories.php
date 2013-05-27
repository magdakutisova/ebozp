<?php
class Document_Model_Directories extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_directories";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"parent" => array(
					"columns" => "parent_id",
					"refTableClass" => "Document_Model_Directories",
					"refColumns" => "id"
			),
			
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_Client",
					"refColumns" => "id_client"
			),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_Subsidiary",
					"refColumns" => "id_subsidiary"
			)
	);
	
	protected $_rowClass = "Document_Model_Row_Directory";
	
	protected $_rowsetClass = "Document_Model_Rowset_Directories";
	
	public function createRoot($clientId, $name) {
		$retVal = $this->createRow(array(
				"name" => $name, 
				"client_id" => $clientId,
				"left_id" => 0,
				"right_id" => 1,
				"is_home" => 1));
		
		$retVal->save();
		$retVal->root_id = $retVal->id;
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * nacte korenovy adresar klienta
	 * 
	 * @param int $clientId id klienta
	 * @return Document_Model_Row_Directory
	 */
	public function root($clientId) {
		return $this->fetchRow(array("client_id = " . $this->getAdapter()->quote($clientId), "is_home"));
	}
	
	/**
	 * vraci korenove adresare (standardne bude jeden, ale jen pro jistotu)
	 * 
	 * @return Document_Model_Rowset_Directories
	 */
	public function roots() {
		return $this->fetchAll("parent_id is null", "name");
	}
}