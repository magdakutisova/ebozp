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