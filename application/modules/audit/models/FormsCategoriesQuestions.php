<?php
class Audit_Model_FormsCategoriesQuestions extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_forms_categories_questions";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"category" => array(
					"columns" => "category_id",
					"refTableClass" => "Audit_Model_FormsCategories",
					"refColumns" => "id"
			),
			
			"item" => array(
					"columns" => "questionary_item_id",
					"refTableClass" => "Questionary_Model_QuestinariesItems",
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
	public function createQuestion(Audit_Model_Row_FormCategory $questionCategory,
			Questionary_Model_Row_QuestionaryItem $item,
			$category,
			$subcategory,
			$concretisation,
			$mistake,
			$suggestion) {
		
		$retVal = $this->createRow(array(
				"group_id" => $questionCategory->id,
				"questionary_item_id" => $item->id,
				"question" => $item->label,
				"category" => $category,
				"subcategory" => $subcategory,
				"concretisation" => $concretisation,
				"mistake" => $mistake,
				"suggestion" => $suggestion
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