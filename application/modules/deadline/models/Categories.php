<?php
class Deadline_Model_Categories extends Zend_Db_Table_Abstract {
	
	protected $_primary = "id";
	
	protected $_name = "deadline_categories";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"parent" => array(
					"columns" => "parent_id",
					"refTableClass" => "Deadline_Model_Categories",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Deadline_Model_Row_Category";
	
	/**
	 * vraci bazove kategorie
	 */
	public function bases() {
		return $this->fetchAll("parent_id IS NULL", "name");
	}
	
	/**
	 * vytvori novou kategorii
	 * 
	 * @param string $name jmeno kategorie
	 * @param Deadline_Model_Row_Category $parent rodicovska kategorie
	 * @param int $period vychozi perioda
	 */
	public function createCategory($name, $parent = null, $period = null) {
		$retVal = $this->createRow(array(
				"name" => $name,
				"period" => $period
				));
		
		if (!is_null($parent)) {
			$retVal->parent_id = $parent->id;
			$retVal->depth = $parent->depth + 1;
		}
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * nacte vsechna data a vraci jejich hierarchicke usporadani
	 * 
	 * @return array
	 */
	public function findAll() {
		// nacteni vsech dat a serazeni dle id
		$dataAll = $this->fetchAll(null, array("parent_id", "name"));
		
		// priprava indexu a navratove hodnoty
		$index = array();
		$retVal = array();
		
		// prochazeni a sestaveni dat
		foreach ($dataAll as $item) {
			// vygenerovani zaznamu
			$record = (object) $item->toArray();
			$record->children = array();
			
			// zaindexovani a zapis do spravneho predka
			$index[$item->id] = $record;
			
			$itemIndex = $item->value ? $item->value : $item->name;
			
			if ($item->parent_id) {
				$index[$item->parent_id]->children[$itemIndex] = $record;
			} else {
				$retVal[$itemIndex] = $record;
			}
		}
		
		return $retVal;
	}
	
	/**
	 * vraci kategorii dle id
	 * 
	 * @param int $categoryId identifikacni cislo kategorie
	 * @return Deadline_Model_Row_Category
	 */
	public function findById($categoryId) {
		return $this->find($categoryId)->current();
	}
}