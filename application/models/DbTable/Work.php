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
	
}