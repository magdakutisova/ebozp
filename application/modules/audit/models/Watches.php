<?php
class Audit_Model_Watches extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"client" => array(
					"columns" => array("client_id"),
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => array("id_client")
					),
			
			"subsidiary" => array(
					"columns" => array("subsidiary_id"),
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refColumns" => array("id_subsidiary")
					),
			
			"user" => array(
					"columns" => array("user_id"),
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => array("id_user")
					),
			
			"contact" => array(
					"columns" => array("contactperson_id"),
					"refTableClass" => "Application_Model_DbTable_ContactPerson",
					"refColumns" => "id_contact_person"
					)
	);
	
	protected $_rowClass = "Audit_Model_Row_Watch";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Watches";
	
	/**
	 * nacte dohlidku dle id
	 * @param int $id id dohldiky
	 * @return Audit_Model_Row_Watch
	 */
	public function findById($id) {
		return $this->find($id)->current();
	}
	
	/**
	 * najde rozsirene informace o dohlidce
	 * filtruje dle klienta a pripadne dle pobocky
	 * 
	 * @param int $clientId
	 * @param int $subsidiaryId
	 * @return Audit_Model_Rowset_Watches
	 */
	public function findWatches($clientId, $subsidiaryId = -1) {
		$tableUsers = new Application_Model_DbTable_User();
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$nameUsers = $tableUsers->info("name");
		$nameContacts = $tableContacts->info("name");
		
		// sestaveni selectu
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from($this->_name);
		
		// asociace na technika a na kontaktni osobu
		$select->joinLeft($nameContacts, "contactperson_id = id_contact_person", array("name", "email", "phone"))->joinLeft($nameUsers, "user_id = id_user");
		
		// podminky
		$select->where("$this->_name.client_id = ?", $clientId);
		
		if ($subsidiaryId != -1) {
			$select->where("$this->_name.subsidiary_id = ?", $subsidiaryId);
		}
		
		// navrat dat
		return new Audit_Model_Rowset_Watches(array("data" => $select->query()->fetchAll(), "rowClass" => $this->_rowClass, "table" => $this));
	}
}