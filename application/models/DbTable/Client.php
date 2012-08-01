<?php

class Application_Model_DbTable_Client extends Zend_Db_Table_Abstract {
	
	protected $_name = 'client';
	protected $_rowClass = 'Application_Model_DbTable_Row_ClientRow';
	//TODO loader pro tabulky
	
	/************************************************************
	 * Zároveň slouží ke zjištění, zda klient nebyl vymazán.
	 */
	public function getClient($id) {
		$id = ( int ) $id;
		$row = $this->fetchRow ( 'id_client = ' . $id );
		$client = $row->toArray();
		if (! $row || $client['deleted']) {
			throw new Exception ( "Klient $id nebyl nalezen." );
		}
		return new Application_Model_Client($client);
	}
	
	/**************************
	 * Vrací číslo právě vloženého řádku pro potřeby vložení pobočky ihned po vložení
	 * klienta.
	 */
	public function addClient(Application_Model_Client $client) {
		$data = $client->toArray();
		$clientId = $this->insert ( $data );
		
		//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'companyNumber', $client->getCompanyNumber(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $client->getIdClient(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $client->getCompanyName(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $client->getHeadquartersStreet(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $client->getHeadquartersTown(), 'utf-8' ) );
		$document->addField (Zend_Search_Lucene_Field::text('invoiceStreet', $client->getInvoiceStreet()), 'utf-8');
		$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $client->getInvoiceTown(), 'utf-8'));
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
		
		$index->addDocument ( $document );
		$index->commit ();
		$index->optimize ();
		
		return $clientId;
	}
	
	public function updateClient(Application_Model_Client $client) {
		$this->getClient($client->getIdClient());
		$data = $client->toArray();
		$this->update ( $data, 'id_client = ' . $client->getIdClient() );
		
		//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$hits = $index->find ( 'companyNumber: ' . $client->getCompanyNumber() );
		
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
		
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'companyNumber', $client->getCompanyNumber(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $client->getIdClient(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $client->getCompanyName(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $client->getHeadquartersStreet(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $client->getHeadquartersTown(), 'utf-8' ) );
		$document->addField (Zend_Search_Lucene_Field::text('invoiceStreet', $client->getInvoiceStreet()), 'utf-8');
		$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $client->getInvoiceTown(), 'utf-8'));
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
		
		$index->addDocument ( $document );
		$index->commit ();
		$index->optimize ();
	}
	
	public function deleteClient($id) {
		$this->getClient($id);
		$client = $this->fetchRow ( 'id_client = ' . $id );
		$client->deleted = 1;
		$client->save ();
		$subsidiaries = $client->getAllSubsidiaries ();
		foreach ( $subsidiaries as $subsidiary ) {
			$subsidiary->deleted = 1;
			$subsidiary->save ();
		}
		
		//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		//klient
		$companyNumber = $client->company_number;
		
		$hits = $index->find ( 'companyNumber: ' . $companyNumber );
		
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
		
		//pobočka
		foreach ( $subsidiaries as $subsidiary ) {			
			$subsidiaryId = $subsidiary->id_subsidiary;
			$hits = $index->find ( 'subsidiaryId: ' . $subsidiaryId );
			
			foreach ( $hits as $hit ) :
				$index->delete ( $hit->id );
			endforeach
			;
		}
		
		$index->commit ();
		$index->optimize ();
	
	}
	
	public function getClients() {
		$select = $this->select ()->where ( 'deleted = 0' )->order ( 'company_name' );
		$result =  $this->fetchAll ( $select );
		return $this->process($result);
	}
	
	public function getLastOpen() {
		$select = $this->select ()->where ( 'deleted = 0' )->order ( 'open DESC' );
		$result = $this->fetchAll ( $select );
		return $this->process($result);
	}
	
	/******
	 * @returns bool existuje ICO
	 */
	public function existsCompanyNumber($companyNumber) {
		$ico = $this->fetchAll ( $this->select ()->from ( 'client' )->columns ( 'company_number' )->where ( 'deleted = 0' )->where ( 'company_number = ?', $companyNumber ) );
		if (count ( $ico ) != 0) {
			return true;
		}
		return false;
	}
	
	public function getCompanyNumber($clientId) {
		$this->getClient($clientId);
		$companyNumber = $this->fetchAll ( $this->select ()->from ( 'client' )->columns ( 'company_number' )->where ( 'id_client = ?', $clientId ) );
		return $companyNumber->current ()->company_number;
	}
	
	public function getCompanyName($clientId) {
		$this->getClient($clientId);
		$companyName = $this->fetchAll ( $this->select ()->from ( 'client' )->columns ( 'company_name' )->where ( 'id_client = ?', $clientId ) );
		return $companyName->current ()->company_name;
	}
	
	public function openClient($clientId) {
		$this->getClient($clientId);
		$client = $this->fetchRow ( 'id_client = ' . $clientId );
		$client->open = new Zend_Db_Expr ( 'NOW()' );
		$client->save ();
	}
	
	public function getSubsidiaries($clientId){
		$client = $this->fetchRow('id_client = ' . $clientId);
		$select = $client->select()->where('deleted = 0')->where('hq = 0');
		$subsidiaries = $client->findDependentRowset('Application_Model_DbTable_Subsidiary', 'Client', $select);
		if (count($subsidiaries)){
			$results = array();
			foreach ($subsidiaries as $subsidiary){
				$results[] = $subsidiary['id_subsidiary'];
			}
			return $results;
		}
		return 0;
	}
	
	private function process($result){
		if ($result->count()){
			$clients = array();
			foreach($result as $client){
				$client = $result->current();
				$clients[] = $this->processClient($client);
			}
			return $clients;
		}
	}
	
	private function processClient($client){
		$data = $client->toArray();
		return new Application_Model_Client($data);
	}

}

