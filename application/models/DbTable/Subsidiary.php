<?php

class Application_Model_DbTable_Subsidiary extends Zend_Db_Table_Abstract {
	
	protected $_name = 'subsidiary';
	
	protected $_referenceMap = array ('Client' => array ('columns' => 'client_id', 'refTableClass' => 'Application_Model_DbTable_Client', 'refColumns' => 'id_client' ) );
	
	public function getSubsidiary($id) {
		$id = ( int ) $id;
		$row = $this->fetchRow ( 'id_subsidiary = ' . $id );
		$subsidiary = $row->toArray();
		if (! $row || $subsidiary['deleted']) {
			throw new Exception ( "Pobočka $id nebyla nalezena." );
		}
		return $subsidiary;
	}
	
	public function addSubsidiary($subsidiaryName, $subsidiaryStreet, $subsidiaryCode, $subsidiaryTown,
		$contactPerson, $phone, $email, $supervisionFrequency, $doctor, $clientId, $private, $hq) {
		$data = array (
			'subsidiary_name' => $subsidiaryName,
			'subsidiary_street' => $subsidiaryStreet,
			'subsidiary_code' => $subsidiaryCode,
			'subsidiary_town' => $subsidiaryTown,
			'contact_person' => $contactPerson,
			'phone' => $phone,
			'email' => $email,
			'supervision_frequency' => $supervisionFrequency,
			'doctor' => $doctor,
			'client_id' => $clientId,
			'private' => $private,
			'hq' => $hq );
		$subsidiaryId = $this->insert ( $data );
		
		if ($hq == 0) {
			//indexace pro vyhledávání
			try {
				$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
			} catch ( Zend_Search_Lucene_Exception $e ) {
				$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
			}
			
			$document = new Zend_Search_Lucene_Document ();
			$document->addField ( Zend_Search_Lucene_Field::keyword ( 'subsidiaryId', $subsidiaryId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryName', $subsidiaryName, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryStreet', $subsidiaryStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryTown', $subsidiaryTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $clientId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'subsidiary', 'utf-8' ) );
			
			$index->addDocument ( $document );
			$index->commit ();
			$index->optimize ();
		}
		return $subsidiaryId;
	}
	
	public function updateSubsidiary($id, $subsidiaryName, $subsidiaryStreet, $subsidiaryCode,
		$subsidiaryTown, $contactPerson, $phone, $email, $supervisionFrequency, $doctor, $clientId,
		$private, $hq) {
		$this->getSubsidiary($id);
		$data = array (
			'subsidiary_name' => $subsidiaryName,
			'subsidiary_street' => $subsidiaryStreet,
			'subsidiary_code' => $subsidiaryCode,
			'subsidiary_town' => $subsidiaryTown,
			'contact_person' => $contactPerson,
			'phone' => $phone,
			'email' => $email,
			'supervision_frequency' => $supervisionFrequency,
			'doctor' => $doctor,
			'client_id' => $clientId,
			'private' => $private,
			'hq' => $hq );
		$this->update ( $data, 'id_subsidiary = ' . ( int ) $id );
		
		if ($hq == 0) {
			//indexace pro vyhledávání
			try {
				$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
			} catch ( Zend_Search_Lucene_Exception $e ) {
				$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
			}
			
			$hits = $index->find ( 'subsidiaryId: ' . $id );
			
			foreach ( $hits as $hit ) :
				$index->delete ( $hit->id );
			endforeach
			;
			
			$document = new Zend_Search_Lucene_Document ();
			$document->addField ( Zend_Search_Lucene_Field::keyword ( 'subsidiaryId', $id, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryName', $subsidiaryName, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryStreet', $subsidiaryStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryTown', $subsidiaryTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $clientId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'subsidiary', 'utf-8' ) );
			
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
		
		if ($subsidiary->hq == 0) {
			//indexace pro vyhledávání
			try {
				$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
			} catch ( Zend_Search_Lucene_Exception $e ) {
				$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
			}
			
			$hits = $index->find ( 'subsidiaryId: ' . $id );
			
			foreach ( $hits as $hit ) :
				$index->delete ( $hit->id );
			endforeach
			;
			
			$index->commit ();
			$index->optimize ();
		}
	}
	
	/**
	 * Pro rozbalovací seznam poboček.
	 * @param int $clientId
	 */
	public function getSubsidiaries($clientId) {
		$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town' ) )->where ( 'client_id = ?', $clientId )->where ( 'hq = 0' )->where ( 'deleted = 0' );
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
	
	public function getSubsidiariesSearch() {
		$select = $this->select ()->from ( 'subsidiary' )->where ( 'deleted = 0' )->where ( 'hq = 0' );
		return $this->fetchAll ( $select );
	}
	
	public function getByTown() {
		$select = $this->select ()->from ( 'subsidiary' )->columns ( array ('id_subsidiary', 'subsidiary_name', 'subsidiary_town', 'client_id', 'hq' ) )->where ( 'deleted = 0' )->order ( 'subsidiary_town' );
		return $this->fetchAll ( $select );
	}

}

