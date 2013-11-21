<?php
class Audit_Model_Farplans extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_farplans";
	
	protected $_sequence = true;
	
	protected $_primary = array("id");
	
	protected $_rowClass = "Audit_Model_Row_Farplan";
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
					)
			);
	
	/**
	 * vytvori z formulare farplan
	 * 
	 * @param Audit_Model_Row_Form $form puvodni formular
	 * @param Audit_Model_Row_Audit $audit radek auditu
	 */
	public function cloneForm(Audit_Model_Row_Form $form, Audit_Model_Row_Audit $audit) {
		// vytvoreni farplanu
		$farplan = $this->createRow(array(
				"audit_id" => $audit->id,
				"form_id" => $form->id,
				"name" => $form->name
				));
		
		$farplan->save();
		
		// nacteni a vytvoreni kategorii
		$tableCategories = new Audit_Model_FarplansCategories();
		$tableTexts = new Audit_Model_FarplansTexts();
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		
		$nameQuestions = $tableQuestions->info("name");
		$nameTexts = $tableTexts->info("name");
		
		$categories = $form->findCategories();
		
		foreach ($categories as $category) {
			// vytvoreni kategorie farplanu
			$farCat = $tableCategories->createRow(array(
					"farplan_id" => $farplan->id,
					"name" => $category->name
					));
			
			$farCat->save();
			
			// nakopirovani otazek
			$select = new Zend_Db_Select($this->getAdapter());
			$select->from($nameQuestions, array("farplan_text", new Zend_Db_Expr($farCat->id)));
			$select->where("group_id = ?", $category->id)->where("new_id IS NULL")->where("!is_deleted")->order("position");
			
			// sestaveni vkladaciho SQL dtoazu
			$sql = sprintf("insert into %s (`text`, category_id) %s", $nameTexts, $select->assemble());
			$this->getAdapter()->query($sql);
		}
		
		return $farplan;
	}
	
	/**
	 * @return Audit_Model_Row_Farplan
	 * @param unknown_type $id
	 */
	public function findById($id) {
		return $this->find($id)->current();
	}
}