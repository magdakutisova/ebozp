<?php

class ClientController extends Zend_Controller_Action
{
	private $_acl;
	private $_username;
	private $_role;
	private $_user;

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
		
		//do index, admin, edit action může jen když má přístup k pobočce/centrále
		$action = $this->getRequest()->getActionName();
		$users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		
		if ($action == 'index' || $action == 'admin' || $action == 'edit'){
			if(!$this->_acl->isAllowed($this->_user, $subsidiaries->getHeadquarters($this->_getParam('clientId')))){
				$this->_helper->redirector('denied', 'error');
			}
		}
		
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
		
		//do list action může vždy - neošetřuje se
		//new, delete action je ošetřena jinde
		
    }

    public function indexAction()
    {
		$clients = new Application_Model_DbTable_Client();
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		$clientId = $this->_getParam ( 'clientId' );
			
		$clients->openClient ( $clientId );
			
		$client = $clients->getClient($clientId);
		$subsidiary = $subsidiaries->getHeadquarters($clientId);
		
		$this->view->subtitle = $client->getCompanyName();
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		
		//bezpečnostní deník
		$diary = new Application_Model_DbTable_Diary();
		$messages = $diary->getDiaryByClient($clientId);
		$this->_helper->diary($messages);
		$this->_helper->diaryMessages();
		
		//výběr poboček
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
    	if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
    	}
		
		if ($formContent != 0) {
			$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte pobočku:');
			$this->view->form = $form;
			
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if (in_array('Zobrazit', $formData) && $form->isValid ( $formData )) {
					$subsidiary = $this->getRequest ()->getParam ( 'select' );
					$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary ), 'subsidiaryIndex' );
				}
			}
		}
    	else{
			$form = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
			$this->view->form = $form;
		}
		
		
    }

    public function listAction()
    {
		$this->view->subtitle = 'Výběr klienta';
		
		$this->_helper->layout()->setLayout('layout');
		
		$mode = $this->_getParam ( 'mode' );
			
		switch($mode){
			case "bt":
			case "koo":
				$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
				
				if ($mode == "bt"){
					$subsidiaries = $userSubs->getByRole(My_Role::ROLE_TECHNICIAN);
				}
				else{
					$subsidiaries = $userSubs->getByRole(My_Role::ROLE_COORDINATOR);
				}
				
				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary['subsidiary']->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary['subsidiary']));
				}
				
				$this->view->subsidiaries = $subsidiaries;
				
				$this->renderScript ( 'client/assigned.phtml' );
				break;
			case "obec":
				$subsidiariesDb = new Application_Model_DbTable_Subsidiary ();

				$subsidiaries = $subsidiariesDb->getByTown ();
				
				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary));
				}
				
				$this->view->subsidiaries = $subsidiaries;
				$this->renderScript ( 'client/town.phtml' );
				break;
			case "naposledy":
				$subsidiariesDb = new Application_Model_DbTable_Subsidiary ();

				$subsidiaries = $subsidiariesDb->getLastOpen ();
				
				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary));
				}
				
				$this->view->subsidiaries = $subsidiaries;
				$this->renderScript ( 'client/list.phtml' );
				break;
			default:				
				$subsidiariesDb = new Application_Model_DbTable_Subsidiary ();

				$subsidiaries = $subsidiariesDb->getByClient ();
				
				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary));
				}
				
				$this->view->subsidiaries = $subsidiaries;
				$this->renderScript ( 'client/list.phtml' );
				break;
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
				$client = new Application_Model_Client($formData);
				$subsidiary = new Application_Model_Subsidiary($formData);
//				$companyName = $form->getValue ( 'company_name' );
				//checkbox zda je adresa fakturační stejná jako adresa sídla
				$invoiceAddress = $form->getValue('invoice_address');
