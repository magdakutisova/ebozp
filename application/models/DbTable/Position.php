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
		$positions[0] = '-----';
		if(count($results) > 0){
			foreach ($results as $result){
				$key = $result->id_position;
				$positions[$key] = $result->position;
			}
		}
		return $positions;
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
	
}