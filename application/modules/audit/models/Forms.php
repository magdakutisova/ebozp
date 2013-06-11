<?php
class Audit_Model_Forms extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_forms";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_rowClass = "Audit_Model_Row_Form";
	
	protected $_rowsetClass = "Audit_Model_Rowset_Forms";
	
	/**
	 * zavede novy formular z dotazniku
	 * 
	 * @param string $name jmeno formulare
	 * @param Questionary_Model_Row_Questionary $questionary dotaznik
	 * @return Audit_Model_Row_Form
	 */
	public function createForm($name) {
		// x - navratova hodnota
		$x = $this->createRow(array(
				"name" => $name
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