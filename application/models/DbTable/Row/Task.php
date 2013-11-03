<?php
class Application_Model_DbTable_Row_Task extends Zend_Db_Table_Row_Abstract {
	
	public function createComment($comment, $userId) {
		$tableComments = new Application_Model_DbTable_TaskComment();
		$retVal = $tableComments->createRow(array(
				"task_id" => $this->id,
				"comment" => $comment,
				"user_id" => $userId
				));
		
		$retVal->save();
		
		return $retVal;
	}
	
	public function findComments() {
		// sestaveni dotazu
		$tableUsers = new Application_Model_DbTable_User();
		$tableComments = new Application_Model_DbTable_TaskComment();
		
		$select = new Zend_Db_Select($this->getTable()->getAdapter());
		
		$select->from($tableComments->info("name"))->where("task_id = ?", $this->id)->order("created_at");
		
		// pripojeni uzivatelu
		$select->joinInner($tableUsers->info("name"), "id_user = user_id", array("name"));
		
		return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll()));
	}
}