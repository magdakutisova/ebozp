<?php
class Application_Model_DbTable_Workplace extends Zend_Db_Table_Abstract {
	
	protected $_name = 'workplace';
	
	protected $_referenceMap = array(
    	'Subsidiary' => array(
    		'columns' => 'subsidiary_id',
    		'refTableClass' => 'Application_Model_DbTable_Subsidiary',
    		'refColumns' => 'id_subsidiary'
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
		$data = $workplace->toArray();
		$workplaceId = $this->insert($data);
		return $workplaceId;
	}
	
	public function updateWorkplace(Application_Model_Workplace $workplace){
		$data = $workplace->toArray();
		$this->update($data, 'id_workplace = ' . $workplace->getIdWorkplace());
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
	
	/****************************************
	 * Pro výpis databáze pracovišť.
	 */
	public function getByClientDetails($clientId){
		$select = $this->select()
			->from('workplace')
			->join('subsidiary', 'subsidiary.id_subsidiary = workplace.subsidiary_id')
			->where('subsidiary.client_id = ?', $clientId)
			->order('subsidiary.subsidiary_name');
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if ($result->count()){
			$workplaces = array();
			$i = 0;
			foreach($result as $workplace){
				$workplace = $result->current();
				$workplaces[$workplace->subsidiary_name][$i]["workplace"] = $this->processWorkplace($workplace);
				if($workplace->hq){
					$workplaces[$workplace->subsidiary_name]['hq'] = 1;
				}
				$workplaces[$workplace->subsidiary_name]['id_subsidiary'] = $workplace->id_subsidiary; 
				$workplaceFactors = $workplace->findDependentRowset('Application_Model_DbTable_WorkplaceFactor', 'Workplace');
				if(count($workplaceFactors) > 0){
					foreach($workplaceFactors as $workplaceFactor){
						$workplaceFactor = $workplaceFactors->current();
						$workplaces[$workplace->subsidiary_name][$i]['workplaceFactors'][] = $this->processWorkplaceFactor($workplaceFactor);
					}
				}
				$workplaceRisks = $workplace->findDependentRowset('Application_Model_DbTable_WorkplaceRisk', 'Workplace');
				if(count($workplaceRisks) > 0){
					foreach($workplaceRisks as $workplaceRisk){
						$workplaceRisk = $workplaceRisks->current();
						$workplaces[$workplace->subsidiary_name][$i]['workplaceRisks'][] = $this->processWorkplaceRisk($workplaceRisk);
					}
				}
				$i++;
			}
			return $workplaces;
		}
		else{
			return null;
		}
	}
	
	/**************************************************************
	 * Vrací pole pro rozbalovací seznam pracovišť.
	 */
	public function getWorkplaces($clientId){
		$select = $this->select()->from('workplace')
			->join('subsidiary', 'workplace.subsidiary_id = subsidiary.id_subsidiary')
			->where('client_id = ?', $clientId)
			->where('subsidiary.deleted = 0')
			->order('name');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		if (count($results) > 0){
			$workplaces = array();
			foreach($results as $result){
				$key = $result->id_workplace;
				$workplace = $result->name . ' - ' . $result->subsidiary_name;
				$workplaces[$key][0] = $workplace;
				$workplaces[$key][1] = $result->subsidiary_id;
			}
			return $workplaces;
		}
		else{
			return 0;
		}
	}
	
	public function getWorkplaceFactors($workplaceId){
		$workplace = $this->fetchRow('id_workplace = ' . $workplaceId);
		$result = $workplace->findDependentRowset('Application_Model_DbTable_WorkplaceFactor', 'Workplace');
		if($result->count()){
			$workplaceFactors = array();
			foreach($result as $workplaceFactor){
				$workplaceFactor = $result->current();
				$workplaceFactors[] = $this->processWorkplaceFactor($workplaceFactor);
			}
			return $workplaceFactors;
		}
	}
	
	public function getWorkplaceRisks($workplaceId){
		$workplace = $this->fetchRow('id_workplace = ' . $workplaceId);
		$result = $workplace->findDependentRowset('Application_Model_DbTable_WorkplaceRisk', 'Workplace');
		if($result->count()){
			$workplaceRisks = array();
			foreach($result as $workplaceRisk){
				$workplaceRisk = $result->current();
				$workplaceRisks[] = $this->processWorkplaceRisk($workplaceRisk);
			}
			return $workplaceRisks;
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
	
	private function processWorkplaceFactor($workplaceFactor){
		$data = $workplaceFactor->toArray();
		return new Application_Model_WorkplaceFactor($data); 
	}
	
	private function processWorkplaceRisk($workplaceRisk){
		$data = $workplaceRisk->toArray();
		return new Application_Model_WorkplaceRisk($data);
	}
	
}