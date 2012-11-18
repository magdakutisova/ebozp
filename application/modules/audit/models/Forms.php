<?php
class Audit_Model_Forms extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_forms";
	
	protected $_primary = "questionary_id";
	
	protected $_sequence = false;
	
	protected $_referenceMap = array(
			"questionary" => array(
					"columns" => "questionary_id",
					"refTableClass" => "Questionary_Model_Questionaries",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_Form";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Forms";
	
	/**
	 * zavede novy formular z dotazniku
	 * 
	 * @param string $name jmeno formulare
	 * @param Questionary_Model_Row_Questionary $questionary dotaznik
	 * @return Audit_Model_Row_Form
	 */
	public function createForm($name, Questionary_Model_Row_Questionary $questionary) {
		// x - navratova hodnota
		$x = $this->createRow(array(
				"name" => $name,
				"questionary_id" => $questionary->id
		));
		
		$x->save();
		
		return $x;
	}
	
	/**
	 * najde formular podle id
	 * 
	 * @param int $id identifikacni cislo formulare/dotazniku
	 * @return Audit_Model_Row_Form
	 */
	public function findById($id) {
		return $this->find($id)->current();
	}
}