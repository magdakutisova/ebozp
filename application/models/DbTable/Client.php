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
		return $client;
	}
	
	/**************************
	 * Vrací číslo právě vloženého řádku pro potřeby vložení pobočky ihned po vložení
	 * klienta.
	 */
	public function addClient($companyName, $companyNumber, $taxNumber, $headquartersStreet,
		$headquartersCode, $headquartersTown, $invoiceStreet, $invoiceCode, $invoiceTown, $business,
		$insuranceCompany, $private) {
		$data = array (
			'company_name' => $companyName,
			'company_number' => $companyNumber,
			'tax_number' => $taxNumber,
			'headquarters_street' => $headquartersStreet,
			'headquarters_code' => $headquartersCode,
			'headquarters_town' => $headquartersTown,
			'invoice_street' => $invoiceStreet,
			'invoice_code' => $invoiceCode,
			'invoice_town' => $invoiceTown,
			'business' => $business,
			'insurance_company' => $insuranceCompany,
			'private' => $private );
		$clientId = $this->insert ( $data );
		
		//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'companyNumber', $companyNumber, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $clientId, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $companyName, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $headquartersStreet, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $headquartersTown, 'utf-8' ) );
		$document->addField (Zend_Search_Lucene_Field::text('invoiceStreet', $invoiceStreet), 'utf-8');
		$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $invoiceTown, 'utf-8'));
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
		
		$index->addDocument ( $document );
		$index->commit ();
		$index->optimize ();
		
		return $clientId;
	}
	
	public function updateClient($id, $companyName, $companyNumber, $taxNumber, $headquartersStreet,
		$headquartersCode, $headquartersTown, $invoiceStreet, $invoiceCode, $invoiceTown, $business,
		$insuranceCompany, $private) {
		$this->getClient($id);
		$data = array (
			'company_name' => $companyName,
			'company_number' => $companyNumber,
			'tax_number' => $taxNumber,
			'headquarters_street' => $headquartersStreet,
			'headquarters_code' => $headquartersCode,
			'headquarters_town' => $headquartersTown,
			'invoice_street' => $invoiceStreet,
			'invoice_code' => $invoiceCode,
			'invoice_town' => $invoiceTown,
			'business' => $business,
			'insurance_company' => $insuranceCompany,
			'private' => $private );
		$this->update ( $data, 'id_client = ' . ( int ) $id );
		
		//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$hits = $index->find ( 'companyNumber: ' . $companyNumber );
		
		foreach ( $hits as $hit ) :
			$index->delete ( $hit->id );
		endforeach
		;
		
		$document = new Zend_Search_Lucene_Document ();
		$document->addField ( Zend_Search_Lucene_Field::keyword ( 'companyNumber', $companyNumber, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $id, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $companyName, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $headquartersStreet, 'utf-8' ) );
		$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $headquartersTown, 'utf-8' ) );
		$document->addField(Zend_Search_Lucene_Field::text('invoiceStreet', $invoiceStreet, 'utf-8'));
		$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $invoiceStreet, $invoiceTown, 'utf-8'));
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
		return $this->fetchAll ( $select );
	}
	
	public function getLastOpen() {
		$select = $this->select ()->where ( 'deleted = 0' )->order ( 'open DESC' );
		return $this->fetchAll ( $select );
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
	
	public function getHeadquarters($clientId) {
		$this->getClient($clientId);
		$select = $this->select ()->from ( 'client' )->join ( 'subsidiary', 'client.id_client = subsidiary.client_id' )->where ( 'client.id_client = ?', $clientId )->where ( 'hq = 1' );
		$select->setIntegrityCheck ( false );
		$headquarters = $this->fetchAll ( $select );
		return $headquarters->current ()->toArray ();
	}
	
	public function openClient($clientId) {
		$this->getClient($clientId);
		$client = $this->fetchRow ( 'id_client = ' . $clientId );
		$client->open = new Zend_Db_Expr ( 'NOW()' );
		$client->save ();
	}

}

