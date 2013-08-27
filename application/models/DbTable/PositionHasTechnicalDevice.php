<?php

class Application_Model_DbTable_PositionHasTechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'position_has_technical_device';
	
	protected $_referenceMap = array(
		'Position' => array(
			'columns' => array('id_position'),
			'refTableClass' => 'Application_Model_DbTable_Position',
			'refColumns' => array('id_position'),
		),
		'TechnicalDevice' => array(
			'columns' => array('id_technical_device'),
			'refTableClass' => 'Application_Model_DbTable_TechnicalDevice',
			'refColumns' => array('id_technical_device'),
		),
	);
	
	public function getTechnicalDevices($positionId){
		$select = $this->select()
			->where('id_position = ?', $positionId);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		foreach($results as $result){
			$technicalDevices[] = $result->id_technical_device;
		}
		return $technicalDevices;
	}
	
	public function removeRelation($technicalDeviceId, $positionId){
		$this->delete(array(
				'id_technical_device = ?' => $technicalDeviceId,
				'id_position = ?' => $positionId,
		));
	}
	
	
	public function addRelation($positionId, $technicalDeviceId){
		try{
			$data['id_position'] = $positionId;
			$data['id_technical_device'] = $technicalDeviceId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
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
				$data['id_technical_device'] = $newId;
				$this->update($data, "id_position = $position->id_position AND id_technical_device = $oldId");
			}
			catch(Exception $e){
				$this->delete("id_position = $position->id_position AND id_technical_device = $oldId");
			}
		}
	}
	
	public function removeAllClientRelations($clientId, $technicalDeviceId){
		$select = $this->select()
			->from('position')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$positions = $this->fetchAll($select);
		foreach($positions as $position){
			$this->delete("id_position = $position->id_position AND id_technical_device = $technicalDeviceId");
		}
	}
	
}