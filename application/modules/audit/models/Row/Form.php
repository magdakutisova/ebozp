<?php
class Audit_Model_Row_Form extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * naklonuje formular a vraci radek kopie
	 * 
	 * @param string $name jmeno noveho formulare
	 */
	public function cloneForm($name) {
		$tableForms = new Audit_Model_Forms();
		$tableCategories = new Audit_Model_FormsCategories();
		$tableQuestoins = new Audit_Model_FormsCategoriesQuestions();
		
		$nameQuestions = $tableQuestoins->info("name");
		
		// zahajeni transkace
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$adapter->beginTransaction();
		
		$retVal = $tableForms->createForm($name);
		$retVal->save();
		
		// prekopirovani kategorii
		$categories = $tableCategories->fetchAll(array("form_id = ?" => $this->id, "!is_deleted"), "position");
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		foreach ($categories as $category) {
			// prekopirovani kateogrie
			$arrInfo = $category->toArray();
			unset($arrInfo["id"]);
			$arrInfo["form_id"] = $retVal->id;
			
			$newCategory = $tableCategories->createRow($arrInfo);
			$newCategory->save();
			
			// vyhledavaci poddotaz
			$select = new Zend_Db_Select($adapter);
			$select->from($nameQuestions, array(
					new Zend_Db_Expr($newCategory->id),
					"position",
					"question",
					"weight",
					"category",
					"subcategory",
					"concretisation",
					"mistake",
					"suggestion"
					));
			
			$select->where("group_id = ?", $category->id)->where("!is_deleted")->where("new_id is null");
			
			// prekopirovani otazek v kategorii
			$sql = "insert into $nameQuestions (group_id, position, question, weight, category, subcategory, concretisation, mistake, suggestion) " . $select->assemble();
			$adapter->query($sql);
		}
		
		$adapter->commit();
		
		return $retVal;
	}
	
	/**
	 * vraci seznam kategorii
	 * 
	 * @return Audit_Model_Rowset_Categories
	 */
	public function findCategories() {
		$tableCategories = new Audit_Model_FormsCategories();
		
		return $tableCategories->fetchAll(array("form_id = ?" => $this->_data["id"], "!is_deleted"), "position");
	}
}