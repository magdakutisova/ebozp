<?php

class Application_Model_DbTable_PositionHasEnvironmentFactor extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_environment_factor';
	
	protected $_referenceMap = array(
			'Position' => array(
					'columns' => array('id_position'),
					'refTableClass' => 'Application_Model_DbTable_Position',
					'refColumns' => array('id_position'),
					),
			'EnvironmentFactor' => array(
					'columns' => array('id_environment_factor'),
					'refTableClass' => 'Application_Model_DbTable_EnvironmentFactor',
					'refColumns' => array('id_environment_factor'),
					),
			);
	
	public function addRelation($positionId, $environmentFactorId, $category, $protectionMeasures, $measurementTaken,
			$note, $private){
		try{
			$data['id_position'] = $positionId;
			$data['id_environment_factor'] = $environmentFactorId;
			$data['category'] = $category;
			$data['protection_measures'] = $protectionMeasures;
			$data['measurement_taken'] = $measurementTaken;
			$data['note'] = $note;
			$data['private'] = $private;
			$this->insert($data);
		}
		catch(Exception $e){
			$data['category'] = $category;
			$data['protection_measures'] = $protectionMeasures;
			$data['measurement_taken'] = $measurementTaken;
			$data['note'] = $note;
			$data['private'] = $private;
			$this->update($data, array(
					'id_position = ?' => $positionId,
					'id_environment_factor = ?' => $environmentFactorId,
					));
		}
	}
	
}