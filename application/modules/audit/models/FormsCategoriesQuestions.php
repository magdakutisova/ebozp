<?php
class Audit_Model_FormsCategoriesQuestions extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_forms_categories_questions";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"category" => array(
					"columns" => "group_id",
					"refTableClass" => "Audit_Model_FormsCategories",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_FormCategoryQuestion";
	
	protected $_rowsetClass = "Audit_Model_Rowset_FormsCategoriesQuestions";
	
	/**
	 * vytvori novou otazku v kategorii
	 * 
	 * @param Audit_Model_Row_FormCategory $questionCategory kategorie otazek
	 * @param Questionary_Model_Row_QuestionaryItem $item prvek dotazniku s otazkou
	 * @param string $category kategorie
	 * @param string $subcategory podkategorie
	 * @param string $concretisation specifikace
	 * @param string $mistake neshoda
	 * @param string $suggestion navrh opatreni
	 * @return Audit_Model_Row_FormCategoryQuestion
	 */
	public function createQuestion(Audit_Model_Row_FormCategory $questionCategory, array $data) {
		
		$retVal = $this->createRow(array(
				"group_id" => $questionCategory->id,
				"weight" => $data["weight"],
				"question" => $data["question"],
				"farplan_text" => $data["farplan_text"],
				"category" => $data["category"],
				"subcategory" => $data["subcategory"],
				"concretisation" => $data["concretisation"],
				"mistake" => $data["mistake"],
				"suggestion" => $data["suggestion"]
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vraci seznam otazek v kategorii
	 * 
	 * @param Audit_Model_Row_FormCategory $category
	 * @return Audit_Model_Rowset_FormsCategoriesQuestions
	 */
	public function getByCategory(Audit_Model_Row_FormCategory $category) {
		return $this->fetchAll("group_id = " . $category->id, "id");
	}
	
	/**
	 * vraci otazku podle id
	 * 
	 * @param int $id id otazky
	 * @return Audit_Model_Row_FormCategoryQuestion
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
}