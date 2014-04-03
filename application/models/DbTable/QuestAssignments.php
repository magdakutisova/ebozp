<?php
class Application_Model_DbTable_QuestAssignments extends Zend_Db_Table_Abstract {

	const TYPE_UNUSED = 0;
	const TYPE_CLIENT = 1;
	const TYPE_SUBSIDIARY = 2;
	const TYPE_WORKPLACE = 3;
	const TYPE_POSITION = 4;
	const TYPE_EMPLOYEE = 5;

	protected $_name = "quest_assignments";

	protected $_primary = "id";

	protected $_squence = true;

	protected $_referenceMap = array(
		"client" => array(
			"columns" => "client_id",
			"refTableClass" => "Application_Model_DbTable_Client",
			"refColumns" => "id_client"
		),
		"questionary" => array(
			"columns" => "questionary_id",
			"refTableClass" => "Questionary_Model_Questionaries",
			"refColumns" => "id"
		)
	);

	/**
	 * najde zaznamy dle identifikatoru klienta
	 * @param int $clientId identifikacni cislo klienta
	 * @param int $type typ formulare
	 * @return Zend_Db_Table_Abstract
	 */
	public function findByClient($clientId, $type = null) {
		$select = $this->prepareSelect($clientId);

		// kontrola typu
		if (!is_null($type)) {
			$select->where("assign_type = ?", $type);
		}

		$data = $select->query()->fetchAll();

		return new Zend_Db_Table_Rowset(array("data" => $data, "stored" => true));
	}

	/**
	 * prirpavy vyhledavaci dotaz
	 * @return Zend_Db_Select
	 */
	public function prepareSelect($clientId) {
		$tableQuestionaries = new Questionary_Model_Questionaries();
		$select = new Zend_Db_Select($this->getAdapter());

		$select->from(array("q" => $tableQuestionaries->info("name")), array("questionary_id" => "q.id", "name"));

		$select->joinLeft(array("a" => $this->_name), "a.questionary_id = q.id and client_id = $clientId", array(
			"assign_type" => new Zend_Db_Expr("IFNULL(a.assign_type, 0)")
		));

		return $select;
	}
}