<?php
class Application_Model_DbTable_TechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'technical_device';
	
	public function getTechnicalDevice($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_technical_device = ' . $id);
		if(!$row){
			throw new Exception("Technické zařízení $id nebylo nalezeno.");
		}
		$technicalDevice = $row->toArray();
		return new Application_Model_TechnicalDevice($technicalDevice);
	}
	
	public function addTechnicalDevice(Application_Model_TechnicalDevice $technicalDevice){
		$data = $technicalDevice->toArray();
		$technicalDeviceId = $this->insert($data);
		return $technicalDeviceId;
	}
	
	public function updateTechnicalDevice(Application_Model_TechnicalDevice $technicalDevice){
		$data = $technicalDevice->toArray();
		$this->update($data, 'id_technical_device = ' . $technicalDevice->getIdTechnicalDevice());
	}
	
	public function deleteTechnicalDevice($id){
		$this->delete('id_technical_device = ' . (int)$id);
	}
	
}