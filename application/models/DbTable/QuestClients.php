<?php
class Application_Model_DbTable_QuestClients extends Zend_Db_Table_Abstract {

	protected $_name = "quest_clients";

	protected $_primary = "id";

	protected $_sequence = true;

	protected $_referenceMap = array(
		"filled" => array(
			"columns" => "filled_id",
			"refTableClass" => "Questionary_Model_Filleds",
			"refColumns" => "id"
		),
		"client" => array(
			"columns" => "client_id",
			"refTableClass" => "Application_Model_DbTable_Clients",
			"refColumns" => "id_client"
		)
	);

	/*
	 * nalezne zaznamy dle id klienta
	 * @param int $clientId identifikacni cislo klienta
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function findByClient($clientId) {
		// sestaveni dotazu
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from(array("c" => $this->_name));

		$tableFilleds = new Questionary_Model_Filleds();
		$select->joinInner(array("f" => $tableFilleds->info("name")), "f.id = c.filled_id");

		$select->where("c.client_id = ?", $clientId);

		// nacteni dat
		$data = $select->query()->fetchAll();

		return new Zend_Db_Table_Rowset(array("data" => $data, "stored" => true));
	}

	/*
	 * najde zaznam dle identifikacniho cislo vyplneneho dotazniku
	 * @param int $filledId identifikacni cislo vyplneneho dotazniu
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function findByFilledId($filledId) {
		$retVal = $this->fetchRow(array("filled_id = ?" => $filledId));
		return $retVal;
	}
}