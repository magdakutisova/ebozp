<?php
class Application_Model_DbTable_Chemical extends Zend_Db_Table_Abstract{
	
	protected $_name = 'chemical';
	
	public function getChemical($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_chemical = ' . $id);
		if(!$row){
			throw new Exception("Chemická látka $id nebyla nalezena.");
		}
		$chemical = $row->toArray();
		return new Application_Model_Chemical($chemical);
	}
	
	public function addChemical(Application_Model_Chemical $chemical){
		$data = $chemical->toArray();
		$chemicalId = $this->insert($data);
		return $chemicalId;
	}
	
	public function updateChemical(Application_Model_Chemical $chemical){
		$data = $chemical->toArray();
		$this->update($data, 'id_chemical = ' . $chemical->getIdChemical());
	}
	
	public function deleteChemical($id){
		$this->delete('id_chemical = ' . (int)$id);
	}
	
	/*********************************************************************
	 * Vrací seznam ID - chemická látka.
	 */
	public function getChemicals($clientId) {
		$select = $this->select()
			->from('chemical')
			->join('client_has_chemical', 'chemical.id_chemical = client_has_chemical.id_chemical')
			->where('client_has_chemical.id_client = ?', $clientId)
			->order('chemical.chemical')
			->group('chemical');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$chemicals = array();
		$chemicals[0] = '------';
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_chemical;
				$chemicals[$key] = $result->chemical;
			}
		}
		return $chemicals;
	}
	
	public function existsChemical($chemical){
		$select = $this->select()
			->from('chemical')
			->where('chemical = ?', $chemical);
		$results = $this->fetchAll($select);
		if(count($results) > 0){
			return $results->current()->id_chemical;
		}
		else{
			return 0;
		}
	}
	
}