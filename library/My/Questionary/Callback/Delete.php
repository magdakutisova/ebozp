<?php
class My_Questionary_Callback_Delete extends My_Questionary_Callback_Abstract {

	public function callback($questionary, $row=null, array $params = array()) {
		// nacteni informaci z databaze a overeni prislusnosti
		$tableClients = new Application_Model_DbTable_QuestClients();
		$record = $tableClients->fetchRow(array("filled_id = ?" => $row->id));

	}
}