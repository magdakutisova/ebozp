<?php
class Application_Model_DbTable_ContactPerson extends Zend_Db_Table_Abstract{
	
	protected $_name = 'contact_person';
	
	protected $_referenceMap = array ('Subsidiary' => array (
			'columns' => 'subsidiary_id',
			'refTableClass' => 'Application_Model_DbTable_Subsidiary',
			'refColumns' => 'id_subsidiary' ) );
	
	public function getContactPerson($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_contact_person = ' . $id);
		if(!$row){
			throw new Exception("Kontaktní osoba $id nebyla nalezena.");
		}
		$contactPerson = $row->toArray();
		return new Application_Model_ContactPerson($contactPerson);
	}
	
	public function addContactPerson(Application_Model_ContactPerson $contactPerson){
		$data = $contactPerson->toArray();
		$contactPersonId = $this->insert($data);
		return $contactPersonId;
	}
	
	public function updateContactPerson(Application_Model_ContactPerson $contactPerson){
		$data = $contactPerson->toArray();
		$this->update($data, 'id_contact_person = ' . $contactPerson->getIdContactPerson());
	}
	
	public function deleteContactPerson($id){
		$this->delete('id_contact_person = ' . (int)$id);
	}
	
}