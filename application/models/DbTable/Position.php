<?php
class Application_Model_DbTable_Position extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position';
	
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
	);
	
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
	
	public function updatePosition(Application_Model_Position $position, $differentName = false){
		$data = $position->toArray();
		$this->update($data, 'id_position = ' . $position->getIdPosition());
		return true;
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
				->where('subsidiary_id = ?', $subsidiaryId)
				->order('position.position');
			//$select->setIntegrityCheck(false);
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
				->where('subsidiary_id = ?', $subsidiaryId)
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
						$positions[$i]['environmentFactors'][$k]['source'] = $environmentFactor->source;
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
	
	/**
	 * 
	 * @param unknown_type $positionId
	 * @return kompletní pole pro populate formuláře
	 */
	public function getPositionComplete($positionId){
		$select = $this->select()->from('position')
			->where('id_position = ?', $positionId);
		$position = $this->fetchAll($select);
		if(count($position) > 0){
			$position = $position->current()->toArray();
		}
		
		$select = $this->select()->from('subsidiary_has_position')
			->where('id_position = ?', $positionId);
		$select->setIntegrityCheck(false);
		$subsidiaries = $this->fetchAll($select);
		if(count($subsidiaries) > 0){
			$i = 0;
			foreach($subsidiaries as $subsidiary){
				$position['subsidiaryList'][$i] = $subsidiary->id_subsidiary;
				$i++;
			}
		}
		
		$select = $this->select()->from('workplace_has_position')
			->where('id_position = ?', $positionId);
		$select->setIntegrityCheck(false);
		$workplaces = $this->fetchAll($select);
		if(count($workplaces) > 0){
			$i = 0;
			foreach($workplaces as $workplace){
				$position['workplaceList'][$i] = $workplace->id_workplace;
				$i++;
			}
		}
		
		
		$select = $this->select()->from('position_has_environment_factor')
			->where('id_position = ?', $positionId)
			->join('environment_factor', 'position_has_environment_factor.id_environment_factor = environment_factor.id_environment_factor')
			->order('environment_factor.factor');
		$select->setIntegrityCheck(false);
		$environmentFactors = $this->fetchAll($select);
		if(count($environmentFactors) > 0){
			$i = 0;
			foreach($environmentFactors as $environmentFactor){
				$position['environmentfactorList'][$i] = $environmentFactor->id_environment_factor;
				$position['environmentFactorDetails'][$i]['id_environment_factor'] = $environmentFactor->id_environment_factor;
				$position['environmentFactorDetails'][$i]['factor'] = $environmentFactor->factor;
				$position['environmentFactorDetails'][$i]['category'] = $environmentFactor->category;
				$position['environmentFactorDetails'][$i]['protection_measures'] = $environmentFactor->protection_measures;
				$position['environmentFactorDetails'][$i]['measurement_taken'] = $environmentFactor->measurement_taken;
				$position['environmentFactorDetails'][$i]['source'] = $environmentFactor->source;
				$position['environmentFactorDetails'][$i]['note'] = $environmentFactor->note;
				$position['environmentFactorDetails'][$i]['private'] = $environmentFactor->private;
				$i++;
			}
		}
		
		$select = $this->select()->from('position_has_schooling')
			->where('id_position = ?', $positionId)
			->join('schooling', 'position_has_schooling.id_schooling = schooling.id_schooling')
			->order('schooling.schooling');
		$select->setIntegrityCheck(false);
		$schoolings = $this->fetchAll($select);
		if(count($schoolings) > 0){
			$i = 0;
			foreach($schoolings as $schooling){
				$position['schoolingList'][$i] = $schooling->id_schooling;
				$position['schoolingDetails'][$i]['id_schooling'] = $schooling->id_schooling;
				$position['schoolingDetails'][$i]['schooling'] = $schooling->schooling;
				$position['schoolingDetails'][$i]['note'] = $schooling->note;
				$position['schoolingDetails'][$i]['private'] = $schooling->private;
				$i++;
			}
		}
		
		$select = $this->select()->from('position_has_work')
			->where('id_position = ?', $positionId)
			->join('work', 'position_has_work.id_work = work.id_work')
			->order('work.work');
		$select->setIntegrityCheck(false);
		$works = $this->fetchAll($select);
		if(count($works) > 0){
			$i = 0;
			$frequencies = My_Frequency::getFrequencies();
			foreach($works as $work){
				$position['workList'][$i] = $work->id_work;
				$position['workDetails'][$i]['id_work'] = $work->id_work;
				$position['workDetails'][$i]['work'] = $work->work;
					foreach($frequencies as $key => $frequency){
						if($work->frequency == $frequency){
							$position['workDetails'][$i]['frequency'] = $key;
							$position['workDetails'][$i]['new_frequency'] = null;
							break 1;
						}
					}
					if(!isset($position['workDetails'][$i]['frequency'])){
						$position['workDetails'][$i]['frequency'] = 6;
						$position['workDetails'][$i]['new_frequency'] = $work->frequency;
					}
				$i++;
			}
		}
		
		$select = $this->select()->from('position_has_technical_device')
			->where('id_position = ?', $positionId);
		$select->setIntegrityCheck(false);
		$technicalDevices = $this->fetchAll($select);
		if(count($technicalDevices) > 0){
			$i = 0;
			foreach($technicalDevices as $technicalDevice){
				$position['technicaldeviceList'][$i] = $technicalDevice->id_technical_device;
				$i++;
			}
		}
		
		$select = $this->select()->from('position_has_chemical')
			->where('id_position = ?', $positionId)
			->join('chemical', 'position_has_chemical.id_chemical = chemical.id_chemical')
			->order('chemical.chemical');
		$select->setIntegrityCheck(false);
		$chemicals = $this->fetchAll($select);
		if(count($chemicals) > 0){
			$i = 0;
			foreach($chemicals as $chemical){
				$position['chemicalList'][$i] = $chemical->id_chemical;
				$position['chemicalDetails'][$i]['id_chemical'] = $chemical->id_chemical;
				$position['chemicalDetails'][$i]['chemical'] = $chemical->chemical;
				$position['chemicalDetails'][$i]['exposition'] = $chemical->exposition;
				$i++;
			}
		}
		
		$select = $this->select()->from('employee')
			->where('position_id = ?', $positionId);
		$select->setIntegrityCheck(false);
		$employees = $this->fetchAll($select);
		if(count($employees) > 0){
			$i = 0;
			foreach($employees as $employee){
				$position['employeeList'][$i] = $employee->id_employee;
				$i++;
			}
		}
		return $position;
		
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