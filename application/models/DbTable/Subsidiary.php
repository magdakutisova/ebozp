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
		$this->update ( $data, 'id_subsidiary = ' . ( int ) $id );
	}
	
	public function deleteSubsidiary($id) {
		$subsidiary = $this->fetchRow('id_subsidiary = ' . $id);
		$subsidiary->deleted = 1;
		$subsidiary->save();
	}
	
	/**
	 * Pro plnění selectu poboček.
	 * @param int $clientId
	 */
	public function getSubsidiaries($clientId) {
		$select = $this->select ()
			->from ( 'subsidiary' )
			->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )
			->where ( 'client_id = ?', $clientId )
			->where ( 'hq = 0' )
			->where('deleted = 0');
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
	
	public function getByTown(){
		$select = $this->select()
			->from('subsidiary')
			->columns (array('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq'))
			->where('deleted = 0')
			->order('subsidiary_town');
		return $this->fetchAll($select);
	}

}

