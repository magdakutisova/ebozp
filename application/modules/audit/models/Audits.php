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
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 * @param Zend_Date $date datum provedeni auditu
	 * @param bool $isCheck prepinac audit - proverka 
	 * @param array $responsibiles seznam zodpovednych
	 * @return Audit_Model_Row_Audit
	 */
	public function createAudit(Zend_Db_Table_Row_Abstract $auditor, 
			Zend_Db_Table_Row_Abstract $subsidiary,
			Zend_Date $doneAt,
			$isCheck,
			$contactPersonId) 
	{
		// vytvoreni zaznamu
		$retVal = $this->createRow(array(
				"client_id" => $subsidiary->client_id,
				"subsidiary_id" => $subsidiary->id_subsidiary,
				"auditor_id" => $auditor->id_user,
				"auditor_name" => $auditor->username,
				"contactperson_id" => $contactPersonId,
				"done_at" => $doneAt->get("y-MM-dd HH-mm-ss"),
				"is_check" => $isCheck
		));
		
		// zapis posledni modifikace a ulozeni dat
		$retVal->modified_at = new Zend_Db_Expr("NOW()");
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * najde audity podle zadanych podminek
	 * 
	 * @param Zend_Db_Table_Row_Abstract $auditor auditor
	 * @param Zend_Db_Table_Row_Abstract $client klient
	 * @param Zend_Db_Table_Row_Abstract $subsidiary pobocka
	 * @param array $order razeni
	 * @return Audit_Model_Rowset_Audits
	 */
	public function getAudit(Zend_Db_Table_Row_Abstract $auditor = null,
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
	
	public function findAudits($clientId, $subsidiaryId = null, $closedOnly = false) {
		$where = array("$this->_name.client_id = ?" => $clientId);
		
		if (!is_null($subsidiaryId)) $where["subsidiary_id = ?"] = $subsidiaryId;
		
		if ($closedOnly) $where[] = "is_closed";
		
		$select = $this->prepareSelect($where);
		$select->order(array("is_closed DESC", "auditor_confirmed_at DESC", "done_at"));
		
		$data = $select->query()->fetchAll();
		
		return new Zend_Db_Table_Rowset(array("data" => $data));
	}
	
	/**
	 * pripravi vyhledavaci dotaz pro nacteni informaci o auditech
	 */
	public function prepareSelect(array $where = array()) {
		// priprava selectu
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$select->from($this->_name);
		
		// navazani na technika
		$tableUsers = new Application_Model_DbTable_User();
		$nameUser = $tableUsers->info("name");
		
		$select->joinLeft(array("auditors" => $nameUser), "auditors.id_user = auditor_id", array("auditor_name" => "auditors.name"));
		
		// propojeni s pobockami
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$nameSubsidiaries = $tableSubsidiaries->info("name");
		
		$select->joinInner($nameSubsidiaries, "id_subsidiary = subsidiary_id", array("subsidiary_name", "subsidiary_street", "subsidiary_town"));
		
		// propojeni s klienty
		$tableClients = new Application_Model_DbTable_Client();
		$nameClients = $tableClients->info("name");
		
		$select->joinInner($nameClients, "$this->_name.client_id = $nameClients.id_client", array("company_name"));
		
		// zapis podminek
		foreach ($where as $cond => $val) {
			if (is_numeric($cond)) {
				$select->where($val);
			} else {
				$select->where($cond, $val);
			}
		}
		
		return $select;
	}
}