<?php
class Application_Model_DbTable_WorkplaceHasTechnicalDevice extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_technical_device';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'TechnicalDevice' => array(
			'columns' => array('id_technical_device'),
			'refTableClass' => 'Application_Model_DbTable_TechnicalDevice',
			'refColumns' => array('id_technical_device'),
		),
	);
	
	public function getTechnicalDevices($workplaceId){
		$select = $this->select()
			->where('id_workplace = ?', $workplaceId);
		$results = $this->fetchAll($select);
		$technicalDevices = array();
		foreach($results as $result){
			$technicalDevices[] = $result->id_technical_device;
		}
		return $technicalDevices;
	}
	
	public function addRelation($workplaceId, $technicalDeviceId){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_technical_device'] = $technicalDeviceId;
			$this->insert($data);
		}
		catch (Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
	public function removeRelation($workplaceId, $technicalDeviceId){
		$this->delete(array(
			'id_workplace = ?' => $workplaceId,
			'id_technical_device = ?' => $technicalDeviceId,
		));
	}
	
	public function updateRelation($clientId, $oldId, $newId){
		$select = $this->select()
			->from('workplace')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$workplaces = $this->fetchAll($select);
		foreach($workplaces as $workplace){
			try{
				$data['id_workplace'] = $workplace->id_workplace;
				$data['id_technical_device'] = $newId;
				$this->update($data, "id_workplace = $workplace->id_workplace AND id_technical_device = $oldId");
			}
			catch(Exception $e){
				$this->delete("id_workplace = $workplace->id_workplace AND id_technical_device = $oldId");
			}
		}
	}
	
	public function removeAllClientRelations($clientId, $technicalDeviceId){
		$select = $this->select()
			->from('workplace')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$workplaces = $this->fetchAll($select);
		foreach($workplaces as $workplace){
			$this->delete("id_workplace = $workplace->id_workplace AND id_technical_device = $technicalDeviceId");
		}
	}
	
}