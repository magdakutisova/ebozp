<?php

class Application_Model_DbTable_Client extends Zend_Db_Table_Abstract {
	
	protected $_name = 'client';
	protected $_rowClass = 'Application_Model_DbTable_Row_ClientRow';
	
	/************************************************************
	 * Zároveň slouží ke zjištění, zda klient nebyl vymazán.
	 */
	public function getClient($id) {
		$id = ( int ) $id;
		$row = $this->fetchRow ( 'id_client = ' . $id );
		if (! $row ) {
			throw new Exception ( "Klient $id nebyl nalezen." );
		}
		$client = $row->toArray();
		if( $client['deleted']){
			throw new Exception("Klient $id byl v minulosti vymazán");
		}
		return new Application_Model_Client($client);
	}
	
	/**************************
	 * Vrací číslo právě vloženého řádku pro potřeby vložení pobočky ihned po vložení
	 * klienta, nebo false když nebyl vložen.
	 */
	public function addClient(Application_Model_Client $client) {
		if($client->getCompanyNumber()){
			//když ico neexistuje, ulozit, vratit ID
			$companyNumberRow = $this->existsCompanyNumber($client->getCompanyNumber());
			if(!$companyNumberRow){
				$data = $client->toArray();
				$clientId = $this->insert ( $data );
				$client->setIdClient($clientId);
			
				//indexace pro vyhledávání
				$index = $this->getSearchIndex();
				$document = $this->composeDocument($client);
				$index->addDocument ( $document );
				$index->commit ();
				$index->optimize ();
			
				return $clientId;
			}
			//kdyz ico existuje a klient je smazan, prepsat, vratit ID
			elseif($companyNumberRow->deleted == "1"){
				$companyNumber = $companyNumberRow->company_number;
				$this->delete(array('company_number = ?' => $companyNumber));
				$data = $client->toArray();
				$clientId = $this->insert ( $data );
			
				//indexace pro vyhledávání
				$index = $this->getSearchIndex();
				$this->removeClientFromSearchIndex($index, $companyNumber);
				$document = $this->composeDocument($client);
				$index->addDocument ( $document );
				$index->commit ();
				$index->optimize ();
			
				return $clientId;
			}
			//kdyz ico existuje a klient neni smazan, vratit false
			else {
				return false;
			}
		}
		else{
			//když ičo není zadáno - výhradně import klientů, nikoli zadání z formuláře
			$data = $client->toArray();
			$clientId = $this->insert ( $data );
			$client->setIdClient($clientId);
			
			//indexace pro vyhledávání
			$index = $this->getSearchIndex();
			$document = $this->composeDocument($client);
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
				
			return $clientId;
		}
	}
	
	public function updateClient(Application_Model_Client $client) {
		if($client->getCompanyNumber()){
			$companyNumberRow = $this->existsCompanyNumber($client->getCompanyNumber());
			if(!($companyNumberRow) || $client->getCompanyNumber() == $this->getCompanyNumber($client->getIdClient())){
				$this->getClient($client->getIdClient());
				$data = $client->toArray();
				$this->update ( $data, 'id_client = ' . $client->getIdClient() );
			
				//indexace pro vyhledávání
				$index = $this->getSearchIndex();
				$companyNumber = $client->getCompanyNumber();
				$this->removeClientFromSearchIndex($index, $companyNumber);
				$document = $this->composeDocument($client);
				$index->addDocument ( $document );
				$index->commit ();
				$index->optimize ();
				return true;
			}
			elseif ($companyNumberRow->deleted == "1"){
				$companyNumber = $companyNumberRow->company_number;
				$this->delete(array('company_number = ?', $companyNumber));
				$this->getClient($client->getIdClient());
				$data = $client->toArray();
				$this->update ( $data, 'id_client = ' . $client->getIdClient() );
			
				//indexace pro vyhledávání
				$index = $this->getSearchIndex();
				$companyNumber = $client->getCompanyNumber();
				$this->removeClientFromSearchIndex($index, $companyNumber);
				$document = $this->composeDocument($client);
				$index->addDocument ( $document );
				$index->commit ();
				$index->optimize ();
				return true;
			}
			else{
				return false;
			}
		}
		else{
			$this->getClient($client->getIdClient());
			$data = $client->toArray();
			$this->update ( $data, 'id_client = ' . $client->getIdClient() );
				
			//indexace pro vyhledávání
			$index = $this->getSearchIndex();
			$companyNumber = $client->getCompanyNumber();
			$this->removeClientFromSearchIndex($index, $companyNumber);
			$document = $this->composeDocument($client);
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
			return true;
		}
	}
	
