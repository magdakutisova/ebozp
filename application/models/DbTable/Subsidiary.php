<?php

class Application_Model_DbTable_Subsidiary extends Zend_Db_Table_Abstract {
	
	protected $_name = 'subsidiary';
	
	protected $_referenceMap = array ('Client' => array (
		'columns' => 'client_id',
		'refTableClass' => 'Application_Model_DbTable_Client',
		'refColumns' => 'id_client' ) );
	
	public function getSubsidiary($id, $every = false) {
		$id = ( int ) $id;

		$row = $this->fetchRow ( 'id_subsidiary = ' . $id );
		
		if (! $row ) {
			throw new Exception ( "Pobočka $id nebyla nalezena." );
		}
		
		$subsidiary = $row->toArray();
		if ($subsidiary['deleted'] && !$every) {
			throw new Exception ( "Pobočka $id nebyla nalezena." );
		}
		return new Application_Model_Subsidiary($subsidiary);
	}
	
	public function addSubsidiary(Application_Model_Subsidiary $subsidiary) {
		$data = $subsidiary->toArray();
		$subsidiaryId = $this->insert ( $data );
		$subsidiary->setIdSubsidiary($subsidiaryId);
		
		if ($subsidiary->getHq() == 0) {
			//indexace pro vyhledávání
			$index = $this->getSearchIndex();			
			$document = $this->composeDocument($subsidiary);			
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
		}
		return $subsidiaryId;
	}
	
	public function updateSubsidiary(Application_Model_Subsidiary $subsidiary) {
		$this->getSubsidiary($subsidiary->getIdSubsidiary());
		$data = $subsidiary->toArray();
		$this->update ( $data, 'id_subsidiary = ' . $subsidiary->getIdSubsidiary() );
		
		if ($subsidiary->getHq() == 0) {
			//indexace pro vyhledávání
			$index = $this->getSearchIndex();			
			$this->removeSubsidiaryFromSearchIndex($index, $subsidiary->getIdSubsidiary());			
			$document = $this->composeDocument($subsidiary);
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
		}
	}
	
	public function deleteSubsidiary($id) {
		$this->getSubsidiary($id);
		$subsidiary = $this->fetchRow ( 'id_subsidiary = ' . $id );
		$subsidiary->deleted = 1;
		$subsidiary->save ();
		
		//smazání závislých pracovišť/FPP/rizik
		$workplaces = new Application_Model_DbTable_Workplace();
		$toDelete = $workplaces->getBySubsidiary($id);
		foreach ($toDelete as $workplace){
			$workplaces->deleteWorkplace($workplace->getIdWorkplace());
		}
		
		if ($subsidiary->hq == 0) {
			//indexace pro vyhledávání
			$index = $this->getSearchIndex();
			$this->removeSubsidiaryFromSearchIndex($index, $id);	
			$index->commit ();
			$index->optimize ();
		}
	}
	
