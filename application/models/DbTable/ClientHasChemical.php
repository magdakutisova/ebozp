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
	
}