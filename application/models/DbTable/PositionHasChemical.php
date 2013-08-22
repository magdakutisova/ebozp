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
	
	public function updateRelation($clientId, $oldId, $newId){
		$select = $this->select()
			->from('position')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$positions = $this->fetchAll($select);
		foreach($positions as $position){
			try{
				$data['id_position'] = $position->id_position;
				$data['id_chemical'] = $newId;
				$this->update($data, "id_position = $position->id_position AND id_chemical = $oldId");
			}
			catch(Exception $e){
				//už to tam je ale musím vymazat aspoň starý záznam
				$this->delete("id_position = $position->id_position AND id_chemical = $oldId");
			}
		}
	}
	
	public function removeAllClientRelations($clientId, $chemicalId){
		$select = $this->select()
			->from('position')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$positions = $this->fetchAll($select);
		foreach($positions as $position){
			$this->delete("id_position = $position->id_position AND id_chemical = $chemicalId");
		}
	}
	
}