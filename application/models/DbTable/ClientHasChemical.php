<?php
class Application_Model_DbTable_ClientHasChemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'client_has_chemical';
	
	protected $_referenceMap = array(
		'Client' => array(
			'columns' => array('id_client'),
			'refTableClass' => 'Application_Model_DbTable_Client',
			'refColumns' => array('id_client'),
		),
		'Chemical' => array(
			'columns' => array('id_chemical'),
			'refTableClass' => 'Application_Model_DbTable_Chemical',
			'refColumns' => array('id_chemical'),
		),
	);
	
	public function addRelation($clientId, $chemicalId){
		try{
			$data['id_client'] = $clientId;
			$data['id_chemical'] = $chemicalId;
			$this->insert($data);
		}
		catch (Exception $e){
			//pokud už existuje, tak to tam znova vkládat nepotřebuju
		}
	}
	
	public function updateRelation($clientId, $oldId, $newId){
		try{
			$data['id_client'] = $clientId;
			$data['id_chemical'] = $newId;
			$this->update($data, "id_chemical = $oldId");
		}
		catch(Exception $e){
			//už to tam je ale musím vymazat aspoň starý záznam
			$this->delete("id_client = $clientId AND id_chemical = $oldId");
		}
	}
	
	public function removeRelation($clientId, $chemicalId){
		$this->delete(array(
				'id_client = ?' => $clientId,
				'id_chemical = ?' => $chemicalId,
				));
	}
	
}