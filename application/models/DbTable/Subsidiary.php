<?php

class Application_Model_DbTable_Subsidiary extends Zend_Db_Table_Abstract {
	
	protected $_name = 'subsidiary';
	
	protected $_referenceMap = array ('Client' => array ('columns' => 'client_id', 'refTableClass' => 'Application_Model_DbTable_Client', 'refColumns' => 'id_client' ) );
	
	public function getSubsidiary($id) {
		$id = ( int ) $id;
		$row = $this->fetchRow ( 'id_subsidiary = ' . $id );
		if (! $row) {
			throw new Exception ( "Pobočka $id nebyla nalezena." );
		}
		return $row->toArray ();
	}
	
<<<<<<< HEAD
	public function addSubsidiary($subsidiaryName, $subsidiaryStreet, $subsidiaryCode, $subsidiaryTown, $invoiceStreet, $invoiceCode, $invoiceTown, $contactPerson, $phone, $email, $supervisionFrequency, $clientId, $private, $hq) {
		$data = array ('subsidiary_name' => $subsidiaryName, 'subsidiary_street' => $subsidiaryStreet, 'subsidiary_code' => $subsidiaryCode, 'subsidiary_town' => $subsidiaryTown, 'invoice_street' => $invoiceStreet, 'invoice_code' => $invoiceCode, 'invoice_town' => $invoiceTown, 'contact_person' => $contactPerson, 'phone' => $phone, 'email' => $email, 'supervision_frequency' => $supervisionFrequency, 'client_id' => $clientId, 'private' => $private, 'hq' => $hq );
		$subsidiaryId = $this->insert ( $data );
		
		if ($hq == 0) {
			//indexace pro vyhledávání
			try {
				$index = Zend_Search_Lucene::open ( Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex' );
			} catch ( Zend_Search_Lucene_Exception $e ) {
				$index = Zend_Search_Lucene::create ( Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex' );
			}
			
			$document = new Zend_Search_Lucene_Document ();
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'subsidiaryId', $subsidiaryId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryName', $subsidiaryName, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryStreet', $subsidiaryStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryTown', $subsidiaryTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'invoiceStreet', $invoiceStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'invoiceTown', $invoiceTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $clientId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'subsidiary', 'utf-8' ) );
			
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
		}
		return $subsidiaryId;
	}
	
	public function updateSubsidiary($id, $subsidiaryName, $subsidiaryStreet, $subsidiaryCode, $subsidiaryTown, $invoiceStreet, $invoiceCode, $invoiceTown, $contactPerson, $phone, $email, $supervisionFrequency, $clientId, $private, $hq) {
		$data = array ('subsidiary_name' => $subsidiaryName, 'subsidiary_street' => $subsidiaryStreet, 'subsidiary_code' => $subsidiaryCode, 'subsidiary_town' => $subsidiaryTown, 'invoice_street' => $invoiceStreet, 'invoice_code' => $invoiceCode, 'invoice_town' => $invoiceTown, 'contact_person' => $contactPerson, 'phone' => $phone, 'email' => $email, 'supervision_frequency' => $supervisionFrequency, 'client_id' => $clientId, 'private' => $private, 'hq' => $hq );
=======
	public function addSubsidiary($subsidiaryName, $subsidiaryStreet, $subsidiaryCode, $subsidiaryTown,
		$invoiceStreet, $invoiceCode, $invoiceTown, $contactPerson, $phone, $email, $supervisionFrequency,
		$clientId, $private, $hq) {
			$data = array (
				'subsidiary_name' => $subsidiaryName,
				'subsidiary_street' => $subsidiaryStreet,
				'subsidiary_code' => $subsidiaryCode,
				'subsidiary_town' => $subsidiaryTown,
				'invoice_street' => $invoiceStreet,
				'invoice_code' => $invoiceCode,
				'invoice_town' => $invoiceTown,
				'contact_person' => $contactPerson,
				'phone' => $phone,
				'email' => $email,
				'supervision_frequency' => $supervisionFrequency,
				'client_id' => $clientId,
				'private' => $private,
				'hq' => $hq
			);
		return $this->insert ( $data );
	}
	
	public function updateSubsidiary($id, $subsidiaryName, $subsidiaryStreet, $subsidiaryCode,
		$subsidiaryTown, $invoiceStreet, $invoiceCode, $invoiceTown, $contactPerson, $phone, $email,
		$supervisionFrequency, $clientId, $private, $hq) {
			$data = array (
				'subsidiary_name' => $subsidiaryName,
				'subsidiary_street' => $subsidiaryStreet,
				'subsidiary_code' => $subsidiaryCode,
				'subsidiary_town' => $subsidiaryTown,
				'invoice_street' => $invoiceStreet,
				'invoice_code' => $invoiceCode,
				'invoice_town' => $invoiceTown,
				'contact_person' => $contactPerson,
				'phone' => $phone,
				'email' => $email,
				'supervision_frequency' => $supervisionFrequency,
				'client_id' => $clientId,
				'private' => $private,
				'hq' => $hq
			);
>>>>>>> 5e236ec5c17f1229f8db9ed583672c5c27891941
		$this->update ( $data, 'id_subsidiary = ' . ( int ) $id );
	}
	
	public function deleteSubsidiary($id) {
<<<<<<< HEAD
		$subsidiary = $this->fetchRow ( 'id_subsidiary = ' . $id );
		$subsidiary->deleted = 1;
		$subsidiary->save ();
=======
		$subsidiary = $this->fetchRow('id_subsidiary = ' . $id);
		$subsidiary->deleted = 1;
		$subsidiary->save();
>>>>>>> 5e236ec5c17f1229f8db9ed583672c5c27891941
	}
	
	/**
	 * Pro plnění selectu poboček.
	 * @param int $clientId
	 */
	public function getSubsidiaries($clientId) {
<<<<<<< HEAD
		$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )->where ( 'client_id = ?', $clientId )->where ( 'hq = 0' )->where ( 'deleted = 0' );
=======
		$select = $this->select ()
			->from ( 'subsidiary' )
			->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )
			->where ( 'client_id = ?', $clientId )
			->where ( 'hq = 0' )
			->where('deleted = 0');
>>>>>>> 5e236ec5c17f1229f8db9ed583672c5c27891941
		$results = $this->fetchAll ( $select );
		if (count ( $results ) > 0) {
			$subsidiares = array ();
			foreach ( $results as $result ) :
				$key = $result->id_subsidiary;
				$subsidiary = $result->subsidiary_name . ' - ' . $result->subsidiary_town;
				$subsidiaries [$key] = $subsidiary;
			endforeach
			;
			return $subsidiaries;
		} else {
			return 0;
		}
	}
	
<<<<<<< HEAD
	public function getSubsidiariesSearch() {
		$select = $this->select ()->from ( 'subsidiary' )->where ( 'deleted = 0' )->where ( 'hq = 0' );
		return $this->fetchAll ( $select );
	}
	
	public function getByTown() {
		$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq' ) )->where ( 'deleted = 0' )->order ( 'subsidiary_town' );
		return $this->fetchAll ( $select );
=======
	public function getByTown(){
		$select = $this->select()
			->from('subsidiary')
			->columns (array('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq'))
			->where('deleted = 0')
			->order('subsidiary_town');
		return $this->fetchAll($select);
>>>>>>> 5e236ec5c17f1229f8db9ed583672c5c27891941
	}

}

