<?php
class Audit_Model_FormsCategories extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_forms_categories";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"form" => array(
					"columns" => "form_id",
					"refTableClass" => "Audit_Model_Forms",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_FormCategory";
	
	protected $_rowsetClass = "Audit_Model_Rowset_FormsCategories";
	
	/**
	 * vytvori novou kategorii
	 * 
	 * @param string $name jmeno kategorie
	 * @param Audit_Model_Row_Form $form formular, kteremu nalezi
	 * @return Audit_Model_Row_FormCategory
	 */
	public function createCategory($name, Audit_Model_Row_Form $form) {
		// zjisteni maximalni pozice
		$sql = "select max(position) as m from " . $this->_name . " where form_id = " . $form->id;
		$position = $this->getAdapter()->query($sql)->fetchColumn() + 1;
		
		$retVal = $this->createRow(array(
				"name" => $name,
				"form_id" => $form->id,
				"position" => $position
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vraci kategorie dle formulare
	 * 
	 * @param Audit_Model_Row_Form $form formular
	 * @param string $order razeni
	 * @return Audit_Model_Rowset_FormsCategories
	 */
	public function getByForm(Audit_Model_Row_Form $form, $order = null) {
		return $this->fetchAll("questionary_id = " . $form->questionary_id, $order);
	}
	
	/**
	 * vraci kategorii dle id
	 * 
	 * @param int $id identifikacni cislo
	 * @return Audit_Model_Row_FormCategory
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
	
	/**
	 * vraci formular vlasntici kategorii
	 * 
	 * @return Audit_Model_Row_Form
	 */
	public function getForm() {
		$tableForms = new Audit_Model_Forms();
		
		return $tableForms->find($this->questionary_id)->current();
	}
}