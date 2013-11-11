<?php
class Application_Model_DbTable_Workplace extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace';
	
	protected $_referenceMap = array(
    	'Subsidiary' => array(
    		'columns' => 'subsidiary_id',
    		'refTableClass' => 'Application_Model_DbTable_Subsidiary',
    		'refColumns' => 'id_subsidiary',
    	),
    	'Client' => array(
    		'columns' => 'client_id',
    		'refTableClass' => 'Application_Model_DbTable_Client',
    		'refColumns' => 'id_client',
    	),
    	'Folder' => array(
    		'columns' => 'folder_id',
    		'refTableClass' => 'Application_Model_DbTable_Folder',
    		'refColumns' => 'id_folder',
    	),
    );
	
	public function getWorkplace($id){
		$id = (int) $id;
		$row = $this->fetchRow('id_workplace = ' . $id);
		if (!$row){
			throw new Exception("Pracoviště $id nebylo nalezeno.");
		}
		$workplace = $row->toArray();
		return new Application_Model_Workplace($workplace);
	}
	
	public function addWorkplace(Application_Model_Workplace $workplace){
		//když neexistuje pracoviště s daným názvem u klienta, vložit a vrátit ID
		$existingWorkplace = $this->existsWorkplaceSub($workplace->getName(), $workplace->getSubsidiaryId());
		if(!$existingWorkplace){
			$data = $workplace->toArray();
			$workplaceId = $this->insert($data);
			return $workplaceId;
		}
		return false;
	}
	
	public function updateWorkplace(Application_Model_Workplace $workplace, $differentName = false){
		$data = $workplace->toArray();
		if($differentName){
			$existingWorkplace = $this->existsWorkplaceSub($workplace->getName(), $workplace->getSubsidiaryId());
		}
		else{
			$existingWorkplace = false;
		}
		if(!$existingWorkplace){
			$this->update($data, 'id_workplace = ' . $workplace->getIdWorkplace());
			return true;
		}
		return false;
	}
	
	public function deleteWorkplace($id){
		$this->delete('id_workplace = ' . (int)$id);
	}
	
	public function getBySubsidiary($subsidiaryId){
		$select = $this->select()
			->from('workplace')
			->where('subsidiary_id = ?', $subsidiaryId);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function getBySubsidiaryWithDetails($subsidiaryId, $incomplete = false){
		if(!$incomplete){
			$select = $this->select()
				->from('workplace')
				->joinLeft('folder', 'workplace.folder_id = folder.id_folder')
				->joinLeft('employee', 'workplace.boss_id = employee.id_employee')
				->where('workplace.subsidiary_id = ?', $subsidiaryId)
				->order(array('folder.folder', 'workplace.name'));
			$select->setIntegrityCheck(false);
			$result = $this->fetchAll($select);
		}
		else{
			$subSelectA = $this->select()
				->distinct()
				->from(array('workplace_has_position'), array('workplace_has_position.id_workplace'));
			$subSelectA->setIntegrityCheck(false);
			$subSelectB = $this->select()
				->distinct()
				->from(array('workplace_has_work'), array('workplace_has_work.id_workplace'));
			$subSelectB->setIntegrityCheck(false);
			$select = $this->select()
				->from('workplace')
				->joinLeft('folder', 'workplace.folder_id = folder.id_folder')
				->joinLeft('employee', 'workplace.boss_id = employee.id_employee')
				->where('workplace.subsidiary_id = ?', $subsidiaryId)
				->where('workplace.business_hours IS NULL OR workplace.id_workplace NOT IN (' . $subSelectA . ') OR workplace.id_workplace NOT IN (' . $subSelectB . ')')
				->order(array('folder.folder', 'workplace.name'));
			$select->setIntegrityCheck(false);
			$result = $this->fetchAll($select);
		}
		
		$workplaces = array();
		$i = 0;
		if($result != null){
			foreach($result as $workplace){
				$workplaces[$i]['workplace'] = $this->processWorkplace($workplace);
				$workplaces[$i]['folder'] = $workplace->folder;
				$workplaces[$i]['boss'] = new Application_Model_Employee($workplace->toArray());
				
				$selectPositions = $this->select()
					->from('position')
					->join('workplace_has_position', 'position.id_position = workplace_has_position.id_position')
					->where('workplace_has_position.id_workplace = ?', $workplaces[$i]['workplace']->getIdWorkplace());
				$selectPositions->setIntegrityCheck(false);
				$positions = $this->fetchAll($selectPositions);
				if($positions != null){
					$j = 0;
					foreach($positions as $position){
						$workplaces[$i]['positions'][$j] = $position->position;
						$j++;
					}
				}
				
				$selectWorks = $this->select()
					->from('work')
					->join('workplace_has_work', 'work.id_work = workplace_has_work.id_work')
					->where('workplace_has_work.id_workplace = ?', $workplaces[$i]['workplace']->getIdWorkplace());
				$selectWorks->setIntegrityCheck(false);
				$works = $this->fetchAll($selectWorks);
				if($works != null){
					$k = 0;
					foreach($works as $work){
						$workplaces[$i]['works'][$k] = $work->work;
						$k++;
					}
				}
				
				$selectTechnicalDevices = $this->select()
					->from('technical_device')
					->join('workplace_has_technical_device', 'technical_device.id_technical_device = workplace_has_technical_device.id_technical_device')
					->where('workplace_has_technical_device.id_workplace = ?', $workplaces[$i]['workplace']->getIdWorkplace());
				$selectTechnicalDevices->setIntegrityCheck(false);
				$technicalDevices = $this->fetchAll($selectTechnicalDevices);
				if($technicalDevices != null){
					$l = 0;
					foreach($technicalDevices as $technicalDevice){
						$workplaces[$i]['technical_devices'][$l]['sort'] = $technicalDevice->sort;
						$workplaces[$i]['technical_devices'][$l]['type'] = $technicalDevice->type;
						$l++;
					}
				}
				
				$selectChemicals = $this->select()
					->from('chemical')
					->join('workplace_has_chemical', 'chemical.id_chemical = workplace_has_chemical.id_chemical')
					->where('workplace_has_chemical.id_workplace = ?', $workplaces[$i]['workplace']->getIdWorkplace());
				$selectChemicals->setIntegrityCheck(false);
				$chemicals = $this->fetchAll($selectChemicals);
				if($chemicals != null){
					$k = 0;
					foreach($chemicals as $chemical){
						$workplaces[$i]['chemicals'][$k]['chemical'] = $chemical->chemical;
						$workplaces[$i]['chemicals'][$k]['usual_amount'] = $chemical->usual_amount;
						$workplaces[$i]['chemicals'][$k]['use_purpose'] = $chemical->use_purpose;
						$k++;
					}
				}
					
				$i++;
			}
			
			return $workplaces;
		}
		return null;
	}
	
	/**************************************************************
	 * Vrací pole pro rozbalovací seznam pracovišť.
	 */
	public function getWorkplaces($clientId){
		$select = $this->select()->from('workplace')
			->join('subsidiary', 'workplace.subsidiary_id = subsidiary.id_subsidiary')
			->where('workplace.client_id = ?', $clientId)
			->order('name');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		if (count($results) > 0){
			$workplaces = array();
			foreach($results as $result){
				$key = $result->id_workplace;
				$workplace = $result->name;
				$workplaces[$key] = $workplace;
			}
			return $workplaces;
		}
		else{
			return 0;
		}
	}
	
	/**************************************************************
	 * Vrací pole pro rozbalovací seznam pracovišť s názvem pobočky.
	*/
	public function getWorkplacesWithSubsidiaryName($clientId){
		$select = $this->select()->from('workplace')
		->join('subsidiary', 'workplace.subsidiary_id = subsidiary.id_subsidiary')
		->where('workplace.client_id = ?', $clientId)
		->order('name');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		if (count($results) > 0){
			$workplaces = array();
			foreach($results as $result){
				$key = $result->id_workplace;
				$workplace = $result->name . ' (' . $result->subsidiary_name . ', ' . $result->subsidiary_street . ', ' . $result->subsidiary_town . ')';
				$workplaces[$key] = $workplace;
			}
			return $workplaces;
		}
		else{
			return array();
		}
	}
	
	public function getWorkplacesBySubsidiaries($data){
		$select = $this->select()->from('workplace')
			->join('subsidiary', 'workplace.subsidiary_id = subsidiary.id_subsidiary')
			->where('subsidiary.id_subsidiary IN(?)', $data)
			->order('name');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		if (count($results) > 0){
			$workplaces = array();
			foreach($results as $result){
				$key = $result->id_workplace;
				$workplace = $result->name . ' (' . $result->subsidiary_name . ', ' . $result->subsidiary_street . ', ' . $result->subsidiary_town . ')';
				$workplaces[$key] = $workplace;
			}
			return $workplaces;
		}
		else{
			return array();
		}
	}
	
	/*********************************************************************
	 * Vrací kompletní pole pro populate formuláře.
	 */
	public function getWorkplaceComplete($workplaceId){
		$select = $this->select()->from('workplace')
			->where('id_workplace = ?', $workplaceId);
		$workplace = $this->fetchAll($select);
		if(count($workplace) > 0){
			$workplace = $workplace->current()->toArray();			
		}
		
		$select = $this->select()->from('workplace_has_position')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$positions = $this->fetchAll($select);
		if(count($positions) > 0){
			$i = 0;
			foreach($positions as $position){
				$workplace['positionList'][$i] = $position->id_position;
				$i++;
			}
		}
		
		$select = $this->select()->from('workplace_has_work')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$works = $this->fetchAll($select);
		if(count($works) > 0){
			$i = 0;
			foreach($works as $work){
				$workplace['workList'][$i] = $work->id_work;
				$i++;
			}
		}
		
		$select = $this->select()->from('workplace_has_technical_device')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$technicalDevices = $this->fetchAll($select);
		if(count($technicalDevices) > 0){
			$i = 0;
			foreach($technicalDevices as $technicalDevice){
				$workplace['technicaldeviceList'][$i] = $technicalDevice->id_technical_device;
				$i++;
			}
		}
		
		$select = $this->select()->from('workplace_has_chemical')
			->where('id_workplace = ?', $workplaceId)
			->join('chemical', 'workplace_has_chemical.id_chemical = chemical.id_chemical')
			->order('chemical.chemical');
		$select->setIntegrityCheck(false);
		$chemicals = $this->fetchAll($select);
		if(count($chemicals) > 0){
			$i = 0;
			foreach($chemicals as $chemical){
				$workplace['chemicalList'][$i] = $chemical->id_chemical;
				$workplace['chemicalDetails'][$i]['id_chemical'] = $chemical->id_chemical;
				$workplace['chemicalDetails'][$i]['chemical'] = $chemical->chemical;
				$workplace['chemicalDetails'][$i]['use_purpose'] = $chemical->use_purpose;
				$workplace['chemicalDetails'][$i]['usual_amount'] = $chemical->usual_amount;
				$i++;
			}
		}
		return $workplace;
	}
	
	public function existsWithinSubsidiaries($workplaceId, $subsidiaries){
		$workplace = $this->getWorkplace($workplaceId);
		/* Zend_Debug::dump($workplace->getSubsidiaryId());
		Zend_Debug::dump($subsidiaries);
		die(); */
		if(in_array($workplace->getSubsidiaryId(), $subsidiaries)){
			return "OK";
		}
		else{
			return "Pracoviště " . $workplace->getName() . " se nenachází na žádné ze zaškrtnutých poboček. 
					Zaškrtněte pobočku, kde se pracoviště nachází, nebo zrušte zaškrtnutí pracoviště.";
		}
	}
	
	private function process($result){
		if ($result->count()){
			$workplaces = array();
			foreach($result as $workplace){
				$workplace = $result->current();
				$workplaces[] = $this->processWorkplace($workplace);
			}
			return $workplaces;
		}
	}
	
	private function processWorkplace($workplace){
		$data = $workplace->toArray();
		return new Application_Model_Workplace($data);
	}
	
	public function existsWorkplace($workplaceName, $clientId){
		$workplace = $this->fetchAll($this->select()
									->from('workplace')
									->where('name = ?', $workplaceName)
									->where('client_id = ?', $clientId));
		if (count($workplace) != 0){
			return $workplace->current()->id_workplace;
		}
		return false;
	}
	
	public function existsWorkplaceSub($workplaceName, $subsidiaryId){
		$workplace = $this->fetchAll($this->select()
				->from('workplace')
				->where('name = ?', $workplaceName)
				->where('subsidiary_id = ?', $subsidiaryId));
		if (count($workplace) != 0){
			return $workplace->current()->id_workplace;
		}
		return false;
	}
	
}