<?php
class Application_Model_DbTable_WorkplaceHasPosition extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_position';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
	);
	
	public function getPositions($workplaceId){
		$select = $this->select()
			->where('id_workplace = ?', $workplaceId);
		$results = $this->fetchAll($select);
		$positions = array();
		foreach($results as $result){
			$positions[] = $result->id_position;
		}
		return $positions;
	}
	
	public function addRelation($workplaceId, $positionId){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_position'] = $positionId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		} 
	}
	
	public function removeRelation($workplaceId, $positionId){
		$this->delete(array(
			'id_workplace = ?' => $workplaceId,
			'id_position = ?' => $positionId,
		));
	}
	
}