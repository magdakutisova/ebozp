<?php
class Application_Model_DbTable_Work extends Zend_Db_Table_Abstract{
	
	protected $_name = 'work';
	
	public function getWork($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_work = ' . $id);
		if(!$row){
			throw new Exception("Pracovní činnost $id nebyla nalezena");
		}
		$work = $row->toArray();
		return new Application_Model_Work($work);
	}
	
	public function addWork(Application_Model_Work $work){
		$data = $work->toArray();
		$workId = $this->insert($data);
		return $workId;
	}
	
	public function updateWork(Application_Model_Work $work){
		$data = $work->toArray();
		$this->update($data, 'id_work = ' . $work->getIdWork());
	}
	
	public function deleteWork($id){
		$this->delete('id_work = ' . (int)$id);
	}
	
	public function updateWorkAtClient(Application_Model_Work $work, $clientId){
		$existsWork = $this->existsWork($work->getWork());
		$oldId = $work->getIdWork();
		$newId = '';
		
		if($existsWork){
			$newId = $existsWork;
		}
		else{
			$work->setIdWork(null);
			$newId = $this->addWork($work);
		}
		
		$clientHasWork = new Application_Model_DbTable_ClientHasWork();
		$clientHasWork->updateRelation($clientId, $oldId, $newId);
		$positionHasWork = new Application_Model_DbTable_PositionHasWork();
		$positionHasWork->updateRelation($clientId, $oldId, $newId);
		$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
		$workplaceHasWork->updateRelation($clientId, $oldId, $newId);
	}
	
	public function deleteWorkFromClient($id, $clientId){
		$clientHasWork = new Application_Model_DbTable_ClientHasWork();
		$clientHasWork->removeRelation($clientId, $id);
		$positionHasWork = new Application_Model_DbTable_PositionHasWork();
		$positionHasWork->removeAllClientRelations($clientId, $id);
		$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
		$workplaceHasWork->removeAllClientRelations($clientId, $id);
	}
	
	/********************************************************
	 * Vrací seznam ID - činnost.
	 */
	public function getWorks($clientId){
		$select = $this->select()
			->from('work')
			->join('client_has_work', 'work.id_work = client_has_work.id_work')
			->where('client_has_work.id_client = ?', $clientId)
			->order('work.work')
			->group('work');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$works = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_work;
				$works[$key] = $result->work;
			}
		}
		return $works;
	}
	
	public function getByWorkplace($workplaceId){
		$select = $this->select()
			->from('work')
			->join('workplace_has_work', 'work.id_work = workplace_has_work.id_work')
			->where('id_workplace = ?', $workplaceId);
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function getBySubsidiaryWithPositions($subsidiaryId){
		$select = $this->select()
			->from('work')
			->join('workplace_has_work', 'work.id_work = workplace_has_work.id_work')
			->join('workplace', 'workplace_has_work.id_workplace = workplace.id_workplace')
			->where('workplace.subsidiary_id = ?', $subsidiaryId)
			->order('workplace.name');
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			$works = array();
			foreach ($result as $work){
				if($work->work != ''){
					$works[$work->name][$work->id_work]['work'] = $work->work;
					$select = $this->select()
						->from('work')
						->join('position_has_work', 'work.id_work = position_has_work.id_work')
						->join('position', 'position_has_work.id_position = position.id_position')
						->where('work.id_work = ' . $work->id_work . ' AND position.subsidiary_id = ' . $subsidiaryId)
						->order('position.position');
					$select->setIntegrityCheck(false);
					$subResult = $this->fetchAll($select);
					if(count($subResult) > 0){
						$works[$work->name][$work->id_work]['positions'] = ', provádí se na pracovních pozicích: ';
						$isFirst = true;
						foreach($subResult as $position){
							if($isFirst){
								$works[$work->name][$work->id_work]['positions'] .= $position->position;
							}
							else{
								$works[$work->name][$work->id_work]['positions'] .= ', ' . $position->position;
							}
							$isFirst = false;
						}
					}
				}
				else{
					$works[$work->name] = null;
				}
			}
			return $works;
		}
		else{
			return null;
		}
	}
	
	public function getBySubsidiaryWithWorkplaces($subsidiaryId){
		$select = $this->select()
			->from('work')
			->join('position_has_work', 'work.id_work = position_has_work.id_work')
			->join('position', 'position_has_work.id_position = position.id_position')
			->where('position.subsidiary_id = ?', $subsidiaryId)
			->order('position.position');
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		if(count($result) > 0){
			$works = array();
			foreach ($result as $work){
				if($work->work != ''){
					$works[$work->position][$work->id_work]['work'] = $work->work;
					$select = $this->select()
						->from('work')
						->join('workplace_has_work', 'work.id_work = workplace_has_work.id_work')
						->join('workplace', 'workplace_has_work.id_workplace = workplace.id_workplace')
						->where('work.id_work = ' . $work->id_work . ' AND workplace.subsidiary_id = ' . $subsidiaryId)
						->order('workplace.name');
					$select->setIntegrityCheck(false);
					$subResult = $this->fetchAll($select);
					if(count($subResult) > 0){
						$works[$work->position][$work->id_work]['workplaces'] = ', provádí se na pracovištích: ';
						$isFirst = true;
						foreach($subResult as $workplace){
							if($isFirst){
								$works[$work->position][$work->id_work]['workplaces'] .= $workplace->name;
							}
							else{
								$works[$work->position][$work->id_work]['workplaces'] .= ', ' . $workplace->name;
							}
							$isFirst = false;
						}
					}
				}
				else{
					$works[$work->position] = null;
				}
			}
			return $works;
		}
		else{
			return null;
		}
	}
	
	public function existsWork($work){
		$select = $this->select()
			->from('work')
			->where('work = ?', $work);
		$results = $this->fetchAll($select);
		if(count($results) > 0){
			return $results->current()->id_work;
		}
		else{
			return 0;
		}
	}
	
	private function process($result){
		if ($result->count()){
			$works = array();
			foreach($result as $work){
				$work = $result->current();
				$works[] = $this->processWork($work);
			}
			return $works;
		}
	}
	
	private function processWork($work){
		$data = $work->toArray();
		return new Application_Model_Work($data);
	}
	
}