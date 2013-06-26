<?php
class Application_Model_DbTable_ContactPerson extends Zend_Db_Table_Abstract{
	
	protected $_name = 'contact_person';
	
	public function getContactPerson($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_contact_person = ' . $id);
		if(!$row){
			throw new Exception("Kontaktní osoba $id nebyla nalezena.");
		}
		$contactPerson = $row->toArray();
		return new Application_Model_ContactPerson($contactPerson);
	}
	
}