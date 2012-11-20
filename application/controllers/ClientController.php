<?php

class ClientController extends Zend_Controller_Action
{
	private $_acl;
	private $_username;
	private $_role;
	private $_user;
	private $_canViewHeadquarters = false;

    public function init()
    {
    	//globální nastavení view
		$this->view->title = 'Správa klientů';
		$this->view->headTitle ( $this->view->title );
		$action = $this->getRequest()->getActionName();
		if($action == 'list' || $action == 'new'){
			$this->_helper->layout()->setLayout('layout');
		}
		else{
			$this->_helper->layout()->setLayout('clientLayout');
		}
		
		//nastavení pro ajax
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ( true );
		}		
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'list', 'html' )->initContext ();
		
		//nastavení přístupových práv
		$this->_acl = new My_Controller_Helper_Acl();		
		if(Zend_Auth::getInstance()->hasIdentity()){
			$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
			$this->_role = Zend_Auth::getInstance()->getIdentity()->role;
		}
		
		$users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		if ($action == 'index' || $action == 'edit' || $action == 'admin'){	
			//do index, edit action může jen když má přístup k centrále
	    	if($this->_acl->isAllowed($this->_user, $subsidiaries->getHeadquarters($this->_getParam('clientId')))){
					$this->_canViewHeadquarters = true;
			}	
			if ($action == 'index' || $action == 'edit'){
				if(!$this->_canViewHeadquarters){
					$this->_helper->redirector('denied', 'error');
				}
			}
			
			//do admin action může jen když má přístup k některé z poboček
			if ($action == 'admin'){
				if(!$this->_canViewHeadquarters && !$this->hasAnySubsidiary()){
					$this->_helper->redirector('denied', 'error');
				}
			}
		}
		//zobrazení soukromých poznámek
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
		
		//zobrazení věcí jen pro majitele centrály
		$this->view->canViewHeadquarters = $this->_canViewHeadquarters;
		
		//do list action může vždy - neošetřuje se
		//new, delete action je ošetřena v Acl helperu
		
    }
    
    private function hasAnySubsidiary(){
    	$clients = new Application_Model_DbTable_Client();
    	$subsidiaries = $clients->getSubsidiaries($this->getRequest()->getParam('clientId'));
    	$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
    	if ($subsidiaries){
    		foreach($subsidiaries as $subsidiaryId){
    			if($this->_acl->isAllowed($this->_user, $subsidiariesDb->getSubsidiary($subsidiaryId))){
    				return true;
    			}
    		}
    	}
    	return false;
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
		
		$userSubs = new Application_Model_DbTable_UserHasSubsidiary(); 
		$this->view->technicians = $userSubs->getByRoleAndSubsidiary(My_Role::ROLE_TECHNICIAN, $subsidiary->getIdSubsidiary());
		
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
    	
    	$this->view->subsidiaryId = array_shift(array_keys($formContent));
    	
		if ($formContent != 0) {
			$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte pobočku:');
			$form->submit->setLabel('Vybrat');
			$this->view->form = $form;
			
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if (in_array('Vybrat', $formData) && $form->isValid ( $formData )) {
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
				//checkbox zda je adresa fakturační stejná jako adresa sídla
				$invoiceAddress = $form->getValue('invoice_address');
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				
				if($invoiceAddress){
					$client->setInvoiceStreet($client->getHeadquartersStreet());
					$client->setInvoiceCode($client->getHeadquartersCode());
					$client->setInvoiceTown($client->getHeadquartersTown());
				}
				$client->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
							
				$clients = new Application_Model_DbTable_Client ();
				
				//přidání klienta a kontrola IČO
				$clientId = $clients->addClient ( $client);
				if (!$clientId) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientNew' );
				}
					
				$subsidiary->setSubsidiaryName($client->getCompanyName());
				$subsidiary->setSubsidiaryStreet($client->getHeadquartersStreet());
				$subsidiary->setSubsidiaryCode($client->getHeadquartersCode());
				$subsidiary->setSubsidiaryTown($client->getHeadquartersTown());
				$subsidiary->setClientId($clientId);
				$subsidiary->setHq(true);
				
				//přidání pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaryId = $subsidiaries->addSubsidiary ( $subsidiary);
				
				if($this->_user->getRole() == My_Role::ROLE_COORDINATOR){
					$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
					$userSubs->addRelation($this->_user->getIdUser(), $subsidiaryId);
				}

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
		$this->view->canViewHeadquarters = $this->_canViewHeadquarters;
		
		//výběr poboček
		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
		
			$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte pobočku:');
			$this->view->form = $form;
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if(isset($formData['editSubsidiary']) || isset($formData['deleteSubsidiary'])){
					if ($form->isValid ( $formData )) {
						$subsidiary = $this->getRequest ()->getParam ( 'select' );
						if (isSet ( $formData ['editSubsidiary'] )) {
							$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary ), 'subsidiaryEdit' );
						}
						if (isSet ( $formData ['deleteSubsidiary'] )) {
							//jen forward kvůli metodě POST
							$this->_forward ( 'delete', 'subsidiary' );
						}
					}
				}
			}
		}
		else{
			$form = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
			$this->view->form = $form;
		}
		
		//výběr pracovišť - se momentálně nezobrazuje
		$workplaces = new Application_Model_DbTable_Workplace();
		$workplaceSelect = $workplaces->getWorkplaces($clientId);
		if ($workplaceSelect != 0){
			foreach($workplaceSelect as $key => $workplace){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($workplace[1]))){
					unset($workplaceSelect[$key]);
				}
				else{
					$workplace = $workplace[0];
					$workplaceSelect[$key] = $workplace;
				}
			}
			$formWorkplace = new Application_Form_Select();
			$formWorkplace->select->setMultiOptions($workplaceSelect);
			$formWorkplace->select->setLabel('Vyberte pracoviště:');
			$formWorkplace->submit->setName('submitWorkplace');
			$this->view->formWorkplace = $formWorkplace;
			if($this->getRequest()->isPost()){
				$formData = $this->getRequest()->getPost();
				if(isset($formData['editWorkplace']) || isset($formData['deleteWorkplace'])){
					if($formWorkplace->isValid($formData)){
						$workplaceId = $this->getRequest()->getParam('select');
						if(isset($formData['editWorkplace'])){
							$this->_helper->redirector->gotoRoute(array('clientId' => $clientId, 'workplaceId' => $workplaceId), 'workplaceEdit');
						}
						if(isset($formData['deleteWorkplace'])){
							//jen forward kvůli metodě POST
							$this->_forward('delete', 'workplace');
						}
					}
				}
			}
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
				
				//update klienta a kontrola IČO
				if (!$clients->updateClient ( $client)) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientEdit' );
				}
				
				//update klienta
				//$clients->updateClient ( $client);
				
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






