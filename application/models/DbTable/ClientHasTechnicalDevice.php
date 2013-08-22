<?php
class Application_Model_DbTable_ClientHasTechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'client_has_technical_device';
	
	protected $_referenceMap = array(
		'Client' => array(
			'columns' => array('id_client'),
			'refTableClass' => 'Application_Model_DbTable_Client',
			'refColumns' => array('id_client'),
		),
		'TechnicalDevice' => array(
			'columns' => array('id_technical_device'),
			'refTableClass' => 'Application_Model_DbTable_TechnicalDevice',
			'refColumns' => array('id_technical_device'),
		),
	);
	
	public function addRelation($clientId, $technicalDeviceId){
		try{
			$data['id_client'] = $clientId;
			$data['id_technical_device'] = $technicalDeviceId;
			$this->insert($data);
		}
		catch(Exception $e){
			//pokud už tam je, tak to tam znova vkládat nepotřebuju
		}
	}
	
	public function updateRelation($clientId, $oldId, $newId){
		try{
			$data['id_client'] = $clientId;
			$data['id_technical_device'] = $newId;
			$this->update($data, "id_technical_device = $oldId");
		}
		catch(Exception $e){
			$this->delete("id_client = $clientId AND id_technical_device = $oldId");
		}
	}
	
	public function removeRelation($clientId, $technicalDeviceId){
		$this->delete(array(
				'id_client = ?' => $clientId,
				'id_technical_device = ?' => $technicalDeviceId,
				));
	}
	
}