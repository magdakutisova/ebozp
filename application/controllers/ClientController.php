<?php

class ClientController extends Zend_Controller_Action
{
	private $_acl;
	private $_username;
	private $_role;
	private $_user;
	private $_canViewHeadquarters = false;
	private $_responsibilityList = array();
	private $_employeeList = array();

	public function init()
	{
		//globální nastavení view
		$this->view->title = 'Správa klientů';
		$this->view->headTitle ( $this->view->title );
		$action = $this->getRequest()->getActionName();
		if($action == 'list' || $action == 'new' || $action == 'populateresponsibility'){
			$this->_helper->layout()->setLayout('layout');
		}
		else{
			$this->_helper->layout()->setLayout('clientLayout');
		}
		$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');

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

		//získání seznamu odpovědností
		$this->_responsibilityList = My_Responsibility::getResponsibilities();
		$clientId = $this->getRequest()->getParam('clientId', null);
		$responsibilities = new Application_Model_DbTable_Responsibility();
		$extraResponsibilities = $responsibilities->getExtraResponsibilities($clientId);
		$this->_responsibilityList = $this->_responsibilityList + $extraResponsibilities;

		//získání seznamu zaměstnanců
		$this->_employeeList[0] = '-----';
		$employees = new Application_Model_DbTable_Employee();
		$this->_employeeList = $this->_employeeList + $employees->getResponsibleEmployees($clientId);

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
		$headquarters = $subsidiaries->getHeadquartersWithDetails($clientId);
		$subsidiary = $headquarters['subsidiary'];

		$this->view->subtitle = $client->getCompanyName();
		$this->view->client = $client;
		$this->view->subsidiary = $headquarters;

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
			foreach ($formContent as $key => $subs){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
		}
		 
		$this->view->subsidiaryId = $subsidiary->getIdSubsidiary();

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
			$form = "<p>Klient má jen jednu pobočku nebo k ostatním pobočkám nemáte přístup.</p>";
			$this->view->form = $form;
		}

		/*
		 * PETR JINDRA 30. 11. 2012
		*/

