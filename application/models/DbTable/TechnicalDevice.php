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
	
	/****************************************************************
	 * Vrací seznam ID - druh techniky.
	 */
	public function getSorts($subsidiaryId){
		$select = $this->select()
			->from('technical_device')
			->join('position_has_technical_device', 'technical_device.id_technical_device = position_has_technical_device.id_technical_device')
			->join('position', 'position_has_technical_device.id_position = position.id_position')
			->where('subsidiary_id = ?', $subsidiaryId)
			->order('technical_device.sort');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		$technicalDevices[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_technical_device;
				$technicalDevices[$key] = $result->sort;
			}
		}
		return $technicalDevices;
	}
	
	/****************************************************************
	 * Vrací seznam ID - typ techniky.
	 */
	public function getTypes($subsidiaryId){
		$select = $this->select()
			->from('technical_device')
			->join('position_has_technical_device', 'technical_device.id_technical_device = position_has_technical_device.id_technical_device')
			->join('position', 'position_has_technical_device.id_position = position.id_position')
			->where('subsidiary_id = ?', $subsidiaryId)
			->order('technical_device.type');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		$technicalDevices[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_technical_device;
				$technicalDevices[$key] = $result->type;
			}
		}
		return $technicalDevices;
	}
	
}