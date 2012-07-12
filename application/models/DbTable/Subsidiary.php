<?php

class Application_Model_DbTable_Subsidiary extends Zend_Db_Table_Abstract
{

    protected $_name = 'subsidiary';
    
    protected $_referenceMap = array(
    	'Client' => array(
    		'columns' => 'client_id',
    		'refTableClass' => 'Application_Model_DbTable_Client',
    		'refColumns' => 'id',
    		),
    	);

    public function getSubsidiary($id){
    	$id = (int)$id;
    	$row = $this->fetchRow('id = ' . $id);
    	if (!$row){
    		throw new Exception("Pobočka $id nebyla nalezena.");
    	}
    	return $row->toArray();
    }
    
    public function addSubsidiary($subsidiaryName, $subsidiaryAddress, $contactPerson,
    	$phone, $email, $supervisionFrequency, $clientId, $private, $hq){
    		$data = array(
    			'subsidiary_name' => $subsidiaryName,	
    			'subsidiary_address' => $subsidiaryAddress,
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
    	$contactPerson, $phone, $email, $supervisionFrequency, $clientId, $private, $hq){
    		$data = array(
    			'subsidiary_name' => $subsidiaryName,
    			'subsidiary_address' => $subsidiaryAddress,
    			'contact_person' => $contactPerson,
    			'phone' => $phone,
    			'email' => $email,
    			'supervision_frequency' => $supervisionFrequency,
    			'client_id' => $clientId,
    			'private' => $private,
    			'hq' => $hq,
    		);
    		$this->update($data, 'id = ' . (int)$id);
    	}
    	
    public function deleteSubsidiary($id){
    	$this->delete('id = ' . (int)$id);
    }

}

