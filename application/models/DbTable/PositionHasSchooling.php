<?php

class Application_Model_DbTable_PositionHasSchooling extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_schooling';
	
	protected $_referenceMap = array(
			'Position' => array(
					'columns' => array('id_position'),
					'refTableClass' => 'Application_Model_DbTable_Position',
					'refColumns' => array('id_position'),
					),
			'Schooling' => array(
					'columns' => array('id_schooling'),
					'refTableClass' => 'Application_Model_DbTable_Schooling',
					'refColumns' => array('id_schooling'),
					),
			);
	
	public function getSchoolings($positionId){
		$select = $this->select()
			->where('id_position = ?', $positionId);
		$results = $this->fetchAll($select);
		$schoolings = array();
		foreach($results as $result){
			$schoolings[] = $result->id_schooling;
		}
		return $schoolings;
	}
	
	public function removeRelation($schoolingId, $positionId){
		$this->delete(array(
				'id_schooling = ?' => $schoolingId,
				'id_position = ?' => $positionId,
		));
	}
	
	public function addRelation($positionId, $schoolingId, $note, $private){
		try{
			$data['id_position'] = $positionId;
			$data['id_schooling'] = $schoolingId;
			$data['note'] = $note;
			$data['private'] = $private;
			$this->insert($data);
		}
		catch(Exception $e){
			$data['note'] = $note;
			$data['private'] = $private;
			$this->update($data, array(
					'id_position = ?' => $positionId,
					'id_schooling = ?' => $schoolingId,
					));
		}
	}
	
}