<?php
class My_Controller_Helper_PositionRelationships extends Zend_Controller_Action_Helper_Abstract{
	
	public function direct($formData, $positionId, $toEdit = false){
		$subsidiaryHasPosition = new Application_Model_DbTable_SubsidiaryHasPosition();
		$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
		$positionHasEnvironmentFactor = new Application_Model_DbTable_PositionHasEnvironmentFactor();
		$positionHasSchooling = new Application_Model_DbTable_PositionHasSchooling();
		$positionHasWork = new Application_Model_DbTable_PositionHasWork();
		$positionHasTechnicalDevice = new Application_Model_DbTable_PositionHasTechnicalDevice();
		$positionHasChemical = new Application_Model_DbTable_PositionHasChemical();
		$employees = new Application_Model_DbTable_Employee();
		 
		foreach($formData['subsidiaryList'] as $subsidiaryId){
			$subsidiaryHasPosition->addRelation($subsidiaryId, $positionId);
		}
		foreach($formData['workplaceList'] as $workplaceId){
			$workplaceHasPosition->addRelation($workplaceId, $positionId);
		}
		if($formData['categorization'] == 1){
			$environmentFactorDetails = array_filter(array_keys($formData), array($this, 'findEnvironmentFactorDetails'));
			foreach($formData['environmentfactorList'] as $environmentFactorId){
				$category = '';
				$measurementTaken = '';
				$protectionMeasures = '';
				$note = '';
				$private = '';
				foreach ($environmentFactorDetails as $detail){
					if($formData[$detail]['id_environment_factor'] == $environmentFactorId){
						$category = $formData[$detail]['category'];
						$measurementTaken = $formData[$detail]['measurement_taken'];
						$protectionMeasures = $formData[$detail]['protection_measures'];
						$note = $formData[$detail]['note'];
						$private = $formData[$detail]['private'];
						break 1;
					}
				}
				$positionHasEnvironmentFactor->addRelation($positionId, $environmentFactorId, $category, $protectionMeasures, $measurementTaken, $note, $private);
			}
		}
		$schoolingDetails = array_filter(array_keys($formData), array($this, 'findSchoolingDetails'));
		$schoolingList = array();
		if(array_key_exists('schoolingList', $formData)){
			$schoolingList = $formData['schoolingList'];
		}
		$schoolingList[] = 1;
		$schoolingList[] = 2;
		foreach($schoolingList as $schoolingId){
			$note = '';
			$private = '';
			foreach($schoolingDetails as $detail){
				if($formData[$detail]['id_schooling'] == $schoolingId){
					$note = $formData[$detail]['note'];
					$private = $formData[$detail]['private'];
					break 1;
				}
			}
			$positionHasSchooling->addRelation($positionId, $schoolingId, $note, $private);
		}
		$workDetails = array_filter(array_keys($formData), array($this, 'findWorkDetails'));
		foreach($formData['workList'] as $workId){
			$frequency = null;
			foreach($workDetails as $detail){
				if($formData[$detail]['id_work'] == $workId){
					$frequencyKey = $formData[$detail]['frequency'];
					if($frequencyKey != 0){
						if($frequencyKey != 6){
							$frequencies = My_Frequency::getFrequencies();
							$frequency = $frequencies[$frequencyKey];
						}
						else{
							$frequency = $formData[$detail]['new_frequency'];
						}
					}
					break 1;
				}
			}
			$positionHasWork->addRelation($positionId, $workId, $frequency);
		}
		foreach($formData['technicaldeviceList'] as $technicalDeviceId){
			$positionHasTechnicalDevice->addRelation($positionId, $technicalDeviceId);
		}
		$chemical2Details = array_filter(array_keys($formData), array($this, 'findChemical2Details'));
		foreach($formData['chemicalList'] as $chemicalId){
			$exposition = '';
			foreach($chemical2Details as $detail){
				if($formData[$detail]['id_chemical'] == $chemicalId){
					$exposition = $formData[$detail]['exposition'];
					break 1;
				}
			}
			$positionHasChemical->addRelation($positionId, $chemicalId, $exposition);
		}
		foreach($formData['employeeList'] as $employeeId){
			$employee = $employees->getEmployee($employeeId);
			$employee->setPositionId($positionId);
			$employees->updateEmployee($employee);
		}
		 
		if($toEdit){
			$subsidiaries = $subsidiaryHasPosition->getSubsidiaries($positionId);
			foreach($subsidiaries as $subsidiary){
				if(!in_array($subsidiary, $formData['subsidiaryList'])){
					$subsidiaryHasPosition->removeRelation($subsidiary, $positionId);
				}
			}
			$workplaces = $workplaceHasPosition->getWorkplaces($positionId);
			foreach($workplaces as $workplace){
				if(!in_array($workplace, $formData['workplaceList'])){
					$workplaceHasPosition->removeRelation($workplace, $positionId);
				}
			}
			$environmentFactors = $positionHasEnvironmentFactor->getEnvironmentFactors($positionId);
			foreach($environmentFactors as $environmentFactor){
				if(!in_array($environmentFactor, $formData['environmentfactorList'])){
					$positionHasEnvironmentFactor->removeRelation($environmentFactor, $positionId);
				}
			}
			$schoolingList = array();
			if(array_key_exists('schoolingList', $formData)){
				$schoolingList = $formData['schoolingList'];
			}
			$schoolingList[] = 1;
			$schoolingList[] = 2;
			$schoolings = $positionHasSchooling->getSchoolings($positionId);
			foreach($schoolings as $schooling){
				if(!in_array($schooling, $schoolingList)){
					$positionHasSchooling->removeRelation($schooling, $positionId);
				}
			}
			$works = $positionHasWork->getWorks($positionId);
			foreach($works as $work){
				if(!in_array($work, $formData['workList'])){
					$positionHasWork->removeRelation($work, $positionId);
				}
			}
			$technicalDevices = $positionHasTechnicalDevice->getTechnicalDevices($positionId);
			foreach($technicalDevices as $technicalDevice){
				if(!in_array($technicalDevice, $formData['technicaldeviceList'])){
					$positionHasTechnicalDevice->removeRelation($technicalDevice, $positionId);
				}
			}
			$chemicals = $positionHasChemical->getChemicals($positionId);
			foreach($chemicals as $chemical){
				if(!in_array($chemical, $formData['chemicalList'])){
					$positionHasChemical->removeRelation($chemical, $positionId);
				}
			}
			$employeesList = $employees->getByPosition($positionId);
			foreach($employeesList as $employee){
				if(!in_array($employee->getIdEmployee(), $formData['employeeList'])){
					$employee->setPositionId(null);
					$employees->updateEmployee($employee);
				}
			}
		}
	}
	
	private function findEnvironmentFactorDetails($environmentFactorDetail)
	{
		if(strpos($environmentFactorDetail, "environmentFactorDetail") !== false){
			return $environmentFactorDetail;
		}
	}
	
	private function findSchoolingDetails($schoolingDetail)
	{
		if(strpos($schoolingDetail, "schoolingDetail") !== false){
			return $schoolingDetail;
		}
	}
	
	private function findWorkDetails($workDetail)
	{
		if(strpos($workDetail, "workDetail") !== false){
			return $workDetail;
		}
	}
	
	private function findChemical2Details($chemical2Detail)
	{
		if(strpos($chemical2Detail, "chemical2Detail") !== false){
			return $chemical2Detail;
		}
	}
	
}