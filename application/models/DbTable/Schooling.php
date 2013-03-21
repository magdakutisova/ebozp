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
	
	/*****
	 * Vrátí pole školení "ID - název školení" pouze těch školení, která má klient navíc oproti defaultnímu seznamu.
	 */
	public function getExtraSchoolings($clientId){
		$select = $this->select()
			->from('schooling')
			->join('position_has_schooling', 'schooling.id_schooling = position_has_schooling.id_schooling')
			->join('position', 'position_has_schooling.id_position = position.id_position')
			->where('position.client_id = ?', $clientId)
			->where('schooling.id_schooling > 23')
			->group('schooling.id_schooling');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$schoolings = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_schooling;
				$schoolings[$key] = $result->schooling;
			}
		}
		return $schoolings;
	}
	
}