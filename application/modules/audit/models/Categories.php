<?php
class Audit_Model_Categories extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_categories";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"parent" => array(
					"columns" => "parent_id",
					"refTableClass" => "Audit_Model_Categories",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_Category";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Categories";
	
	/**
	 * vytvori novou kategorii
	 * 
	 * @param string $name jmeno nove kategorie
	 * @param Audio_Model_Row_Category $parent pripadny predek
	 * @return Audit_Model_Row_Category
	 */
	public function createCategory($name, Audit_Model_Row_Category $parent = null) {
		// navratova hodnota
		$retVal = $this->createRow(array(
				"name" => $name
		));
		
		// kontrola predka
		if ($parent) {
			$retVal->parent_id = $parent->id;
		}
		
		// ulozeni do DB a vraceni
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * najde kategorii podle id
	 * 
	 * @param int $id identifikacni cislo kategorie
	 * @return Audit_Model_Row_Category
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
	
	/**
	 * vraci korenove kategorie
	 * 
	 * @param string $order razeni vysledku
	 * @return Audit_Model_Rowset_Categories
	 */
	public function getRoots($order = null) {
		return $this->fetchAll("`parent_id` is null", $order);
	}
}