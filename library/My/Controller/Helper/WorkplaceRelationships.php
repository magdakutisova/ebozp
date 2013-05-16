<?php
class My_Controller_Helper_WorkplaceRelationships extends Zend_Controller_Action_Helper_Abstract{
	
	public function direct($formData, $workplaceId, $toEdit = false){
		$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
		$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
		$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
		$workplaceHasChemical = new Application_Model_DbTable_WorkplaceHasChemical();
		 
		foreach ($formData['positionList'] as $positionId){
			$workplaceHasPosition->addRelation($workplaceId, $positionId);
		}
		foreach ($formData['workList'] as $workId){
			$workplaceHasWork->addRelation($workplaceId, $workId);
		}
		foreach ($formData['technicaldeviceList'] as $technicalDeviceId){
			$workplaceHasTechnicalDevice->addRelation($workplaceId, $technicalDeviceId);
		}
		$chemicalDetails = array_filter(array_keys($formData), array($this, 'findChemicalDetails'));
		foreach ($formData['chemicalList'] as $chemicalId){
			$usePurpose = "";
			$usualAmount = "";
			foreach($chemicalDetails as $detail){
				if($formData[$detail]['id_chemical'] == $chemicalId){
					$usePurpose = $formData[$detail]['use_purpose'];
					$usualAmount = $formData[$detail]['usual_amount'];
					break 1;
				}
			}
			$workplaceHasChemical->addRelation($workplaceId, $chemicalId, $usePurpose, $usualAmount);
		}
		
		if($toEdit){
			$positions = $workplaceHasPosition->getPositions($workplaceId);
			foreach ($positions as $position){
				if(!in_array($position, $formData['positionList'])){
					$workplaceHasPosition->removeRelation($workplaceId, $position);
				}
			}
			$works = $workplaceHasWork->getWorks($workplaceId);
			foreach ($works as $work){
				if(!in_array($work, $formData['workList'])){
					$workplaceHasWork->removeRelation($workplaceId, $work);
				}
			}
			$technicalDevices = $workplaceHasTechnicalDevice->getTechnicalDevices($workplaceId);
			foreach ($technicalDevices as $technicalDevice){
				if(!in_array($technicalDevice, $formData['technicaldeviceList'])){
					$workplaceHasTechnicalDevice->removeRelation($workplaceId, $technicalDevice);
				}
			}
			$chemicals = $workplaceHasChemical->getChemicals($workplaceId);
			foreach ($chemicals as $chemical){
				if(!in_array($chemical, $formData['chemicalList'])){
					$workplaceHasChemical->removeRelation($workplaceId, $chemical);
				}
			}
		}
	}
	
	private function findChemicalDetails($chemicalDetail){
		if(strpos($chemicalDetail, "chemicalDetail") !== false){
			return $chemicalDetail;
		}
	}
	
}