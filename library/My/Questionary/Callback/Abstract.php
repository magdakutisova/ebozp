<?php
abstract class My_Questionary_Callback_Abstract implements Questionary_Callback_Interface {

	/**
	 * vraci typ dotazniku v zavislosti na id ulozenem v asociacni tabulce
	 * jedna se o radek z tabulky Application_Model_DbTable_QuestCliets
	 * 
	 * @param Zend_Db_Table_Row_Abstract $filledId identifikacni cislo vyplneneho dotazniku
	 * @param int $clientId identifikacni cislo klienta
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getQuestionaryType($filled, $clientId) {
		// kontrola, zda vazba existuje, pokud ne, vraci se nepouzity dotaznik
		if ($filled->questionary_id == null) return null;

		if (!$clientId) return null;

		// nacteni typu
		$tableClients = new Application_Model_DbTable_QuestClients();
		$item = $tableClients->findByFilledId($filled->id);

		return $item;
	}

	/**
	 * vraci radek obsahujici typ dotaziku z asociacni tabulky dotaznik-klient
	 * @param Zend_Db_Table_Row_Abstract $filled radek vyplneneho dotazniku
	 * @param int $clientId identifikacni cislo klienta
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function getQuestionaryType2($filled, $clientId) {
		// kontrola dat
		if (is_null($clientId) || is_null($filled->questionary_id)) return null;

		// vytvoreni tabulky a nacteni dat
		$tableQuestionaries = new Application_Model_DbTable_QuestAssignments();

		return $tableQuestionaries->fetchRow(array(
			"client_id = ?" => $clientId,
			"questionary_id = ?" => $filled->questionary_id
		));
	}
}