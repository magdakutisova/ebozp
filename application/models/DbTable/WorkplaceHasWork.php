<?php
class Application_Model_DbTable_WorkplaceHasWork extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_work';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Work' => array(
			'columns' => array('id_work'),
			'refTableClass' => 'Application_Model_DbTable_Work',
			'refColumns' => array('id_work'),
		),
	);
	
	public function getWorks($workplaceId){
		$select = $this->select()
			->where('id_workplace = ?', $workplaceId);
		$results = $this->fetchAll($select);
		$works = array();
		foreach($results as $result){
			$works[] = $result->id_work;
		}
		return $works;
	}
	
	public function addRelation($workplaceId, $workId){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_work'] = $workId;
			$this->insert($data);
		}
		catch(Exception $e){
			//porušení integrity se ignoruje
		}
	}
	
	public function removeRelation($workplaceId, $workId){
		$this->delete(array(
			'id_workplace = ?' => $workplaceId,
			'id_work = ?' => $workId,
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
				$data['id_work'] = $newId;
				$this->update($data, "id_workplace = $workplace->id_workplace AND id_work = $oldId");
			}
			catch(Exception $e){
				$this->delete("id_workplace = $workplace->id_workplace AND id_work = $oldId");
			}
		}
	}
	
	public function removeAllClientRelations($clientId, $workId){
		$select = $this->select()
			->from('workplace')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$workplaces = $this->fetchAll($select);
		foreach($workplaces as $workplace){
			$this->delete("id_workplace = $workplace->id_workplace AND id_work = $workId");
		}
	}
	
}