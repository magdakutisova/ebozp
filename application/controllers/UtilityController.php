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
        		//nový klient: pokud sídlo (bool) = 1
        		if($values[8] == '1'){
        			//založení klienta
        			/* Zend_Debug::dump($values);
        			die(); */
        			$client = new Application_Model_Client();
        			$client->setCompanyName($values[1]);
        			$client->setHeadquartersStreet($values[3]);
        			$client->setHeadquartersTown(trim($values[4]));
        			$client->setHeadquartersCode($values[5]);
        			$client->setPrivate($values[12]);
        			$clientId = null;
        			try{
        				$clientId = $clients->addClient($client);
        				 
        			}
        			catch(Exception $e){
        				$this->_helper->FlashMessenger('Chyba: Záznam "' . $client->getCompanyName() . ', ' . $client->getHeadquartersStreet() . ', ' . $client->getHeadquartersTown() . '" nebyl uložen. ' . PHP_EOL . $e->getMessage() . PHP_EOL .  $e->getTraceAsString());
        			}
        			
        			//pokud klient nebyl založen, přejde se na dalšího
        			if($clientId == null){
        				continue;
        			}
        			
        			//založení hlavní pobočky
        			$subsidiary = new Application_Model_Subsidiary();
        			$subsidiary->setDistrict($values[0]);
        			if($values[2] != ''){
        				$subsidiary->setSubsidiaryName($values[2]);
        			}
        			else{
        				$subsidiary->setSubsidiaryName($values[1]);
        			}
        			$subsidiary->setSubsidiaryStreet($values[3]);
        			$subsidiary->setSubsidiaryTown(trim($values[4]));
        			$subsidiary->setSubsidiaryCode($values[5]);
        			$subsidiary->setDifficulty($values[6]);
        			if($values[7] != ''){
        				$subsidiary->setSupervisionFrequency($values[7]);
        			}
        			else{
        				$subsidiary->setSupervisionFrequency(0);
        			}
        			$subsidiary->setClientId($clientId);
        			$subsidiary->setPrivate($values[12]);
        			$subsidiary->setHq(true);
        			try{
        				$subsidiaries->addSubsidiary($subsidiary);
        			}
        			catch(Exception $e){
        				$this->_helper->FlashMessenger('Chyba: Záznam (podřazená pobočka) "' . $client->getCompanyName() . ', ' . $client->getHeadquartersStreet() . ', ' . $client->getHeadquartersTown() . '" nebyl uložen. ' . PHP_EOL . $e->getMessage() . PHP_EOL .  $e->getTraceAsString());
        				$clients->deleteClient($clientId, true);
        			}
        		}
        		else{
        			$clientId = $clients->getByNameAndAddress($values[1], $values[9], trim($values[10]));
        			if($clientId == -1){
        				$this->_helper->FlashMessenger('Klient "' . $values[1] . ', ' . $values[9] . ', ' . $values[10] . '" se v databázi vyskytuje více než jednou. Pobočku na adrese "' . $values[3] . ', ' . $values[4] . '" přidejte prosím ručně, nebo kontaktujte podporu.');
        				continue;
        			}
        			if($clientId == 0){
        				$this->_helper->FlashMessenger('Pro pobočku "' . $values[3] . ', ' . $values[4] . '" není v databázi zadána centrála s adresou "'  . $values[1] . ', ' . $values[9] . ', ' . $values[10] . '". Prosím zadejte nejprve centrálu. Pro prevenci této chyby seřaďte ve vstupním souboru záznamy tak, aby se nejprve vložily všechny centrály a poté všechny závislé pobočky (seřaďte soubor podle sídla (bool) sestupně).');
        				continue;
        			}
        			else{
        				$subsidiary = new Application_Model_Subsidiary();
        				$subsidiary->setDistrict($values[0]);
        				if($values[2] != ''){
        					$subsidiary->setSubsidiaryName($values[2]);
        				}
        				else{
        					$subsidiary->setSubsidiaryName($values[1]);
        				}
        				$subsidiary->setSubsidiaryStreet($values[3]);
        				$subsidiary->setSubsidiaryTown(trim($values[4]));
        				$subsidiary->setSubsidiaryCode($values[5]);
        				$subsidiary->setDifficulty($values[6]);
	        			if($values[7] != ''){
	        				$subsidiary->setSupervisionFrequency($values[7]);
	        			}
	        			else{
	        				$subsidiary->setSupervisionFrequency(0);
	        			}
        				$subsidiary->setClientId($clientId);
        				$subsidiary->setPrivate($values[12]);
        				$subsidiary->setHq(0);
        				try{
        					$subsidiaries->addSubsidiary($subsidiary);
        				}
        				catch(Exception $e){
        					$this->_helper->FlashMessenger('Chyba: Pobočka "' . $subsidiary->getSubsidiaryStreet() . ', ' . $subsidiary->getSubsidiaryStreet() . '" klienta ' . $values[1] . ' nemohla být uložena. ' . PHP_EOL . $e->getMessage() . PHP_EOL .  $e->getTraceAsString());
        					$clients->deleteClient($clientId, true);
        				}
        			}
        		}
        	}
        	$this->_helper->FlashMessenger('OK');
        	$this->_helper->redirector->gotoRoute(array(), 'clientimport');
        }
    }


}



