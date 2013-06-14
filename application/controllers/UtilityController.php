<?php

class UtilityController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function clientimportAction()
    {
    	$this->view->subtitle = 'Import klientů';
        $form = new Application_Form_ClientImport();
        $this->view->form = $form;
        
        if($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())){
        	$data = $this->getRequest()->getParam('textarea');
        	$rows = explode(PHP_EOL, $data);
        	
        	$clients = new Application_Model_DbTable_Client();
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	
        	foreach($rows as $row){
        		$values = explode(';', $row);
        		//nový klient
        		if($values[8] == '1'){
        			Zend_Debug::dump($values);
        			$client = new Application_Model_Client();
        			$client->setCompanyName($values[1]);
        			$client->setHeadquartersStreet($values[3]);
        			$client->setHeadquartersTown(trim($values[4]));
        			$client->setHeadquartersCode($values[5]);
        			$client->setPrivate($values[13]);
        			$clientId = $clients->addClient($client);        			
        			
        			$subsidiary = new Application_Model_Subsidiary();
        			$subsidiary->setDistrict($values[0]);
        			if($values[2] != ''){
        				$subsidiary->setSubsidiaryName($values[2]);
        			}
        			else{
        				$subsidiary->setSubsidiaryName($values[1]);
        			}
        			//od adresy dále dokončit
        		}
        		//přidání pobočky k existujícímu klientovi
        	}
        	die();
        }
    }


}



