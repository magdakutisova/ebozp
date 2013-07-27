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
}