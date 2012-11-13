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
		//když neexistuje pracoviště s daným názvem u klienta, vložit a vrátit ID
		$existingWorkplace = $this->existsWorkplace($workplace->getName(), $workplace->getClientId());
		if(!$existingWorkplace){
			$data = $workplace->toArray();
			$workplaceId = $this->insert($data);
			return $workplaceId;
		}
		return false;
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
		//TODO 2nd version
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
				$workplaces[$workplace->subsidiary_name][$i] = $this->processWorkplace($workplace);
				if($workplace->hq){
					$workplaces[$workplace->subsidiary_name]['hq'] = 1;
				}
				$workplaces[$workplace->subsidiary_name]['id_subsidiary'] = $workplace->id_subsidiary; 
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
	
}