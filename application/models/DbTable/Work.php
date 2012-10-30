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
	public function getWorks($subsidiaryId){
		$select = $this->select()
			->from('work')
			->join('position_has_work', 'work.id_work = position_has_work.id_work')
			->join('position', 'position_has_work.id_position = position.id_position')
			->where('subsidiary_id = ?', $subsidiaryId)
			->order('work.work');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$works = array();
		$works[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_work;
				$works[$key] = $result->work;
			}
		}
		return $works;
	}
	
}