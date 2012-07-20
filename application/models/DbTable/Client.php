<?php

class Application_Model_DbTable_Client extends Zend_Db_Table_Abstract
{

    protected $_name = 'client';
    protected $_rowClass = 'Application_Model_DbTable_Row_ClientRow';
    //TODO loader pro tabulky
    
    public function getClient($id){
    	$id = (int)$id;
    	$row = $this->fetchRow('id_client = ' . $id);
    	if (!$row) {
    		throw new Exception("Klient $id nebyl nalezen.");
    	}
    	return $row->toArray();
    }
    
    /**************************
     * Vrací číslo právě vloženého řádku pro potřeby vložení pobočky ihned po vložení
     * klienta.
     */
    public function addClient($companyName, $companyNumber, $taxNumber,
    	$headquartersStreet, $headquartersCode, $headquartersTown, $business, $private){
    		$data = array(
    			'company_name' => $companyName,
    			'company_number' => $companyNumber,
    			'tax_number' => $taxNumber,
    			'headquarters_street' => $headquartersStreet,
    			'headquarters_code' => $headquartersCode,
    			'headquarters_town' => $headquartersTown,
    			'business' => $business,
    			'private' => $private,
    		);
    		$clientId = $this->insert($data);
    		
    		//indexace pro vyhledávání
    		try{
				$index = Zend_Search_Lucene::open(Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex');
			}
			catch (Zend_Search_Lucene_Exception $e){
				$index = Zend_Search_Lucene::create(Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex');
			}

			$document = new Zend_Search_Lucene_Document ();
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $clientId, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $companyName, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $headquartersStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $headquartersTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
			
			$index->addDocument ( $document );
			$index->commit();
			$index->optimize();
    		
    		return $clientId;
    }
    	
    public function updateClient($id, $companyName, $companyNumber,
    	$taxNumber, $headquartersStreet, $headquartersCode, $headquartersTown,  $business, $private){
    		$data = array(
    			'company_name' => $companyName,
    			'company_number' => $companyNumber,
    			'tax_number' => $taxNumber,
    			'headquarters_street' => $headquartersStreet,
    			'headquarters_code' => $headquartersCode,
    			'headquarters_town' => $headquartersTown,
    			'business' => $business,
    			'private' => $private,
    		);
    		$this->update($data, 'id_client = ' . (int)$id);
    		
    		//indexace pro vyhledávání
    		try{
				$index = Zend_Search_Lucene::open(Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex');
			}
			catch (Zend_Search_Lucene_Exception $e){
				$index = Zend_Search_Lucene::create(Zend_Controller_Front::getInstance ()->getBaseUrl () . '/searchIndex');
			}
			
			Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive());
			$hits = $index->find('clientId: ' . $id);
			//TODO !!!debug
			//Zend_Debug::dump($hits);
			//die(); 
			foreach($hits as $hit):
				$index->delete($hit->id);
			endforeach;

			$document = new Zend_Search_Lucene_Document ();
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $id, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $companyName, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $headquartersStreet, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $headquartersTown, 'utf-8' ) );
			$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
			
			$index->addDocument ( $document );
			$index->commit();
			$index->optimize();
    }
    	
    public function deleteClient($id){
    	$client = $this->fetchRow('id_client = ' . $id);
    	$client->deleted = 1;
		$client->save();
		$subsidiaries = $client->getAllSubsidiaries();
		foreach ($subsidiaries as $subsidiary){
			$subsidiary->deleted = 1;
			$subsidiary->save();
		}
    }
    
    public function getClients(){
    	$select = $this->select()->where('deleted = 0')->order('company_name');
    	return $this->fetchAll($select);
    }
    
	public function getLastOpen(){
    	$select = $this->select()->where('deleted = 0')->order('open DESC');
    	return $this->fetchAll($select);
    }
       
    /******
     * @returns bool existuje ICO
     */
    public function existsCompanyNumber($companyNumber){
    	$ico = $this->fetchAll($this->select()
    		->from('client')
    		->columns('company_number')
    		->where('deleted = 0')
    		->where('company_number = ?', $companyNumber));
    	if(count($ico) != 0){
    		return true;
    	}
    	return false;
    }
    
	public function getCompanyNumber($clientId){
    	$companyNumber = $this->fetchAll($this->select()
    		->from('client')
    		->columns('company_number')
    		->where('id_client = ?', $clientId));
    	return $companyNumber->current()->company_number;
    }
    
	public function getCompanyName($clientId){
    	$companyName = $this->fetchAll($this->select()
    		->from('client')
    		->columns('company_name')
    		->where('id_client = ?', $clientId));
    	return $companyName->current()->company_name;
    }
    
    public function getHeadquarters($clientId){
    	$select = $this->select()
    		->from('client')
    		->join('subsidiary', 'client.id_client = subsidiary.client_id')
    		->where('client.id_client = ?', $clientId)
    		->where('hq = 1');
    	$select->setIntegrityCheck(false);
    	$headquarters = $this->fetchAll($select);
    	return $headquarters->current()->toArray();
    }
    
    public function openClient($clientId){
    	$client = $this->fetchRow('id_client = ' . $clientId);
    	$client->open = new Zend_Db_Expr('NOW()');
    	$client->save();
    }
    
}

