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
					->join('workplace_has_position', 'position.id_position = workplace_has_position.id_position')
					->where('workplace_has_position.id_position = ?', $positions[$i]['position']->getIdPosition());
			}
		}
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