//				$invoiceStreet = $form->getValue ( 'invoice_street' );
//				$invoiceCode = $form->getValue ( 'invoice_code' );
//				$invoiceTown = $form->getValue ( 'invoice_town' );
//				$companyNumber = $form->getValue ( 'company_number' );
//				$taxNumber = $form->getValue ( 'tax_number' );
//				$headquartersStreet = $form->getValue ( 'headquarters_street' );
//				$headquartersCode = $form->getValue ( 'headquarters_code' );
//				$headquartersTown = $form->getValue ( 'headquarters_town' );
//				$business = $form->getValue ( 'business' );
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
//				$insuranceCompany = $insuranceCompanyOptions[$form->getValue('insurance_company')];
//				$supervisionFrequency = $form->getValue('supervision_frequency');
//				$doctor = $form->getValue('doctor');
//				$contactPerson = $form->getValue ( 'contact_person' );
//				$phone = $form->getValue ( 'phone' );
//				$email = $form->getValue ( 'email' );
//				$private = $form->getValue ( 'private' );
				
				if($invoiceAddress){
					$client->setInvoiceStreet($client->getHeadquartersStreet());
					$client->setInvoiceCode($client->getHeadquartersCode());
					$client->setInvoiceTown($client->getHeadquartersTown());
				}
				$client->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
				
				//$client->setDeleted(0);
							
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $client->getCompanyNumber() )) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientNew' );
				}
				
				//přidání klienta
				$clientId = $clients->addClient ( $client);
					
				$subsidiary->setSubsidiaryName($client->getCompanyName());
				$subsidiary->setSubsidiaryStreet($client->getHeadquartersStreet());
				$subsidiary->setSubsidiaryCode($client->getHeadquartersCode());
				$subsidiary->setSubsidiaryTown($client->getHeadquartersTown());
				$subsidiary->setClientId($clientId);
				$subsidiary->setHq(true);
				//$subsidiary->setDeleted(0);
				
				//přidání pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaryId = $subsidiaries->addSubsidiary ( $subsidiary);

				$this->_helper->diaryRecord($this->_username, 'přidal nového klienta', array('clientId' => $clientId), 'clientIndex', $client->getCompanyName(), $subsidiaryId);
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $client->getCompanyName() . '</strong> přidán' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientIndex' );
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
		
		$this->view->companyName = $client->getCompanyName();
		$this->view->clientId = $clientId;
		
		$this->view->canDeleteClient = $this->_acl->isAllowed($this->_role, 'client', 'delete');
		$this->view->canAddSubsidiary = $this->_acl->isAllowed($this->_role, 'subsidiary', 'new');
		$this->view->canDeleteSubsidiary = $this->_acl->isAllowed($this->_role, 'subsidiary', 'delete');
		
		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
		}

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
			$form = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
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
				$client = new Application_Model_Client($formData);
				$subsidiary = new Application_Model_Subsidiary($formData);
				$invoiceAddress = $form->getValue('invoice_address');
								
				if($invoiceAddress){
					$client->setInvoiceStreet($client->getHeadquartersStreet());
					$client->setInvoiceTown($client->getInvoiceTown());
					$client->setInvoiceCode($client->getInvoiceCode());
				}
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$client->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
				
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $client->getCompanyNumber()) && ($clients->getCompanyNumber ( $client->getIdClient() ) != $client->getCompanyNumber())) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientEdit' );
				}
				
				//update klienta
				$clients->updateClient ( $client, true);
				
				$subsidiary->setSubsidiaryName($client->getCompanyName());
				$subsidiary->setSubsidiaryStreet($client->getHeadquartersStreet());
				$subsidiary->setSubsidiaryCode($client->getHeadquartersCode());
				$subsidiary->setSubsidiaryTown($client->getHeadquartersTown());
				$subsidiary->setClientId($client->getIdClient());
				$subsidiary->setHq(true);
				
				//update pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaries->updateSubsidiary ( $subsidiary, true);
				
				$this->_helper->diaryRecord($this->_username, 'upravil klienta', array('clientId' => $client->getIdClient()), 'clientIndex', $client->getCompanyName(), $subsidiary->getIdSubsidiary());
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $client->getCompanyName() . '</strong> upraven' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $client->getIdClient() ), 'clientAdmin' );
			}
		
		} else {
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			} else {
				$clientId = $this->_getParam('clientId');
				$clients = new Application_Model_DbTable_Client();
				$subsidiaries = new Application_Model_DbTable_Subsidiary();
				$client = $clients->getClient($clientId);
				$subsidiary = $subsidiaries->getHeadquarters($clientId);
				
				$data = $client->toArray();
				$data['id_subsidiary'] = $subsidiary->getIdSubsidiary();
				$data['supervision_frequency'] = $subsidiary->getSupervisionFrequency();
				$data['doctor'] = $subsidiary->getDoctor();
				$data['contact_person'] = $subsidiary->getContactPerson();
				$data['phone'] = $subsidiary->getPhone();
				$data['email'] = $subsidiary->getEmail();
				
				$form->populate ( $data );
			}
		}
    }

    public function deleteAction()
    {
		if ($this->getRequest ()->getMethod () == 'POST') {
			$clientId = $this->_getParam ( 'clientId' );
			
			$clients = new Application_Model_DbTable_Client ();
			$client = $clients->getClient ( $clientId );
			$subsidiaries = new Application_Model_DbTable_Subsidiary();
			$subsidiary = $subsidiaries->getHeadquarters($clientId);
			$companyName = $client->getCompanyName();
			$subsidiaryId = $subsidiary->getIdSubsidiary();
			$clients->deleteClient ( $clientId );
			//$subsidiaries->deleteSubsidiary($subsidiaryId);
			
			$this->_helper->diaryRecord($this->_username, 'smazal klienta', null, null, $companyName, $subsidiaryId);
			
			$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> smazán' );
			$this->_helper->redirector->gotoRoute ( array (), 'clientList' );
		
		} else {
			throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání klienta.', 500 );
		}
	
    }

}






