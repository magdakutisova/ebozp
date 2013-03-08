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