	/**
	 * Vrací pole pro různé seznamy poboček.
	 * @param int $clientId
	 */
	public function getSubsidiaries($clientId = 0, $userId = 0, $hq = 0) {
		if ($clientId != 0 && $hq == 0){
			$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )->where ( 'client_id = ?', $clientId )->where ( 'hq = 0' )->where ( 'deleted = 0' );
		}
		elseif ($clientId != 0 && $hq == 1){
			$select = $this->select()->from('subsidiary')->columns(array('id_subsidiary', 'subsidiary_name', 'subsidiary_town'))->where('client_id = ?', $clientId)->where('deleted = 0')->order('hq DESC');
		}
		elseif($userId != 0){
			$select = $this->select()->from('subsidiary')->join('user_has_subsidiary', 'subsidiary.id_subsidiary = user_has_subsidiary.id_subsidiary')->join('client', 'subsidiary.client_id = client.id_client')->columns(array('id_subsidiary', 'subsidiary_name', 'subsidiary_town'))->where('user_has_subsidiary.id_user = ?', $userId)->where ( 'subsidiary.deleted = 0' )->order(array('client.company_name', 'hq DESC'));
			$select->setIntegrityCheck(false);
		}
		else{
			$select = $this->select ()->from ( 'subsidiary' )->join('client', 'subsidiary.client_id = client.id_client')->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )->where ( 'subsidiary.deleted = 0' )->order(array('client.company_name', 'hq DESC'));
			$select->setIntegrityCheck(false);
		}
		$results = $this->fetchAll ( $select );
		if (count ( $results ) > 0) {
			$subsidiares = array ();
			foreach ( $results as $result ) :
				$key = $result->id_subsidiary;
				$subsidiary = $result->subsidiary_name . ' - ' . $result->subsidiary_street . ', ' . $result->subsidiary_town;
				if($result->hq){
					$subsidiary .= ' (centrála)';
				}
				$subsidiaries [$key] = $subsidiary;
			endforeach
			;
			return $subsidiaries;
		} else {
			return 0;
		}
	}
	
	public function getSubsidiariesSearch() {
		$select = $this->select ()->from ( 'subsidiary' )->where ( 'deleted = 0' )->where ( 'hq = 0' );
		$result = $this->fetchAll ( $select );
		return $this->process($result);
	}
	
	public function getSubsidiariesComplete($clientId) {
		$select = $this->select ()->from ( 'subsidiary' )->where('client_id = ?', $clientId)->where ( 'deleted = 0' )->order('hq DESC');
		$result = $this->fetchAll ( $select );
		return $this->process($result);
	}
	
	/******************
	 * Vrátí centrální pobočku klienta.
	 */
	public function getHeadquarters($clientId){
		$select = $this->select()->from('subsidiary')->where('client_id = ?', $clientId)->where('hq = 1');
		$result = $this->fetchAll($select);
		return new Application_Model_Subsidiary($result->current()->toArray());
	}
	
	public function getHeadquartersWithDetails($clientId){
		$headquarters = array();
		$headquarters['subsidiary'] = $this->getHeadquarters($clientId);
		
		$contactPersons = $this->select()->from('subsidiary')
			->joinLeft('contact_person', 'subsidiary.id_subsidiary = contact_person.subsidiary_id')
			->where('subsidiary.client_id = ?', $clientId)
			->where('subsidiary.hq = 1');
		$contactPersons->setIntegrityCheck(false);
		$contactPersonsResult = $this->fetchAll($contactPersons);
		
		$doctors = $this->select()->from('subsidiary')
			->joinLeft('doctor', 'subsidiary.id_subsidiary = doctor.subsidiary_id')
			->where('subsidiary.client_id = ?', $clientId)
			->where('subsidiary.hq = 1');
		$doctors->setIntegrityCheck(false);
		$doctorsResult = $this->fetchAll($doctors);
		
		$responsibles = $this->select()->from('subsidiary')
			->joinLeft('responsible', 'subsidiary.id_subsidiary = responsible.id_subsidiary')
			->joinLeft('responsibility', 'responsible.id_responsibility = responsibility.id_responsibility')
			->joinLeft('employee', 'responsible.id_employee = employee.id_employee')
			->where('subsidiary.client_id = ?', $clientId)
			->where('subsidiary.hq = 1');
		$responsibles->setIntegrityCheck(false);
		$responsiblesResult = $this->fetchAll($responsibles);
		
		foreach($contactPersonsResult as $contactPerson){
			$headquarters['contact_persons'][] = new Application_Model_ContactPerson($contactPerson->toArray());
		}
		foreach($doctorsResult as $doctor){
			$headquarters['doctors'][] = new Application_Model_Doctor($doctor->toArray());
		}
		$i = 0;
		foreach($responsiblesResult as $responsible){
			$headquarters['responsibles'][$i]['id_responsibility'] = $responsible->id_responsibility;
			$headquarters['responsibles'][$i]['responsibility'] = $responsible->responsibility;
			$headquarters['responsibles'][$i]['employee'] = new Application_Model_Employee($responsible->toArray());
			$i++;
		}
		
		return $headquarters;
	}
	
	public function getSubsidiaryWithDetails($subsidiaryId){
		$subsidiary = array();
		$subsidiary['subsidiary'] = $this->getSubsidiary($subsidiaryId);
	
		$contactPersons = $this->select()->from('contact_person')
			->where('subsidiary_id = ?', $subsidiaryId);
		$contactPersonsResult = $this->fetchAll($contactPersons);
	
		$doctors = $this->select()->from('doctor')
			->where('subsidiary_id = ?', $subsidiaryId);
		$doctorsResult = $this->fetchAll($doctors);
	
		$responsibles = $this->select()->from('responsible')
			->joinLeft('responsibility', 'responsible.id_responsibility = responsibility.id_responsibility')
			->joinLeft('employee', 'responsible.id_employee = employee.id_employee')
			->where('id_subsidiary = ?', $subsidiaryId);
		$responsibles->setIntegrityCheck(false);
		$responsiblesResult = $this->fetchAll($responsibles);
	
		foreach($contactPersonsResult as $contactPerson){
			$subsidiary['contact_persons'][] = new Application_Model_ContactPerson($contactPerson->toArray());
		}
		foreach($doctorsResult as $doctor){
			$subsidiary['doctors'][] = new Application_Model_Doctor($doctor->toArray());
		}
		$i = 0;
		foreach($responsiblesResult as $responsible){
			$subsidiary['responsibles'][$i]['id_responsibility'] = $responsible->id_responsibility;
			$subsidiary['responsibles'][$i]['responsibility'] = $responsible->responsibility;
			$subsidiary['responsibles'][$i]['employee'] = new Application_Model_Employee($responsible->toArray());
			$i++;
		}
	
		return $subsidiary;
	}
	
	public function getByTown() {
		$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq' ) )->where ( 'deleted = 0' )->order ( array('subsidiary_town', 'subsidiary_name') );
		$result = $this->fetchAll ( $select );
		return $this->process($result);
	}
	
	public function getByDistrict(){
		$select = $this->select()->from('subsidiary')->where('deleted = 0')->order(array('district', 'subsidiary_name'));
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function getByClient(){
		$select = $this->select()->from('subsidiary')->join('client', 'subsidiary.client_id = client.id_client')->columns(array('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq', 'client.company_name'))->where('subsidiary.deleted = 0')->order(array('client.company_name', 'hq DESC', 'subsidiary.subsidiary_name', 'subsidiary.subsidiary_town', 'subsidiary.subsidiary_street'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	public function getLastOpen(){
		$select = $this->select()->from('subsidiary')->join('client', 'subsidiary.client_id = client.id_client')->columns(array('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq', 'client.company_name'))->where('subsidiary.deleted = 0')->order(array('client.open DESC', 'hq DESC'));
		$select->setIntegrityCheck(false);
		$result = $this->fetchAll($select);
		return $this->process($result);
	}
	
	private function process($result){
		if ($result->count()){
			$subsidiaries = array();
			foreach($result as $subsidiary){
				$subsidiary = $result->current();
				$subsidiaries[] = $this->processSubsidiary($subsidiary);
			}
			return $subsidiaries;
		}
	}
	
	private function processSubsidiary($subsidiary){
		$data = $subsidiary->toArray();
		return new Application_Model_Subsidiary($data);
	}
	
	private function getSearchIndex(){
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		return $index;
	}
	
	private function composeDocument($subsidiary){
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'subsidiaryId', $subsidiary->getIdSubsidiary(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryName', $subsidiary->getSubsidiaryName(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryStreet', $subsidiary->getSubsidiaryStreet(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryTown', $subsidiary->getSubsidiaryTown(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $subsidiary->getClientId(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'subsidiary', 'utf-8' ) );
		return $document;
	}
	
	private function removeSubsidiaryFromSearchIndex($index, $subsidiaryId){
		$hits = $index->find ( 'subsidiaryId: ' . $subsidiaryId );
			
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
	}

}

