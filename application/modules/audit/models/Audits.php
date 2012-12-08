<?php
class Audit_Model_Audits extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"auditor" => array(
					"columns" => "auditor_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
			),
			
			"client" => array(
					"columns" => "client_id",
					"refTableClass" => "Application_Model_DbTable_Client",
					"refColumns" => "id_client"
			),
			
			"coordinator" => array(
					"columns" => "coordinator_id",
					"refTableClass" => "Application_Model_DbTable_User",
					"refColumns" => "id_user"
			),
			
			"filled" => array(
					"columns" => "form_filled_id",
					"refTableClass" => "Questionary_Model_Filleds",
					"refColumns" => "id"
			),
			
			"form" => array(
					"columns" => "form_id",
					"refTableClass" => "Audit_Model_Forms",
					"refColumns" => "questionary_id"
			),
			
			"questionary" => array(
					"columns" => "form_id",
					"refTableClass" => "Questionary_Model_Questionaries",
					"refColumns" => "id"
			),
			
			"subsidiary" => array(
					"columns" => "subsidiary_id",
					"refTableClass" => "Application_Model_DbTable_Subsidiary",
					"refColumns" => "id_subsidiary"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_Audit";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Audits";
	
	/**
	 * zalozi novy audit
	 * 
	 * @param Zend_Db_Table_Row_Abstract $auditor uzivatel auditora
	 * @param Zend_Db_Table_Row_Abstract $coordinator uzivatel koordinatora
	 * @param Audit_Model_Row_Form $form formular auditu
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 * @param Zend_Date $date datum provedeni auditu
	 * @param array $responsibiles seznam zodpovednych
	 * @return Audit_Model_Row_Audit
	 */
	public function createAudit(Zend_Db_Table_Row_Abstract $auditor, 
			Zend_Db_Table_Row_Abstract $coordinator,
			Audit_Model_Row_Form $form,
			Zend_Db_Table_Row_Abstract $subsidiary,
			Zend_Date $doneAt,
			array $responsibiles) 
	{
		// vytvoreni zaznamu
		$retVal = $this->createRow(array(
				"client_id" => $subsidiary->client_id,
				"subsidiary_id" => $subsidiary->id_subsidiary,
				"form_id" => $form->questionary_id,
				"auditor_id" => $auditor->id_user,
				"auditor_name" => $auditor->username,
				"coordinator_id" => $coordinator->id_user,
				"coordinator_name" => $coordinator->username,
				"done_at" => $doneAt->get("y-MM-dd HH-mm-ss")
		));
		
		// vytvoreni instance formulare
		$questionary = $form->getQuestionary();
		
		$tableFilleds = new Questionary_Model_Filleds();
		$filled = $tableFilleds->createFilled($questionary);
		
		$retVal->form_filled_id = $filled->id;
		
		// zapis posledni modifikace a ulozeni dat
		$retVal->modified_at = new Zend_Db_Expr("NOW()");
		
		$retVal->save();
		
		// zapis zodpovednych hodnot
		$tableResp = new Audit_Model_AuditsResponsibiles();
		
		foreach ($responsibiles as $item) {
			$item = trim($item);
			
			$tableResp->insert(array(
					"audit_id" => $retVal->id,
					"name" => $item
			));
		}
		
		return $retVal;
	}
	
	/**
	 * najde audity podle zadanych podminek
	 * 
	 * @param Zend_Db_Table_Row_Abstract $auditor auditor
	 * @param Zend_Db_Table_Row_Abstract $coordinator koordinator
	 * @param Zend_Db_Table_Row_Abstract $client klient
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 * @param array $order razeni
	 * @return Audit_Model_Rowset_Audits
	 */
	public function getAudit(Zend_Db_Table_Row_Abstract $auditor = null,
			Zend_Db_Table_Row_Abstract $coordinator = null,
			Zend_Db_Table_Row_Abstract $client = null,
			Zend_Db_Table_Row_Abstract $subsidiary = null,
			array $order = null) 
	{
		// sestaveni vyhledavacich podminek
		$retVal = array();
		$adapter = $this->getAdapter();
		
		if (!is_null($auditor)) {
			$where[] = "auditor_id = " . $adapter->quote($auditor->id_user);
		}
		
		if (!is_null($coordinator)) {
			$where[] = "coordinator_id = " . $adapter->quote($coordinator->id_user);
		}
		
		if (!is_null($client)) {
			$where[] = "client_id = " . $adapter->quote($client->id_client);
		}
		
		if (!is_null($subsidiary)) {
			$where[] = "subisidiary_id = " . $adapter->quote($subsidiary->id_subsidiary);
		}
		
		$orderAll = null;
		
		if ($order) $orderAll = implode(",", $order);
		
		return $this->fetchAll($where, implode(",", $orderAll));
	}
	
	/**
	 * vraci audit podle id
	 * 
	 * @param int $id identifikacni cislo auditu
	 * @return Audit_Model_Row_Audit
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
}