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
    public function addClient($companyName, $invoiceAddress, $companyNumber, $taxNumber,
    	$headquartersAddress, $business, $private){
    		$data = array(
    			'company_name' => $companyName,
    			'invoice_address' => $invoiceAddress,
    			'company_number' => $companyNumber,
    			'tax_number' => $taxNumber,
    			'headquarters_address' => $headquartersAddress,
    			'business' => $business,
    			'private' => $private,
    		);
    		return $this->insert($data);
    }
    	
    public function updateClient($id, $companyName, $invoiceAddress, $companyNumber,
    	$taxNumber, $headquartersAddress, $business, $private){
    		$data = array(
    			'company_name' => $companyName,
    			'invoice_address' => $invoiceAddress,
    			'company_number' => $companyNumber,
    			'tax_number' => $taxNumber,
    			'headquarters_address' => $headquartersAddress,
    			'business' => $business,
    			'private' => $private,
    		);
    		$this->update($data, 'id_client = ' . (int)$id);
    }
    	
    public function deleteClient($id){
    	$this->delete('id_client = ' . (int)$id);
    }
    
    /******
     * @returns bool existuje ICO
     */
    public function existsCompanyNumber($companyNumber){
    	$ico = $this->fetchAll($this->select()
    		->from('client')
    		->columns('company_number')
    		->where('company_number = ?', $companyNumber));
    	if(count($ico) != 0){
    		return true;
    	}
    	return false;
    }
    
	public function getCompanyNumber($clientId){
    	$ico = $this->fetchAll($this->select()
    		->from('client')
    		->columns('company_number')
    		->where('id_client = ?', $clientId));
    	return $ico->current()->company_number;
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
    
}

