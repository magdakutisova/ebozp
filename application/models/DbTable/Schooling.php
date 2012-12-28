<?php
class Application_Model_DbTable_Schooling extends Zend_Db_Table_Abstract{
	
	protected $_name = 'schooling';
	
	protected $_referenceMap = array(
			'Position' => array(
					'columns' => 'position_id',
					'refTableClass' => 'Application_Model_DbTable_Position',
					'refColumns' => 'id_position',
			),
	);
	
	public function getSchooling($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_schooling = ' . $id);
		if(!$row){
			throw new Exception("Školení $id nebylo nalezeno.");
		}
		$schooling = $row->toArray();
		return new Application_Model_Schooling($schooling);
	}
	
	public function addSchooling(Application_Model_Schooling $schooling){
		$data = $schooling->toArray();
		$schoolingId = $this->insert($data);
		return $schoolingId;
	}
	
	public function updateSchooling(Application_Model_Schooling $schooling){
		$data = $schooling->toArray();
		$this->update($data, 'id_schooling = ' . $schooling->getIdSchooling());
	}
	
	public function deleteSchooling($id){
		$this->delete('id_schooling = ' . (int)$id);
	}
	
}