<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Správa klientů';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {
        // action body
    }

    public function searchAction()
    {
        // action body
    }

    public function listAction()
    {
    	$this->view->subtitle = 'Výběr klienta';
        $clients = new Application_Model_DbTable_Client();
        $this->view->clients = $clients->fetchAll();
    }

    public function newAction()
    {
    	$this->view->subtitle = 'Nový klient';
    	
        $form = new Application_Form_Client();
        $form->save->setLabel('Přidat');
        $this->view->form = $form;
        
        // naplnění formuláře daty ze session, pokud existují
        $defaultNamespace = new Zend_Session_Namespace();
        if(isset($defaultNamespace->formData)){
        	$form->populate($defaultNamespace->formData);
        	unset($defaultNamespace->formData);
        }

        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		$companyName = $form->getValue('company_name');
        		$invoiceAddress = $form->getValue('invoice_address');
        		$companyNumber = $form->getValue('company_number');
        		$taxNumber = $form->getValue('tax_number');
        		$headquartersAddress = $form->getValue('headquarters_address');
        		$business = $form->getValue('business');
        		$contactPerson = $form->getValue('contact_person');
        		$phone = $form->getValue('phone');
        		$email = $form->getValue('email');
        		$private = $form->getValue('private');
        		
        		$clients = new Application_Model_DbTable_Client();
        		
        		//kontrola IČO
        		if($clients->existsCompanyNumber($companyNumber)){
        			$defaultNamespace->formData = $formData;
        			$this->_helper->FlashMessenger('Chyba! Klient s tímto IČO již existuje.');
        			$this->_helper->redirector->gotoRoute(array(), 'clientNew');
        		}
        		
        		//přidání klienta
        		$clientId = $clients->addClient($companyName, $companyNumber,
        			$taxNumber, $headquartersAddress, $business, $private);
        		
        		//přidání pobočky
        		$subsidiaries = new Application_Model_DbTable_Subsidiary();
        		$subsidiaries->addSubsidiary($companyName, $headquartersAddress, $invoiceAddress,
        			$contactPerson, $phone, $email, null, $clientId, $private, true);
        		        		
        		//TODO zápis do bezpečnostního deníku
        		$this->_helper->FlashMessenger('Klient <strong>' . $companyName . '</strong> přidán');
        		$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'clientAdmin');
        	}
        	else {
        		$form->populate($formData);
        	}
        }
        
    }

    public function adminAction()
    {
    	$this->view->subtitle = 'Administrace klienta';
    	
		$clientId = $this->_getParam('clientId');
		
		$clients = new Application_Model_DbTable_Client();
		$client = $clients->getClient($clientId);
		
		$this->view->companyName = $client['company_name'];
		$this->view->clientId = $clientId;
		
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		$form = new Application_Form_SubsidiaryList ();
		$form->subsidiary->setMultiOptions ( $formContent );
		$this->view->form = $form;
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$subsidiary = $this->getRequest()->getParam('subsidiary');
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary), 'subsidiaryEdit' );
			}
		}
    }

    public function editAction()
    {
    	$this->view->subtitle = 'Editace základních údajů klienta';
    	
    	$form = new Application_Form_Client();
        $form->save->setLabel('Uložit');
        $this->view->form = $form;

        $defaultNamespace = new Zend_Session_Namespace();

        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		$clientId = $form->getValue('id_client');
        		$subsidiaryId = $form->getValue('id_subsidiary');
        		$companyName = $form->getValue('company_name');
        		$invoiceAddress = $form->getValue('invoice_address');
        		$companyNumber = $form->getValue('company_number');
        		$taxNumber = $form->getValue('tax_number');
        		$headquartersAddress = $form->getValue('headquarters_address');
        		$business = $form->getValue('business');
        		$contactPerson = $form->getValue('contact_person');
        		$phone = $form->getValue('phone');
        		$email = $form->getValue('email');
        		$private = $form->getValue('private');
        		
        		$clients = new Application_Model_DbTable_Client();
        		
        		//kontrola IČO
        		if($clients->existsCompanyNumber($companyNumber) && 
        			($clients->getCompanyNumber($clientId) != $companyNumber)){
        				$defaultNamespace->formData = $formData;
        				$this->_helper->FlashMessenger('Chyba! Klient s tímto IČO již existuje.');
        				$this->_helper->redirector->gotoRoute(array(), 'clientEdit');
        		}
        		
        		//update klienta
        		$clients->updateClient($clientId, $companyName, $companyNumber,
        			$taxNumber, $headquartersAddress, $business, $private);
        		
        		//update pobočky
        		$subsidiaries = new Application_Model_DbTable_Subsidiary();
        		$subsidiaries->updateSubsidiary($subsidiaryId, $companyName,
        			$headquartersAddress, $invoiceAddress, $contactPerson, $phone,
        			$email, null, $clientId, $private, true);
        		        		
        		//TODO zápis do bezpečnostního deníku
        		$this->_helper->FlashMessenger('Klient <strong>' . $companyName . '</strong> upraven');
        		$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'clientAdmin');
        	}

        }
        else {
        	if(isset($defaultNamespace->formData)){
        		$form->populate($defaultNamespace->formData);
        		unset($defaultNamespace->formData);
        	}
        	else{
        		$clientId = $this->_getParam('clientId');
        		$clients = new Application_Model_DbTable_Client();
        		$form->populate($clients->getHeadquarters($clientId));
        	}
        }
    }


}
