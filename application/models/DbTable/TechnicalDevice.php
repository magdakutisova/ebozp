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
	public function getSorts($clientId){
		$select = $this->select()
			->from('technical_device')
			->join('client_has_technical_device', 'technical_device.id_technical_device = client_has_technical_device.id_technical_device', array())
			->where('client_has_technical_device.id_client = ?', $clientId)
			->order('technical_device.sort')
			->group('technical_device.sort');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		$technicalDevices[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				if($result->sort != ''){
					$key = $result->id_technical_device;
					$technicalDevices[$key] = $result->sort;
				}
			}
		}
		return $technicalDevices;
	}
	
	/****************************************************************
	 * Vrací seznam ID - typ techniky.
	 */
	public function getTypes($clientId){
		$select = $this->select()
			->from('technical_device')
			->join('client_has_technical_device', 'technical_device.id_technical_device = client_has_technical_device.id_technical_device')
			->where('client_has_technical_device.id_client = ?', $clientId)
			->order('technical_device.type')
			->group('technical_device.type');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		$technicalDevices[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				if($result->type != ''){
					$key = $result->id_technical_device;
					$technicalDevices[$key] = $result->type;
				}
			}
		}
		return $technicalDevices;
	}
	
	public function getByWorkplace($workplaceId){
		$select = $this->select()
			->from('technical_device')
			->join('workplace_has_technical_device')
			->where('id_workplace = ?', $workplaceId);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function existsTechnicalDevice($sort, $type){
		$select = $this->select()
			->from('technical_device')
			->where('sort = ?', $sort)
			->where('type = ?', $type);
		$results = $this->fetchAll($select);
		if(count($results) > 0){
			return $results->current()->id_technical_device;
		}
		else{
			return 0;
		}
	}
	
	private function process($result){
		if ($result->count()){
			$technicalDevices = array();
			foreach($result as $technicalDevice){
				$technicalDevice = $result->current();
				$technicalDevices[] = $this->processTechnicalDevice($technicalDevice);
			}
			return $technicalDevices;
		}
	}
	
	private function processTechnicalDevice($technicalDevice){
		$data = $technicalDevice->toArray();
		return new Application_Model_TechnicalDevice($data);
	}
	
}