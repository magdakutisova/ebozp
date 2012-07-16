<?php

class Application_Model_DbTable_Subsidiary extends Zend_Db_Table_Abstract
{

    protected $_name = 'subsidiary';
    
    protected $_referenceMap = array(
    	'Client' => array(
    		'columns' => 'client_id',
    		'refTableClass' => 'Application_Model_DbTable_Client',
    		'refColumns' => 'id_client',
    		),
    	);

    public function getSubsidiary($id){
    	$id = (int)$id;
    	$row = $this->fetchRow('id_subsidiary = ' . $id);
    	if (!$row){
    		throw new Exception("Pobočka $id nebyla nalezena.");
    	}
    	return $row->toArray();
    }
    
    public function addSubsidiary($subsidiaryName, $subsidiaryAddress, $invoiceAddress, 
    	$contactPerson, $phone, $email, $supervisionFrequency, $clientId, $private, $hq){
    		$data = array(
    			'subsidiary_name' => $subsidiaryName,	
    			'subsidiary_address' => $subsidiaryAddress,
    			'invoice_address' => $invoiceAddress,
    			'contact_person' => $contactPerson,
    			'phone' => $phone,
    			'email' => $email,
    			'supervision_frequency' => $supervisionFrequency,
    			'client_id' => $clientId,
    			'private' => $private,
    			'hq' => $hq,
    		);
    		$this->insert($data);
    	}
    	
    public function updateSubsidiary($id, $subsidiaryName, $subsidiaryAddress,
    	$invoiceAddress, $contactPerson, $phone, $email, $supervisionFrequency,
    	$clientId, $private, $hq){
    		$data = array(
    			'subsidiary_name' => $subsidiaryName,
    			'subsidiary_address' => $subsidiaryAddress,
    			'invoice_address' => $invoiceAddress,
    			'contact_person' => $contactPerson,
    			'phone' => $phone,
    			'email' => $email,
    			'supervision_frequency' => $supervisionFrequency,
    			'client_id' => $clientId,
    			'private' => $private,
    			'hq' => $hq,
    		);
    		$this->update($data, 'id_subsidiary = ' . (int)$id);
    	}
    	
    public function deleteSubsidiary($id){
    	$this->delete('id_subsidiary = ' . (int)$id);
    }
    
    public function getSubsidiaries($clientId){
    	$select = $this->select()
    		->from('subsidiary')
    		->columns(array('id_subsidiary', 'subsidiary_name', 'subsidiary_address'))
    		->where('client_id = ?', $clientId)
    		->where('hq = 0');
		$results = $this->fetchAll($select);
		$subsidiares = array();
		foreach ($results as $result) :
			$key = $result->id_subsidiary;
			$subsidiary = $result->subsidiary_name . ' - ' . $result->subsidiary_address;
			$subsidiaries[$key] = $subsidiary;
		endforeach;
		return $subsidiaries;
    }

}

