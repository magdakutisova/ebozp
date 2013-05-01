<?php

class Application_Model_DbTable_PositionHasChemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_chemical';
	
	protected $_referenceMap = array(
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
		'Chemical' => array(
			'columns' => array('id_chemical'),
			'refTableClass' => 'Application_Model_DbTable_Chemical',
			'refColumns' => array('id_chemical'),
		),
	);
	
	public function getChemicals($positionId){
		$select = $this->select()
			->where('id_position = ?', $positionId);
		$results = $this->fetchAll($select);
		$chemicals = array();
		foreach($results as $result){
			$chemicals[] = $result->id_chemical;
		}
		return $chemicals;
	}
	
	public function removeRelation($chemicalId, $positionId){
		$this->delete(array(
				'id_chemical = ?' => $chemicalId,
				'id_position = ?' => $positionId,
		));
	}
	
	public function addRelation($positionId, $chemicalId, $exposition){
		try{
			$data['id_position'] = $positionId;
			$data['id_chemical'] = $chemicalId;
			$data['exposition'] = $exposition;
			$this->insert($data);
		}
		catch(Exception $e){
			$data['exposition'] = $exposition;
			$this->update($data, array(
					'id_position = ?' => $positionId,
					'id_chemical = ?' => $chemicalId,
			));
		}
	}
	
}