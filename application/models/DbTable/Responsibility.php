<?php
class Application_Model_DbTable_Responsibility extends Zend_Db_Table_Abstract{
	
	protected $_name = 'responsibility';
	
	protected $_referenceMap = array(
			'Client' => array(
					'columns' => 'client_id',
					'refTableClass' => 'Application_Model_DbTable_Client',
					'refColumns' => 'id_client',
					),
			);
	
	public function getResponsibility($id){
		$id = (int)$id;
		$row = $this->fetchRow('id_responsibility = ' . $id);
		if(!$row){
			throw new Exception("Typ odpovědnosti $id nebyl nalezen.");
		}
		$responsibility = $row->toArray();
		return new Application_Model_Responsibility($responsibility);
	}
	
	public function addResponsibility(Application_Model_Responsibility $responsibility){
		$data = $responsibility->toArray();
		$responsibilityId = $this->insert($data);
		return $responsibilityId;
	}
	
	public function updateResponsibility(Application_Model_Responsibility $responsibility){
		$data = $responsibility->toArray();
		$this->update($data, 'id_responsibility = ' . $responsibility->getIdResponsibility());
	}
	
	public function deleteResponsibility($id){
		$this->delete('id_responsibility = ' . (int)$id);
	}
	
	/*****
	 * Vrátí pole odpovědností "ID - název odpovědnosti" pouze těch odpovědností, která má klient
	 * navíc oproti defaultnímu seznamu.
	*/
	public function getExtraResponsibilities($clientId){
		$select = $this->select()
			->from('responsibility')
			->where('client_id = ?', $clientId)
			->where('responsibility.id_responsibility > 6')
			->group('responsibility.id_responsibility');
		$select->setIntegrityCheck(false);
		$results = $this->fetchAll($select);
		$responsibilities = array();
		if(count($results) > 0){
			foreach($results as $result){
				$key = $result->id_responsibility;
				$responsibilities[$key] = $result->responsibility;
			}
		}
		return $responsibilities;
	}
	
}