		// kontrola ACL pro vytvoreni auditu
		$this->view->createAuditAllowed = $this->_acl->isAllowed($this->_user->getRoleId(), "audit:audit", "create");

	}

	public function listAction()
	{
		$this->view->subtitle = 'Výběr klienta';

		//nastavení pro ajax
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ( true );
		}
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'list', 'html' )->initContext ();

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
			case "okres":
				$subsidiariesDb = new Application_Model_DbTable_Subsidiary();

				$subsidiaries = $subsidiariesDb->getByDistrict();

				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary));
				}

				$this->view->subsidiaries = $subsidiaries;
				$this->renderScript('client/district.phtml');
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
			case "abeceda":
				$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
				$subsidiaries = $subsidiariesDb->getByClient();
				//kontrola jestli user má přístup
				foreach($subsidiaries as $subsidiary){
					$subsidiary->setAllowed($this->_acl->isAllowed($this->_user, $subsidiary));
				}
				$this->view->subsidiaries = $subsidiaries;
				$this->renderScript('client/alphabet.phtml');
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

		$form = $this->fillMultiselects($form);
		$form->preValidation($this->getRequest()->getPost(), $this->_responsibilityList, $this->_employeeList);
		$this->view->form = $form;
		$this->view->formResponsibility = new Application_Form_Responsibility();
		$this->view->formEmployee = new Application_Form_ResponsibleEmployee();

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
				if($invoiceAddress){
					$client->setInvoiceStreet($client->getHeadquartersStreet());
					$client->setInvoiceCode($client->getHeadquartersCode());
					$client->setInvoiceTown($client->getHeadquartersTown());
				}
				
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$subsidiary->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
					
				$clients = new Application_Model_DbTable_Client ();
				$adapter = $clients->getAdapter();

				try{
					//zahájení transakce
					$adapter->beginTransaction();
						
					//přidání klienta a kontrola IČO
					$clientId = $clients->addClient ( $client);
					if (!$clientId) {
						$defaultNamespace->formData = $formData;
						$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
						$this->_helper->redirector->gotoRoute ( array (), 'clientNew' );
					}
						
					//přidat u odpovědností a zaměstnanců nově přidaných číslo klienta
					$responsibilities = new Application_Model_DbTable_Responsibility();
					$responsibilities->assignToClient($clientId);
					$employees = new Application_Model_DbTable_Employee();
					$employees->assignToClient($clientId);

					$subsidiary->setSubsidiaryName($client->getCompanyName());
					$subsidiary->setSubsidiaryStreet($client->getHeadquartersStreet());
					$subsidiary->setSubsidiaryCode($client->getHeadquartersCode());
					$subsidiary->setSubsidiaryTown($client->getHeadquartersTown());
					$subsidiary->setClientId($clientId);
					$subsidiary->setHq(true);
						
					//přidání pobočky
					$subsidiaries = new Application_Model_DbTable_Subsidiary ();
					$subsidiaryId = $subsidiaries->addSubsidiary ( $subsidiary);
						
					//přidání kontaktních osob, lékařů a odpovědných osob
					$contactPersons = new Application_Model_DbTable_ContactPerson();
					$doctors = new Application_Model_DbTable_Doctor();
					$responsibles = new Application_Model_DbTable_Responsible();
						
					foreach($formData as $key => $value){
						if(preg_match('/contactPerson\d+/', $key) || preg_match('/newContactPerson\d+/', $key)){
							if($formData[$key]['name'] != ''){
								$contactPerson = new Application_Model_ContactPerson($formData[$key]);
								$contactPerson->setSubsidiaryId($subsidiaryId);
								$contactPersons->addContactPerson($contactPerson);
							}
						}
						if(preg_match('/doctor\d+/', $key) || preg_match('/newDoctor\d+/', $key)){
							if($formData[$key]['name'] != ''){
								$doctor = new Application_Model_Doctor($formData[$key]);
								$doctor->setSubsidiaryId($subsidiaryId);
								$doctors->addDoctor($doctor);
							}
						}
						if(preg_match('/responsibility\d+/', $key) || preg_match('/newResponsibility\d+/', $key)){
							if($formData[$key]['id_responsibility'] != 0 && $formData[$key]['id_employee'] != 0){
								$responsibles->addRelation($formData[$key]['id_responsibility'], $formData[$key]['id_employee'], $subsidiaryId);
							}
						}
					}
						
					//přiřazení práv
					if($this->_user->getRole() == My_Role::ROLE_COORDINATOR){
						$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
						$userSubs->addRelation($this->_user->getIdUser(), $subsidiaryId);
					}
						
					$this->_helper->diaryRecord($this->_username, 'přidal nového klienta', array('clientId' => $clientId), 'clientIndex', $client->getCompanyName(), $subsidiaryId);
						
					//uložení transakce
					$adapter->commit();
						
					$this->_helper->FlashMessenger ( 'Klient <strong>' . $client->getCompanyName() . '</strong> přidán' );
					$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientIndex' );
				}
				catch(Exception $e){
					//zrušení transakce
					$adapter->rollback();
					$this->_helper->FlashMessenger('Uložení klienta do databáze selhalo. ' . $e . $e->getMessage() . $e->getTraceAsString());
					$this->_helper->redirector->gotoRoute(array(), 'clientNew');
				}
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

		$defaultNamespace = new Zend_Session_Namespace();
		$defaultNamespace->referer = $this->_request->getPathInfo();

	}

	public function editAction(){
		$this->view->subtitle = 'Editace základních údajů klienta';
		$defaultNamespace = new Zend_Session_Namespace ();
		$form = $this->loadOrCreateForm($defaultNamespace);
		 
		$form->save->setLabel ( 'Uložit' );
		$form = $this->fillMultiselects($form);
		$form->preValidation($this->getRequest()->getPost(), $this->_responsibilityList, $this->_employeeList);
		$form->removeElement('contactPerson101');
		$form->removeElement('doctor201');
		$form->removeElement('responsibility301');
			
		$this->view->formResponsibility = new Application_Form_Responsibility();
		$this->view->formEmployee = new Application_Form_ResponsibleEmployee();
		 
		if(!$this->getRequest()->isPost()){
			$this->view->form = $form;
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			} else {
				$clientId = $this->_getParam('clientId');
				$clients = new Application_Model_DbTable_Client();
				$subsidiaries = new Application_Model_DbTable_Subsidiary();
				$client = $clients->getClient($clientId);
				$subsidiary = $subsidiaries->getHeadquartersWithDetails($clientId);

				$data = $client->toArray();
				$data['id_subsidiary'] = $subsidiary['subsidiary']->getIdSubsidiary();
				$data['supervision_frequency'] = $subsidiary['subsidiary']->getSupervisionFrequency();
				$data['district'] = $subsidiary['subsidiary']->getDistrict();
				$data['difficulty'] = $subsidiary['subsidiary']->getDifficulty();
				
				switch ($subsidiary['subsidiary']->getInsuranceCompany()) {
					case "Kooperativa":
						$data['insurance_company'] = 0;
					case "Česká pojišťovna":
						$data['insurance_company'] = 1;
				}

				$form->populate ( $data );
				 
				if($subsidiary['contact_persons'][0]->getIdContactPerson() != null){
					$cpOrder = $form->getElement('id_contact_person')->getValue();
					foreach($subsidiary['contact_persons'] as $contactPerson){
						$form->addElement('contactPerson', 'newContactPerson' . $cpOrder, array(
								'order' => $cpOrder,
								'value' => $contactPerson->toArray(),
								'validators' => array(new My_Form_Validator_PersonEmail()),
						));
						$cpOrder++;
					}
					$form->getElement('id_contact_person')->setValue($cpOrder);
				}

				if($subsidiary['doctors'][0]->getIdDoctor() != null){
					$dOrder = $form->getElement('id_doctor')->getValue();
					foreach($subsidiary['doctors'] as $doctor){
						$form->addElement('doctor', 'newDoctor' . $dOrder, array(
								'order' => $dOrder,
								'value' => $doctor->toArray(),
								'validators' => array(new My_Form_Validator_PersonEmail()),
						));
						$dOrder++;
					}
					$form->getElement('id_doctor')->setValue($dOrder);
				}

				if($subsidiary['responsibles'][0]['responsibility'] != null){
					$rOrder = $form->getElement('id_responsible')->getValue();
					foreach($subsidiary['responsibles'] as $responsible){
						$form->addElement('responsibility', 'newResponsibility' . $rOrder, array(
								'order' => $rOrder,
								'multiOptions' => $this->_responsibilityList,
								'multiOptions2' => $this->_employeeList,
								'value' => $responsible + $responsible['employee']->toArray(),
						));
						$rOrder++;
					}
					$form->getElement('id_responsible')->setValue($rOrder);
				}
			}
			return;
		}
		 
		if(!$form->isValid($this->getRequest()->getPost())){
			$form->populate($this->getRequest()->getPost());
			$this->view->form = $form;
			return;
		}
		 
		$formData = $this->getRequest ()->getPost ();
		$defaultNamespace->formData = $formData;
		$defaultNamespace->form = $form;
		 
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
		$adapter = $clients->getAdapter();
		 
		try{
			//zahájení transakce
			$adapter->beginTransaction();
			 
			//update klienta a kontrola IČO
			if (!$clients->updateClient ( $client)) {
				$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
				$this->_helper->redirector->gotoRoute ( array (), 'clientEdit' );
			}

			//přidat u odpovědností a zaměstnanců nově přidaných číslo klienta
			$responsibilities = new Application_Model_DbTable_Responsibility();
			$responsibilities->assignToClient($client->getIdClient());
			$employees = new Application_Model_DbTable_Employee();
			$employees->assignToClient($client->getIdClient());
			 
			$subsidiary->setSubsidiaryName($client->getCompanyName());
			$subsidiary->setSubsidiaryStreet($client->getHeadquartersStreet());
			$subsidiary->setSubsidiaryCode($client->getHeadquartersCode());
			$subsidiary->setSubsidiaryTown($client->getHeadquartersTown());
			$subsidiary->setClientId($client->getIdClient());
			$subsidiary->setHq(true);
			 
			//update pobočky
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			$subsidiaries->updateSubsidiary ( $subsidiary, true);
			$subsidiaryId = $subsidiary->getIdSubsidiary();
			
			//uložení kontaktních osob, lékařů a odpovědných osob
			$contactPersons = new Application_Model_DbTable_ContactPerson();
			$doctors = new Application_Model_DbTable_Doctor();
			$responsibles = new Application_Model_DbTable_Responsible();
			$responsibles->removeResponsibles($subsidiaryId);
			
			foreach($formData as $key => $value){
				if(preg_match('/contactPerson\d+/', $key) || preg_match('/newContactPerson\d+/', $key)){
					//update
					if($formData[$key]['id_contact_person'] != ''){
						if($formData[$key]['name'] != ''){
							$contactPerson = new Application_Model_ContactPerson($formData[$key]);
							$contactPerson->setSubsidiaryId($subsidiaryId);
							$contactPersons->updateContactPerson($contactPerson);
						}
					}
					//create
					else{
						if($formData[$key]['name'] != ''){
							$contactPerson = new Application_Model_ContactPerson($formData[$key]);
							$contactPerson->setSubsidiaryId($subsidiaryId);
							$contactPersons->addContactPerson($contactPerson);
						}
					}
					
				}
				if(preg_match('/doctor\d+/', $key) || preg_match('/newDoctor\d+/', $key)){
					if($formData[$key]['id_doctor'] != ''){
						if($formData[$key]['name'] != ''){
							$doctor = new Application_Model_Doctor($formData[$key]);
							$doctor->setSubsidiaryId($subsidiaryId);
							$doctors->updateDoctor($doctor);
						}
					}
					else{
						if($formData[$key]['name'] != ''){
							$doctor = new Application_Model_Doctor($formData[$key]);
							$doctor->setSubsidiaryId($subsidiaryId);
							$doctors->addDoctor($doctor);
						}
					}					
				}
				
				if(preg_match('/responsibility\d+/', $key) || preg_match('/newResponsibility\d+/', $key)){
					if($formData[$key]['id_responsibility'] != 0 && $formData[$key]['id_employee'] != 0){
						$responsibles->addRelation($formData[$key]['id_responsibility'], $formData[$key]['id_employee'], $subsidiaryId);
					}
				}
			}
			
			 
			$this->_helper->diaryRecord($this->_username, 'upravil klienta', array('clientId' => $client->getIdClient()), 'clientIndex', $client->getCompanyName(), $subsidiary->getIdSubsidiary());
			 
			//uložení transakce
			$adapter->commit();
			unset ($defaultNamespace->form);
			unset ( $defaultNamespace->formData );
			$this->_helper->FlashMessenger ( 'Klient <strong>' . $client->getCompanyName() . '</strong> upraven' );
			$this->_helper->redirector->gotoRoute ( array ('clientId' => $client->getIdClient() ), 'clientAdmin' );
		}
		catch(Exception $e){
			//zrušení transakce
			$adapter->rollback();
			$defaultNamespace->formData = $formData;
			$this->_helper->FlashMessenger('Uložení klienta do databáze selhalo. ' . $e . $e->getMessage() . $e->getTraceAsString());
			$this->_helper->redirector->gotoRoute(array('clientId' => $client->getIdClient()), 'clientEdit');
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

	public function newcontactpersonAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newcontactperson', 'html')->initContext();
		 
		$id = $this->_getParam('id_contact_person', null);
		 
		$element = new My_Form_Element_ContactPerson("newContactPerson$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		 
		$this->view->field = $element->__toString();
	}

	public function newdoctorAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newdoctor', 'html')->initContext();
		 
		$id = $this->_getParam('id_doctor', null);
		 
		$element = new My_Form_Element_Doctor("newDoctor$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		 
		$this->view->field = $element->__toString();
	}

	public function newresponsibleAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newresponsible', 'html')->initContext();
		 
		$id = $this->_getParam('id_responsible', null);
		 
		$element = new My_Form_Element_Responsibility("newResponsibility$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		$element->setAttrib('multiOptions', $this->_responsibilityList);
		$element->setAttrib('multiOptions2', $this->_employeeList);
		 
		$this->view->field = $element->__toString();
	}

	public function addresponsibilityAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('addresponsibility', 'html')->initContext();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		 
		$data = $this->_getAllParams();
		$responsibility = new Application_Model_Responsibility($data);
		$responsibilities = new Application_Model_DbTable_Responsibility();
		$responsibilities->addResponsibility($responsibility);
	}

	public function populateresponsibilityAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		 
		$this->_responsibilityList = My_Responsibility::getResponsibilities();
		$clientId = $this->getRequest()->getParam('clientId', null);

		$responsibilities = new Application_Model_DbTable_Responsibility();
		$extraResponsibilities = $responsibilities->getExtraResponsibilities($clientId);
		$this->_responsibilityList = $this->_responsibilityList + $extraResponsibilities;

		echo Zend_Json::encode($this->_responsibilityList);
	}

	public function addresponsibleemployeeAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('addresponsibleemployee', 'html')->initContext();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		 
		$data = $this->_getAllParams();
		$employee = new Application_Model_Employee($data);
		$employees = new Application_Model_DbTable_Employee();
		$employees->addEmployee($employee);
	}

	public function populateresponsibleemployeeAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$clientId = $this->getRequest()->getParam('clientId', null);
		 
		$this->_employeeList[0] = '-----';
		$employees = new Application_Model_DbTable_Employee();
		$this->_employeeList = $this->_employeeList + $employees->getResponsibleEmployees($clientId);
		 
		echo Zend_Json::encode($this->_employeeList);
	}

	public function removecontactpersonAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$idContactPerson = $this->getRequest()->getParam('idContactPerson');
		
		$contactPersons = new Application_Model_DbTable_ContactPerson();
		$contactPersons->deleteContactPerson($idContactPerson);
	}
	
	public function removedoctorAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$idDoctor = $this->getRequest()->getParam('idDoctor');
		
		$doctors = new Application_Model_DbTable_Doctor();
		$doctors->deleteDoctor($idDoctor);
	}
	
	private function fillMultiselects($form){
		if($form->responsibility301 != null){
			$form->responsibility301->setAttrib('multiOptions', $this->_responsibilityList);
			$form->responsibility301->setAttrib('multiOptions2', $this->_employeeList);
		}
		return $form;
	}

	private function loadOrCreateForm($defaultNamespace){
		//pokud předtím selhalo odeslání, tak se načte aktuální formulář se všemi dodatečně vloženými elementy
		if (isset ( $defaultNamespace->form )) {
			$form = $defaultNamespace->form;
			unset ( $defaultNamespace->form );
		}
		//jinak se vytvoří nový
		else{
			$form = new Application_Form_Client();
		}
		return $form;
	}

}






