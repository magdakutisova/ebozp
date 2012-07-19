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
    		return $this->insert($data);
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

