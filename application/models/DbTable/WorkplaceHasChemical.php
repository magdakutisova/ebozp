<?php
class Application_Model_DbTable_WorkplaceHasChemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'workplace_has_chemical';
	
	protected $_referenceMap = array(
		'Workplace' => array(
			'columns' => array('id_workplace'),
			'refTableClass' => 'Application_Model_DbTable_Workplace',
			'refColumns' => array('id_workplace'),
		),
		'Chemical' => array(
			'columns' => array('id_chemical'),
			'refTableClass' => 'Application_Model_DbTable_Chemical',
			'refColumns' => array('id_chemical'),
		),
	);
	
	public function getChemicals($workplaceId){
		$select = $this->select()
			->where('id_workplace = ?', $workplaceId);
		$results = $this->fetchAll($select);
		$chemicals = array();
		foreach($results as $result){
			$chemicals[] = $result->id_chemical;
		}
		return $chemicals;
	}
	
	public function addRelation($workplaceId, $chemicalId, $usePurpose, $usualAmount){
		try{
			$data['id_workplace'] = $workplaceId;
			$data['id_chemical'] = $chemicalId;
			$data['use_purpose'] = $usePurpose;
			$data['usual_amount'] = $usualAmount;
			$this->insert($data);
		}
		catch(Exception $e){
			$data['use_purpose'] = $usePurpose;
			$data['usual_amount'] = $usualAmount;
			$this->update($data, array(
					'id_workplace = ?' => $workplaceId,
					'id_chemical = ?' => $chemicalId,
					));
		}
	}
	
	public function removeRelation($workplaceId, $chemicalId){
		$this->delete(array(
			'id_workplace = ?' => $workplaceId,
			'id_chemical = ?' => $chemicalId,
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
				$data['id_chemical'] = $newId;
				$this->update($data, "id_workplace = $workplace->id_workplace AND id_chemical = $oldId");
			}
			catch(Exception $e){
				//už to tam je ale musím vymazat aspoň starý záznam
				$this->delete("id_workplace = $workplace->id_workplace AND id_chemical = $oldId");
			}
		}
	}
	
	public function removeAllClientRelations($clientId, $chemicalId){
		$select = $this->select()
			->from('workplace')
			->where('client_id = ?', $clientId);
		$select->setIntegrityCheck(false);
		
		$workplaces = $this->fetchAll($select);
		foreach($workplaces as $workplace){
			$this->delete("id_workplace = $workplace->id_workplace AND id_chemical = $chemicalId");
		}
	}
	
}