	public function deleteClient($id, $completely = false) {
		if($completely){
			$this->delete('id_client = ' . (int)$id);
			return;
		}
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
		$index = $this->getSearchIndex();
		$companyNumber = $client->company_number;		
		$this->removeClientFromSearchIndex($index, $companyNumber);
		foreach ( $subsidiaries as $subsidiary ) {			
			$subsidiaryId = $subsidiary->id_subsidiary;
			$this->removeSubsidiaryFromSearchIndex($index, $subsidiaryId);
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
	 * @returns false když IČO neexistuje, řádek s klientem když IČO existuje
	 */
	public function existsCompanyNumber($companyNumber) {
		$ico = $this->fetchAll ( $this->select ()->from ( 'client' )->where ( 'company_number = ?', $companyNumber ) );
		if (count ( $ico ) != 0) {
			return $ico->current();
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
    
    /**
     * vraci prubeh auditu a dohlidek u klienta
     */
    public function getProgress(array $clientIds = null) {
        // pripojeni tabulky s dohlidkami
        $tableWatches = new Audit_Model_Watches();
        $wSelect = $tableWatches->createCountSelect();
        
        // propojeni s audity/proverkami
        $tableAudits = new Audit_Model_Audits();
        $aSelect = $tableAudits->createCountSelect();
        
        // zakladni select
        $select = new Zend_Db_Select($this->getAdapter());
        $select->from(array("c" => $this->_name), array(
            "c.*",
            "audits_count" => new Zend_Db_Expr("COUNT(s.id_subsidiary)"),
            "audits_done" => new Zend_Db_Expr("(" . $aSelect->assemble() . ")"),
            "watches_count" => new Zend_Db_Expr("SUM(s.supervision_frequency)"),
            "watches_done" => new Zend_Db_Expr("(" . $wSelect->assemble() . ")")
        ));
        
        $select->group("c.id_client")->order(array("company_name", "headquarters_town"));
        
        // kontrola zuzeneho vyberu klientu
        if (!is_null($clientIds)) {
            $select->where("c.id_client in (?)", $clientIds);
        }
        
        // pripojeni na pobocky
        $tableSubs = new Application_Model_DbTable_Subsidiary();
        $nameSubs = $tableSubs->info("name");
        
        $select->joinLeft(array("s" => $nameSubs), "s.client_id = c.id_client", array());
        
        $data = $select->query()->fetchAll();
        
        return new Zend_Db_Table_Rowset(array("data" => $data, "table" => $this, "stored" => true));
    }
	
	public function openClient($clientId) {
		$this->getClient($clientId);
		$client = $this->fetchRow ( 'id_client = ' . $clientId );
		$client->open = new Zend_Db_Expr ( 'NOW()' );
		$client->save ();
	}
	
	/*************************************************************
	 * Vrací ID závislých poboček.
	 */
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
	
	public function getByNameAndAddress($companyName, $headquartersStreet, $headquartersTown){
		$select = $this->select()
			->where('company_name = ?', $companyName)
			->where('headquarters_street = ?', $headquartersStreet)
			->where('headquarters_town = ?', $headquartersTown);
		$result = $this->fetchAll($select);
		if(count($result)){
			if(count($result) > 1){
				return -1;
			}
			else{
				return $result->current()->id_client;
			}
		}
		else{
			return 0;
		}
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
	
	private function getSearchIndex(){
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		return $index;
	}

	private function composeDocument($client){
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'companyNumber', $client->getCompanyNumber(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $client->getIdClient(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $client->getCompanyName(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $client->getHeadquartersStreet(), 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $client->getHeadquartersTown(), 'utf-8' ) );
		$document->addField (Zend_Search_Lucene_Field::text('invoiceStreet', $client->getInvoiceStreet()), 'utf-8');
		$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $client->getInvoiceTown(), 'utf-8'));
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
		return $document;
	}
	
	private function removeClientFromSearchIndex($index, $companyNumber){
		$hits = $index->find ( 'companyNumber: ' . $companyNumber );
		
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
	}
	
	private function removeSubsidiaryFromSearchIndex($index, $subsidiaryId){
		$hits = $index->find ( 'subsidiaryId: ' . $subsidiaryId );
			
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
	}
}

