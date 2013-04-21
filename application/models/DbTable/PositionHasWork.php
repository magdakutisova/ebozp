<?php

class Application_Model_DbTable_PositionHasWork extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_work';
	
	protected $_referenceMap = array(
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
		'Work' => array(
			'columns' => array('id_work'),
			'refTableClass' => 'Application_Model_DbTable_Work',
			'refColumns' => array('id_work'),
		),
	);
	
	public function addRelation($positionId, $workId, $frequency){
		try{
			$data['id_position'] = $positionId;
			$data['id_work'] = $workId;
			$data['frequency'] = $frequency;
			$this->insert($data);
		}
		catch(Exception $e){
			$data['frequency'] = $frequency;
			$this->update($data, array(
					'id_position = ?' => $positionId,
					'id_work = ?' => $workId,
					));
		}
	}
	
}