<?php
class Audit_Model_Row_AuditForm extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci audit 
	 * 
	 * @return Audit_Model_Row_Audit
	 */
	public function getAudit() {
		return $this->findParentRow("Audit_Model_Audits", "audit");
	}
	
	/**
	 * vraci formular
	 * 
	 * @return Audit_Model_Row_Form
	 */
	public function getForm() {
		return $this->findParentRow("Audit_Model_Forms", "form");
	}
	
	/**
	 * vraci seznam skupin ve formulari
	 */
	public function getGroups() {
		$tableCategories = new Audit_Model_FormsCategories();
		$where = array("!is_deleted", "form_id = ?" => $this->_data["form_id"]);
		
		return $tableCategories->fetchAll($where, "position");
	}
	
	/**
	 * vraci seznam prirazenych zaznamu
	 * 
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function getRecords($group = null, $order = null) {
		// sestaveni vyhledavaciho dotazu
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		
		$nameRecords = $tableRecords->info("name");
		$nameQuestions = $tableQuestions->info("name");
		
		$select = new Zend_Db_Select($this->_table->getAdapter());
		
		$select->from($nameRecords, array("score", "note", "mistake_id", "id"))
				->where("audit_form_id = ?", $this->_data["id"])
				->joinInner($nameQuestions, "$nameQuestions.id = $nameRecords.question_id", array("question", "weight", "group_id"))
				->order(is_null($order) ? "position" : $order);
		
		if ($group) $select->where("group_id = ?", $group->id);
		
		return new Zend_Db_Table_Rowset(array("data" => $select->query()->fetchAll()));
		
		$tableRecords = new Audit_Model_AuditsRecords();
		$where = array("audit_form_id = ?" => $this->id);
		
		// kontrola omezeni skupinou
		if ($group) {
			// vytvoreni filtracniho poddotazu
			$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
			$nameQuestions = $tableQuestions->info("name");
			
			$subSql = "select id from $nameQuestions where group_id = " . $group->id;
			$where["question_id in (?)"] = new Zend_Db_Expr($subSql);
		}
		
		return $tableRecords->fetchAll($where);
	}
}