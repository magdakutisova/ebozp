<?php
class Audit_Model_Row_Farplan extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * reference na audit
	 * @var unknown_type
	 */
	private $_audit = null;
	
	public function findAudit() {
		if (!$this->_audit) {
			$this->_audit = $this->findParentRow(new Audit_Model_Audits());
		}
		
		return $this->_audit;
	}
	
	public function findData($onlySelected = false) {
		// nacteni textu a kategorii
		$tableCategories = new Audit_Model_FarplansCategories();
		$tableTexts = new Audit_Model_FarplansTexts();
		
		// nacteni kategorii a jejich indexace
		$where = array("farplan_id = ?" => $this->id);
		
		if ($onlySelected) {
			$where[] = "is_selected";
		}
		
		$categories = $tableCategories->fetchAll($where, "id");
		
		$retVal = array();
		$catIds = array(0);
		
		foreach ($categories as $category) {
			$retVal[$category->id] = array(
					"category" => $category,
					"texts" => array()
					);
			
			$catIds[] = $category->id;
		}
		
		// nacteni otazek a serazeni dle id kategorie a id
		$where = array(
				"category_id in (?)" => $catIds
				);
		
		$texts = $tableTexts->fetchAll($where, array("category_id", "id"));
		
		// zapsani textu
		foreach ($texts as $text) {
			$retVal[$text->category_id]["texts"][] = $text;
		}
		
		return $retVal;
	}
}