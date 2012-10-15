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
	
}