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
	
	public function updateTechnicalDeviceAtClient(Application_Model_TechnicalDevice $technicalDevice, $clientId){
		$existsTechnicalDevice = $this->existsTechnicalDevice($technicalDevice->getSort(), $technicalDevice->getType());
		$oldId = $technicalDevice->getIdTechnicalDevice();
		$newId = '';
		
		if($existsTechnicalDevice){
			$newId = $existsTechnicalDevice;
		}
		else{
			$technicalDevice->setIdTechnicalDevice(null);
			$newId = $this->addTechnicalDevice($technicalDevice);
		}
		
		$clientHasTechnicalDevice = new Application_Model_DbTable_ClientHasTechnicalDevice();
		$clientHasTechnicalDevice->updateRelation($clientId, $oldId, $newId);
		$positionHasTechnicalDevice = new Application_Model_DbTable_PositionHasTechnicalDevice();
		$positionHasTechnicalDevice->updateRelation($clientId, $oldId, $newId);
		$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
		$workplaceHasTechnicalDevice->updateRelation($clientId, $oldId, $newId);
	}
	
	public function deleteTechnicalDeviceFromClient($id, $clientId){
		$clientHasTechnicalDevice = new Application_Model_DbTable_ClientHasTechnicalDevice();
		$clientHasTechnicalDevice->removeRelation($clientId, $id);
		$positionHasTechnicalDevice = new Application_Model_DbTable_PositionHasTechnicalDevice();
		$positionHasTechnicalDevice->removeAllClientRelations($clientId, $id);
		$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
		$workplaceHasTechnicalDevice->removeAllClientRelations($clientId, $id);
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
	
	/******
	 * Vrací seznam ID - technický prostředek.
	 */
	public function getTechnicalDevices($clientId){
		$select = $this->select()
			->from('technical_device')
			->join('client_has_technical_device', 'technical_device.id_technical_device = client_has_technical_device.id_technical_device')
			->where('client_has_technical_device.id_client = ?', $clientId)
			->order('technical_device.sort')
			->group(array('technical_device.sort', 'technical_device.type'));
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_technical_device;
				$technicalDevices[$key] = $result->sort . ' ' . $result->type;
			}
		}
		return $technicalDevices;
	}
	
	public function getByWorkplace($workplaceId){
		$select = $this->select()
			->from('technical_device')
			->join('workplace_has_technical_device', 'technical_device.id_technical_device = workplace_has_technical_device.id_technical_device')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function getBySubsidiaryWithPositions($subsidiaryId){
		$select = $this->select()
			->from('technical_device')
			->join('workplace_has_technical_device', 'technical_device.id_technical_device = workplace_has_technical_device.id_technical_device')
			->join('workplace', 'workplace_has_technical_device.id_workplace = workplace.id_workplace')
			->where('workplace.subsidiary_id = ?', $subsidiaryId)
			->order(array('technical_device.sort', 'technical_device.type'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			$technicalDevices = array();
			foreach($result as $technicalDevice){
				if($technicalDevice->sort != ''){
					$technicalDevices[$technicalDevice->name][$technicalDevice->id_technical_device]['technical_device'] = $technicalDevice->sort . ' ' . $technicalDevice->type;
					$select = $this->select()
						->from('technical_device')
						->join('position_has_technical_device', 'technical_device.id_technical_device = position_has_technical_device.id_technical_device')
						->join('position', 'position_has_technical_device.id_position = position.id_position')
						->where('technical_device.id_technical_device = ' . $technicalDevice->id_technical_device . ' AND position.subsidiary_id = ' . $subsidiaryId)
						->order('position.position');
					$select->setIntegrityCheck(false);
					$subResult = $this->fetchAll($select);
					if(count($subResult) > 0){
						$technicalDevices[$technicalDevice->name][$technicalDevice->id_technical_device]['positions'] = ', vyskytuje se na pracovních pozicích: ';
						$isFirst = true;
						foreach($subResult as $position){
							if($isFirst){
								$technicalDevices[$technicalDevice->name][$technicalDevice->id_technical_device]['positions'] .= $position->position;
							}
							else{
								$technicalDevices[$technicalDevice->name][$technicalDevice->id_technical_device]['positions'] .= ', ' . $position->position;
							}
							$isFirst = false;
						}
					}
				}
				else{
					$technicalDevices[$technicalDevice->name] = null;
				}
			}
			return $technicalDevices;
		}
		else{
			return null;
		}
	}
	
	public function getBySubsidiaryWithWorkplaces($subsidiaryId){
		$select = $this->select()
			->from('technical_device')
			->join('position_has_technical_device', 'technical_device.id_technical_device = position_has_technical_device.id_technical_device')
			->join('position', 'position_has_technical_device.id_position = position.id_position')
			->where('position.subsidiary_id = ?', $subsidiaryId)
			->order('position.position');
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			$technicalDevices = array();
			foreach($result as $technicalDevice){
				if($technicalDevice->sort != ''){
					$technicalDevices[$technicalDevice->position][$technicalDevice->id_technical_device]['technical_device'] = $technicalDevice->sort . ' ' . $technicalDevice->type;
					$select = $this->select()
						->from('technical_device')
						->join('workplace_has_technical_device', 'technical_device.id_technical_device = workplace_has_technical_device.id_technical_device')
						->join('workplace', 'workplace_has_technical_device.id_workplace = workplace.id_workplace')
						->where('technical_device.id_technical_device = ' . $technicalDevice->id_technical_device . ' AND workplace.subsidiary_id = ' . $subsidiaryId)
						->order(array('technical_device.sort', 'technical_device.type'));
					$select->setIntegrityCheck(false);
					$subResult = $this->fetchAll($select);
					if(count($subResult) > 0){
						$technicalDevices[$technicalDevice->position][$technicalDevice->id_technical_device]['workplaces'] = ', vyskytuje se na pracovištích: ';
						$isFirst = true;
						foreach($subResult as $workplace){
							if($isFirst){
								$technicalDevices[$technicalDevice->position][$technicalDevice->id_technical_device]['workplaces'] .= $workplace->name;
							}
							else{
								$technicalDevices[$technicalDevice->position][$technicalDevice->id_technical_device]['workplaces'] .= ', ' . $workplace->name;
							}
							$isFirst = false;
						}
					}
				}
				else{
					$technicalDevices[$technicalDevice->position] = null;
				}
			}
			return $technicalDevices;
		}
		else{
			return null;
		}
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