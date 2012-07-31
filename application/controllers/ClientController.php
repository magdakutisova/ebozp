<?php

class ClientController extends Zend_Controller_Action
{
	private $_acl;
	private $_username;
	private $_role;

    public function init()
    {
		$this->view->title = 'Správa klientů';
		$this->view->headTitle ( $this->view->title );
		
		$this->_helper->layout()->setLayout('clientLayout');
		
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ( true );
		}
		
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'list', 'html' )->initContext ();
		
		$this->_acl = new My_Controller_Helper_Acl();
		
		if(Zend_Auth::getInstance()->hasIdentity()){
			$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
			$this->_role = Zend_Auth::getInstance()->getIdentity()->role;
		}
    }

    public function indexAction()
    {
		$clients = new Application_Model_DbTable_Client();
		$clientId = $this->_getParam ( 'clientId' );
			
		$clients->openClient ( $clientId );
			
		$client = $clients->getHeadquarters($clientId);
		
		$this->view->subtitle = $client['company_name'];
		$this->view->client = $client;
		
		$diary = new Application_Model_DbTable_Diary();

		$this->view->records = $diary->getDiaryByClient($clientId);
		
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		if ($formContent != 0) {
			$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte pobočku:');
			$this->view->form = $form;
			
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if ($form->isValid ( $formData )) {
					$subsidiary = $this->getRequest ()->getParam ( 'select' );
					$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary ), 'subsidiaryIndex' );
				}
			}
		}
    	else{
			$form = "<p>Klient nemá žádné pobočky.</p>";
			$this->view->form = $form;
		}
		
		//TODO filtrovat záznamy v deníku dle uživatele
    }

    public function listAction()
    {
		$this->view->subtitle = 'Výběr klienta';
		
		$this->_helper->layout()->setLayout('layout');
		
		$mode = $this->_getParam ( 'mode' );
			
		switch($mode){
			case "bt":
				$this->renderScript ( 'client/technician.phtml' );
				break;
			case "koo":
				$this->renderScript ( 'client/coordinator.phtml' );
				break;
			case "obec":
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$this->view->subsidiaries = $subsidiaries->getByTown ();
				$this->renderScript ( 'client/town.phtml' );
				break;
			case "naposledy":
				$clients = new Application_Model_DbTable_Client ();
				$this->view->clients = $clients->getLastOpen ();
				$this->renderScript ( 'client/list.phtml' );
				break;
			default:
				$clients = new Application_Model_DbTable_Client ();
				$this->view->clients = $clients->getClients ();
				$this->renderScript ( 'client/list.phtml' );
		}
    }

    public function newAction()
    {
		$this->view->subtitle = 'Nový klient';
		
		$this->_helper->layout()->setLayout('layout');
		
		$form = new Application_Form_Client ();
		$form->save->setLabel ( 'Přidat' );
		$this->view->form = $form;
		
		// naplnění formuláře daty ze session, pokud existují
		$defaultNamespace = new Zend_Session_Namespace ();
		if (isset ( $defaultNamespace->formData )) {
			$form->populate ( $defaultNamespace->formData );
			unset ( $defaultNamespace->formData );
		}
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$companyName = $form->getValue ( 'company_name' );
				//checkbox zda je adresa fakturační stejná jako adresa sídla
				$invoiceAddress = $form->getValue('invoice_address');
				$invoiceStreet = $form->getValue ( 'invoice_street' );
				$invoiceCode = $form->getValue ( 'invoice_code' );
				$invoiceTown = $form->getValue ( 'invoice_town' );
				$companyNumber = $form->getValue ( 'company_number' );
				$taxNumber = $form->getValue ( 'tax_number' );
				$headquartersStreet = $form->getValue ( 'headquarters_street' );
				$headquartersCode = $form->getValue ( 'headquarters_code' );
				$headquartersTown = $form->getValue ( 'headquarters_town' );
				$business = $form->getValue ( 'business' );
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$insuranceCompany = $insuranceCompanyOptions[$form->getValue('insurance_company')];
				$supervisionFrequency = $form->getValue('supervision_frequency');
				$doctor = $form->getValue('doctor');
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$private = $form->getValue ( 'private' );
				
				if($invoiceAddress){
					$invoiceStreet = $headquartersStreet;
					$invoiceTown = $headquartersTown;
					$invoiceCode = $headquartersCode;
				}
				
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $companyNumber )) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientNew' );
				}
				
				//přidání klienta
				$clientId = $clients->addClient ( $companyName, $companyNumber, $taxNumber,
					$headquartersStreet, $headquartersCode, $headquartersTown, $invoiceStreet,
					$invoiceCode, $invoiceTown, $business, $insuranceCompany, $private );
				
				//přidání pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaryId = $subsidiaries->addSubsidiary ( $companyName, $headquartersStreet,
					$headquartersCode, $headquartersTown, $contactPerson, $phone, $email,
					$supervisionFrequency, $doctor, $clientId, $private, true );

				$this->_helper->diaryRecord($this->_username, 'přidal nového klienta', array('clientId' => $clientId), 'clientIndex', $companyName, $subsidiaryId);
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> přidán' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
			} else {
				$form->populate ( $formData );
			}
		}
	
    }

    public function adminAction()
    {
		$this->view->subtitle = 'Administrace klienta';
		
		$clientId = $this->_getParam ( 'clientId' );
		
		$clients = new Application_Model_DbTable_Client ();
		
		$clients->openClient ( $clientId );
		$client = $clients->getClient ( $clientId );
		
		$this->view->companyName = $client ['company_name'];
		$this->view->clientId = $clientId;
		
		$this->view->canDeleteClient = $this->_acl->isAllowed($this->_role, 'client', 'delete');
		
		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		if ($formContent != 0) {
			$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte pobočku:');
			$this->view->form = $form;
			
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if ($form->isValid ( $formData )) {
					$subsidiary = $this->getRequest ()->getParam ( 'select' );
					if (isSet ( $formData ['edit'] )) {
						$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary ), 'subsidiaryEdit' );
					}
					if (isSet ( $formData ['delete'] )) {
						//jen forward kvůli metodě POST
						$this->_forward ( 'delete', 'subsidiary' );
					}
				}
			}
		}
		else{
			$form = "<p>Klient nemá žádné pobočky.</p>";
			$this->view->form = $form;
		}
		
		$defaultNamespace = new Zend_Session_Namespace();
		$defaultNamespace->referer = $this->_request->getPathInfo();
	
    }

    public function editAction()
    {
		$this->view->subtitle = 'Editace základních údajů klienta';
					
		$form = new Application_Form_Client ();
		$form->save->setLabel ( 'Uložit' );
		$this->view->form = $form;
		
		$defaultNamespace = new Zend_Session_Namespace ();
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$clientId = $form->getValue ( 'id_client' );
				$subsidiaryId = $form->getValue ( 'id_subsidiary' );
				$companyName = $form->getValue ( 'company_name' );
				$invoiceAddress = $form->getValue('invoice_address');
				$invoiceStreet = $form->getValue ( 'invoice_street' );
				$invoiceCode = $form->getValue ( 'invoice_code' );
				$invoiceTown = $form->getValue ( 'invoice_town' );
				$companyNumber = $form->getValue ( 'company_number' );
				$taxNumber = $form->getValue ( 'tax_number' );
				$headquartersStreet = $form->getValue ( 'headquarters_street' );
				$headquartersCode = $form->getValue ( 'headquarters_code' );
				$headquartersTown = $form->getValue ( 'headquarters_town' );
				$business = $form->getValue ( 'business' );
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$insuranceCompany = $insuranceCompanyOptions[$form->getValue('insurance_company')];
				$supervisionFrequency = $form->getValue('supervision_frequency');
				$doctor = $form->getValue('doctor');
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$private = $form->getValue ( 'private' );
				
				if($invoiceAddress){
					$invoiceStreet = $headquartersStreet;
					$invoiceTown = $headquartersTown;
					$invoiceCode = $headquartersCode;
				}
				
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $companyNumber ) && ($clients->getCompanyNumber ( $clientId ) != $companyNumber)) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientEdit' );
				}
				
				//update klienta
				$clients->updateClient ( $clientId, $companyName, $companyNumber, $taxNumber,
					$headquartersStreet, $headquartersCode, $headquartersTown, $invoiceStreet,
					$invoiceCode, $invoiceTown, $business, $insuranceCompany, $private );
				
				//update pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaries->updateSubsidiary ( $subsidiaryId, $companyName, $headquartersStreet,
					$headquartersCode, $headquartersTown, $contactPerson, $phone, $email,
					$supervisionFrequency, $doctor, $clientId, $private, true );
				
				$this->_helper->diaryRecord($this->_username, 'upravil klienta', array('clientId' => $clientId), 'clientIndex', $companyName, $subsidiaryId);
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> upraven' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
			}
		
		} else {
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			} else {
				$clientId = $this->_getParam('clientId');
				$clients = new Application_Model_DbTable_Client();
				
				$form->populate ( $clients->getHeadquarters ( $clientId ) );
			}
		}
    }

    public function deleteAction()
    {
		if ($this->getRequest ()->getMethod () == 'POST') {
			$clientId = $this->_getParam ( 'clientId' );
			
			$clients = new Application_Model_DbTable_Client ();
			$client = $clients->getHeadquarters ( $clientId );
			$companyName = $client ['company_name'];
			$subsidiaryId = $client ['id_subsidiary'];
			$clients->deleteClient ( $clientId );
			
			$this->_helper->diaryRecord($this->_username, 'smazal klienta', null, null, $companyName, $subsidiaryId);
			
			$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> smazán' );
			$this->_helper->redirector->gotoRoute ( array (), 'clientList' );
		
		} else {
			throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání klienta.', 500 );
		}
	
    }

}






