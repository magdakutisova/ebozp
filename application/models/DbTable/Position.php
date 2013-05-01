<?php
class Application_Model_DbTable_Position extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position';
	
	public function getPosition($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_position = ' . $id);
		if(!$row){
			throw new Exception("Pracovní pozice $id nebyla nalezena.");
		}
		$position = $row->toArray();
		return new Application_Model_Position($position);
	}
	
	public function addPosition(Application_Model_Position $position){
		$data = $position->toArray();
		$positionId = $this->insert($data);
		return $positionId;
	}
	
	public function updatePosition(Application_Model_Position $position){
		$data = $position->toArray();
		$this->update($data, 'id_position = ' . $position->getIdPosition());
	}
	
	public function deletePosition($id){
		$this->delete('id_position = ' . (int)$id);
	}
	
	
	/*************************************************
	 * Vrací seznam ID - pozice.
	 */
	public function getPositions($clientId){
		$select = $this->select()->from('position')
			->where('client_id = ?', $clientId)
			->order('position');
		$results = $this->fetchAll($select);
		$positions = array();
		if(count($results) > 0){
			foreach ($results as $result){
				$key = $result->id_position;
				$positions[$key] = $result->position;
			}
		}
		return $positions;
	}
	
	public function getByWorkplace($workplaceId){
		$select = $this->select()
			->from('position')
			->join('workplace_has_position', 'position.id_position = workplace_has_position.id_position')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function existsPosition($positionName, $clientId){
		$position = $this->fetchAll($this->select()
									->from('position')
									->where('position = ?', $positionName)
									->where('client_id = ?', $clientId));
		if(count($position) != 0){
			return $position->current()->id_position;
		}
		return false;
	}
	
	public function getBySubsidiaryWithDetails($subsidiaryId, $incomplete = false){
		if(!$incomplete){
			$select = $this->select()
				->from('position')
				->join('subsidiary_has_position', 'position.id_position = subsidiary_has_position.id_position')
				->where('id_subsidiary = ?', $subsidiaryId)
				->order('position.position');
			$select->setIntegrityCheck(false);
			$result = $this->fetchAll($select);
		}
		else{
			$subSelect = $this->select()
				->distinct()
				->from(array('position_has_work'), array('position_has_work.id_position'))
				->where('position_has_work.frequency IS NOT NULL');
			$subSelect->setIntegrityCheck(false);
			$select = $this->select()
				->from('position')
				->join('subsidiary_has_position', 'position.id_position = subsidiary_has_position.id_position')
				->where('subsidiary_has_position.id_subsidiary = ?', $subsidiaryId)
				->where('position.working_hours IS NULL OR position.id_position NOT IN(' . $subSelect . ')')
				->order('position.position');
			$select->setIntegrityCheck(false);
			$result = $this->fetchAll($select);
		}
		
		$positions = array();
		$i = 0;
		if($result != null){
			foreach($result as $position){
				$positions[$i]['position'] = $this->processPosition($position);
				
				$selectWorkplaces = $this->select()
					->from('workplace')
					->join('workplace_has_position', 'workplace.id_workplace = workplace_has_position.id_workplace')
					->where('workplace_has_position.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectWorkplaces->setIntegrityCheck(false);
				$workplaces = $this->fetchAll($selectWorkplaces);
				if($workplaces != null){
					$j = 0;
					foreach($workplaces as $workplace){
						$positions[$i]['workplaces'][$j] = $workplace->name;
						$j++;
					}
				}
				
				$selectEnvironmentFactors = $this->select()
					->from('environment_factor')
					->join('position_has_environment_factor', 'environment_factor.id_environment_factor = position_has_environment_factor.id_environment_factor')
					->where('position_has_environment_factor.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectEnvironmentFactors->setIntegrityCheck(false);
				$environmentFactors = $this->fetchAll($selectEnvironmentFactors);
				if($environmentFactors != null){
					$k = 0;
					foreach($environmentFactors as $environmentFactor){
						$positions[$i]['environmentFactors'][$k]['factor'] = $environmentFactor->factor;
						$positions[$i]['environmentFactors'][$k]['category'] = $environmentFactor->category;
						$positions[$i]['environmentFactors'][$k]['protection_measures'] = $environmentFactor->protection_measures;
						$positions[$i]['environmentFactors'][$k]['measurement_taken'] = $environmentFactor->measurement_taken;
						$positions[$i]['environmentFactors'][$k]['note'] = $environmentFactor->note;
						$positions[$i]['environmentFactors'][$k]['private'] = $environmentFactor->private;
						$k++;
					}
				}
				
				$selectSchoolings = $this->select()
					->from('schooling')
					->join('position_has_schooling', 'schooling.id_schooling = position_has_schooling.id_schooling')
					->where('position_has_schooling.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectSchoolings->setIntegrityCheck(false);
				$schoolings = $this->fetchAll($selectSchoolings);
				if($schoolings != null){
					$l = 0;
					foreach($schoolings as $schooling){
						$positions[$i]['schoolings'][$l]['schooling'] = $schooling->schooling;
						$positions[$i]['schoolings'][$l]['note'] = $schooling->note;
						$positions[$i]['schoolings'][$l]['private'] = $schooling->private;
						$l++;
					}
				}
				
				$selectWorks = $this->select()
					->from('work')
					->join('position_has_work', 'work.id_work = position_has_work.id_work')
					->where('position_has_work.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectWorks->setIntegrityCheck(false);
				$works = $this->fetchAll($selectWorks);
				if($works != null){
					$m = 0;
					foreach($works as $work){
						$positions[$i]['works'][$m]['work'] = $work->work;
						$positions[$i]['works'][$m]['frequency'] = $work->frequency;
						$m++;
					}
				}
				
				$selectTechnicalDevices = $this->select()
					->from('technical_device')
					->join('position_has_technical_device', 'technical_device.id_technical_device = position_has_technical_device.id_technical_device')
					->where('position_has_technical_device.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectTechnicalDevices->setIntegrityCheck(false);
				$technicalDevices = $this->fetchAll($selectTechnicalDevices);
				if($technicalDevices != null){
					$n = 0;
					foreach($technicalDevices as $technicalDevice){
						$positions[$i]['technicalDevices'][$n]['sort'] = $technicalDevice->sort;
						$positions[$i]['technicalDevices'][$n]['type'] = $technicalDevice->type;
						$n++;
					}
				}
				
				$selectChemicals = $this->select()
					->from('chemical')
					->join('position_has_chemical', 'chemical.id_chemical = position_has_chemical.id_chemical')
					->where('position_has_chemical.id_position = ?', $positions[$i]['position']->getIdPosition());
				$selectChemicals->setIntegrityCheck(false);
				$chemicals = $this->fetchAll($selectChemicals);
				if($chemicals != null){
					$o = 0;
					foreach($chemicals as $chemical){
						$positions[$i]['chemicals'][$o]['chemical'] = $chemical->chemical;
						$positions[$i]['chemicals'][$o]['exposition'] = $chemical->exposition;
						$o++;
					}
				}
				
				$selectEmployees = $this->select()
					->from('employee')
					->where('position_id = ?', $positions[$i]['position']->getIdPosition());
				$selectEmployees->setIntegrityCheck(false);
				$employees = $this->fetchAll($selectEmployees);
				if($employees != null){
					$p = 0;
					foreach($employees as $employee){
						$positions[$i]['employees'][$p]['first_name'] = $employee->first_name;
						$positions[$i]['employees'][$p]['surname'] = $employee->surname;
						$p++;
					}
				}
				
				$i++;
			}
			return $positions;
		}
		return null;
	}
	
	private function process($result){
		if ($result->count()){
			$positions = array();
			foreach($result as $position){
				$position = $result->current();
				$positions[] = $this->processPosition($position);
			}
			return $positions;
		}
	}
	
	private function processPosition($position){
		$data = $position->toArray();
		return new Application_Model_Position($data);
	}
	
}