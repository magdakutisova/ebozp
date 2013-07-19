<?php

class SubsidiaryController extends Zend_Controller_Action {
	private $_username;
	private $_user;
	private $_acl;
	private $_canViewHeadquarters = false;
	private $_responsibilityList = array();
	private $_employeeList = array();
	
	public function init() {
		$this->view->title = 'Správa poboček';
		$this->view->headTitle ( $this->view->title );
		$action = $this->getRequest()->getActionName();
		if($action == 'populateresponsibility' || $action == 'populateresponsibleemployee'){
			$this->_helper->layout()->setLayout('layout');
		}
		else{
			$this->_helper->layout()->setLayout('clientLayout');
		}
		$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
		
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
		
		//práva
		if(Zend_Auth::getInstance()->hasIdentity()){
			$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
		}
		
		$users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$this->_acl = new My_Controller_Helper_Acl();

		//do index a edit action může jen když má přístup k pobočce
		if ($action == 'index' || $action == 'edit'){
			$subsidiary = $subsidiaries->getSubsidiary($this->_getParam('subsidiary'));
			if(!$this->_acl->isAllowed($this->_user, $subsidiary)){
				$this->_helper->redirector('denied', 'error');
			}
		}
		
		if($action != 'populateresponsibility' && $action != 'populateresponsibleemployee'){
			if($this->_acl->isAllowed($this->_user, $subsidiaries->getHeadquarters($this->_getParam('clientId')))){
				$this->_canViewHeadquarters = true;
			}
		}		
		
		//do new a delete action může jen když má přístup k centrále
		if ($action == 'new' || $action == 'delete'){
			if(!$this->_canViewHeadquarters){
				$this->_helper->redirector('denied', 'error');
			}
		}
	}
	
	public function indexAction() {
		$clients = new Application_Model_DbTable_Client();
		$clientId = $this->_getParam ( 'clientId' );
			
		$clients->openClient ( $clientId );
		
		$client = $clients->getClient($clientId);
		
		$subsidiaries = new Application_Model_DbTable_Subsidiary();		
		
		$subsidiaryId = $this->_getParam('subsidiary');
		$subsidiary = $subsidiaries->getSubsidiaryWithDetails($subsidiaryId);
		if($subsidiary['subsidiary']->getHq()){
			$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'clientIndex');
		}
		
		$this->view->subtitle = $client->getCompanyName();
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
		$this->view->canViewHeadquarters = $this->_canViewHeadquarters;
		
		$userSubs = new Application_Model_DbTable_UserHasSubsidiary(); 
		$this->view->technicians = $userSubs->getByRoleAndSubsidiary(My_Role::ROLE_TECHNICIAN, $subsidiary['subsidiary']->getIdSubsidiary());
		
		//bezpečnostní deník
		$diary = new Application_Model_DbTable_Diary();
		$messages = $diary->getDiaryBySubsidiary($subsidiaryId);
		$this->_helper->diary($messages);
		$this->_helper->diaryMessages();
		
		$defaultNamespace = new Zend_Session_Namespace();
		$defaultNamespace->referer = $this->_request->getPathInfo();	

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
			$form->select->setLabel('Vyberte jinou pobočku:');
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
		
		/*
		 * PETR JINDRA 30. 11. 2012
		*/
		
		// kontrola ACL pro vytvoreni auditu
		$this->view->createAuditAllowed = $this->_acl->isAllowed($this->_user->getRoleId(), "audit:audit", "create");
	}
	
	public function newAction() {
		$this->view->subtitle = 'Nová pobočka';
		
		$form = new Application_Form_Subsidiary ();
		$form->save->setLabel ( 'Přidat' );
		
		$form = $this->fillMultiselects($form);
		$form->preValidation($this->getRequest()->getPost(), $this->_responsibilityList, $this->_employeeList);
		$this->view->form = $form;
		
		$clientId = $this->_getParam ( 'clientId' );
		$form->id_client->setValue($clientId);
		
		$formResponsibility = new Application_Form_Responsibility();
		$formResponsibility->getElement('save_responsibility')->setName('save_responsibility_subs');
		$formResponsibility->clientId->setValue($clientId);
		$this->view->formResponsibility = $formResponsibility;
		$formEmployee = new Application_Form_ResponsibleEmployee();
		$formEmployee->getElement('save_responsible_employee')->setName('save_responsible_employee_subs');
		$formEmployee->clientId->setValue($clientId);
		$this->view->formEmployee = $formEmployee;
		
		
		$clients = new Application_Model_DbTable_Client ();
		
		$client = $clients->getClient ( $clientId );
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$subsidiary = new Application_Model_Subsidiary($formData);
				
				if ($subsidiary->getSubsidiaryName() == null) {
					$subsidiary->setSubsidiaryName($clients->getCompanyName ( $clientId ));
				}
				
				$subsidiary->setClientId($clientId);
				$subsidiary->setHq(0);
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$subsidiary->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$adapter = $subsidiaries->getAdapter();
				
				try{
					//zahájení transakce
					$adapter->beginTransaction();
					
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
					
					if($this->_user->getRole() == My_Role::ROLE_COORDINATOR){
						$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
						$userSubs->addRelation($this->_user->getIdUser(), $subsidiaryId);
					}
					
					$this->_helper->diaryRecord($this->_username, 'přidal novou pobočku', array ('clientId' => $clientId, 'subsidiary' => $subsidiaryId ), 'subsidiaryIndex', $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown(), $subsidiaryId);
					
					//uložení transakce
					$adapter->commit();
					
					$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown() . '</strong> přidána' );
					if ($form->getElement ( 'other' )->isChecked ()) {
						$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'subsidiaryNew' );
					} else {
						$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
					}
				}
				catch(Exception $e){
					//zrušení transakce
					$adapter->rollback();
					$this->_helper->FlashMessenger('Uložení klienta do databáze selhalo. ' . $e . $e->getMessage() . $e->getTraceAsString());
					$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'subsidiaryNew');
				}
			}
		}
	}
	
	public function editAction() {
		$this->view->subtitle = 'Editace pobočky';
			
		$defaultNamespace = new Zend_Session_Namespace();
		$form = $this->loadOrCreateForm($defaultNamespace);
		$form->save->setLabel ( 'Uložit' );
		
		$form = $this->fillMultiselects($form);
		$form->preValidation($this->getRequest()->getPost(), $this->_responsibilityList, $this->_employeeList);
		$form->removeElement('contactPerson101');
		$form->removeElement('doctor201');
		$form->removeElement('responsibility301');
		
		$form->removeElement ( 'other' );
		$this->view->form = $form;
		
		$subsidiaryId = $this->_getParam ( 'subsidiary' );
		$clientId = $this->_getParam ( 'clientId' );
		$form->id_client->setValue($clientId);
		
		$formResponsibility = new Application_Form_Responsibility();
		$formResponsibility->getElement('save_responsibility')->setName('save_responsibility_subs');
		$formResponsibility->clientId->setValue($clientId);
		$this->view->formResponsibility = $formResponsibility;
		$formEmployee = new Application_Form_ResponsibleEmployee();
		$formEmployee->getElement('save_responsible_employee')->setName('save_responsible_employee_subs');
		$formEmployee->clientId->setValue($clientId);
		$this->view->formEmployee = $formEmployee;
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$subsidiary = new Application_Model_Subsidiary($formData);
				
				if ($subsidiary->getSubsidiaryName() == null) {
					$clients = new Application_Model_DbTable_Client ();
					$subsidiary->setSubsidiaryName($clients->getCompanyName ( $clientId ));
				}
				
				$subsidiary->setHq(0);
				$subsidiary->setClientId($clientId);
				$insuranceCompanyOptions = $form->getElement('insurance_company')->getMultiOptions();
				$subsidiary->setInsuranceCompany($insuranceCompanyOptions[$form->getValue('insurance_company')]);
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$adapter = $subsidiaries->getAdapter();
				
				try{
					//init session pro případ selhání ukládání
					$formData = $this->getRequest()->getPost();
					$defaultNamespace->formData = $formData;
					$defaultNamespace->form = $form;
					
					//zahájení transakce
					$adapter->beginTransaction();
					
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
					
					$this->_helper->diaryRecord($this->_username, 'upravil pobočku', array ('clientId' => $clientId, 'subsidiary' => $subsidiaryId ), 'subsidiaryIndex', $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown(), $subsidiaryId);
					
					//uložení transakce
					$adapter->commit();
					unset($defaultNamespace->form);
					unset($defaultNamespace->formData);
					
					$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown() . '</strong> upravena' );
					
					$defaultNamespace = new Zend_Session_Namespace();
					if (isset($defaultNamespace->referer)){
						$path = $defaultNamespace->referer;
						unset($defaultNamespace->referer);
						$this->_redirect($path);
					}
					else{
						$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
					}
				}	
				catch(Exception $e){
					//zrušení transakce
					$adapter->rollback();
					$this->_helper->FlashMessenger('Uložení klienta do databáze selhalo. ' . $e . $e->getMessage() . $e->getTraceAsString());
					$this->_helper->redirector->gotoRoute(array('clientId' => $clientId, 'subsidiary' => $subsidiaryId), 'subsidiaryEdit');
				}
			}
		} else {
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			$subsidiary = $subsidiaries->getSubsidiaryWithDetails ( $subsidiaryId );
					
			$form->populate ( $subsidiary['subsidiary']->toArray());
			
			if(isset($subsidiary['contact_persons'])){
				$cpOrder = $form->getElement('id_contact_person')->getValue();
				foreach($subsidiary['contact_persons'] as $contactPerson){
					$form->addElement('contactPerson', 'newContactPerson' . $cpOrder, array(
							'order' => $cpOrder,
							'value' => $contactPerson->toArray(),
							'validators' => array(new My_Form_Validator_PersonEmail()),
							'calledFrom' => 'subs',
					));
					$cpOrder++;
				}
				$form->getElement('id_contact_person')->setValue($cpOrder);
			}
			
			if(isset($subsidiary['doctors'])){
				$dOrder = $form->getElement('id_doctor')->getValue();
				foreach($subsidiary['doctors'] as $doctor){
					$form->addElement('doctor', 'newDoctor' . $dOrder, array(
							'order' => $dOrder,
							'value' => $doctor->toArray(),
							'validators' => array(new My_Form_Validator_PersonEmail()),
							'calledFrom' => 'subs',
					));
					$dOrder++;
				}
				$form->getElement('id_doctor')->setValue($dOrder);
			}
			
			if(isset($subsidiary['responsibles'])){
				$rOrder = $form->getElement('id_responsible')->getValue();
				foreach($subsidiary['responsibles'] as $responsible){
					$form->addElement('responsibility', 'newResponsibility' . $rOrder, array(
							'order' => $rOrder,
							'multiOptions' => $this->_responsibilityList,
							'multiOptions2' => $this->_employeeList,
							'value' => $responsible + $responsible['employee']->toArray(),
							'calledFrom' => 'subs',
					));
					$rOrder++;
				}
				$form->getElement('id_responsible')->setValue($rOrder);
			}			
			
		}
	}
	
	public function deleteAction() {
		if ($this->getRequest ()->getMethod () == 'POST') {
			$clientId = $this->_getParam ( 'clientId' );
			$subsidiaryId = $this->_getParam ( 'select' );			
			
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			
			$subsidiary = $subsidiaries->getSubsidiary ( $subsidiaryId );
			$subsidiaryName = $subsidiary->getSubsidiaryName();
			$subsidiaryTown = $subsidiary->getSubsidiaryTown();
			
			
			$subsidiaries->deleteSubsidiary ( $subsidiaryId );
			
			$this->_helper->diaryRecord($this->_username, 'smazal pobočku', null, null, $subsidiaryName . ', ' . $subsidiaryTown, $subsidiaryId);
			
			$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiaryName . ', ' . $subsidiaryTown . '</strong> smazána' );
			$this->_helper->redirector->gotoRoute (array ('clientId' => $clientId ), 'clientAdmin' );
		} else {
			throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání pobočky.', 500 );
		}
	}
	
	public function listAction(){
		$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
		$subsidiaries = $subsidiariesDb->getSubsidiariesComplete($this->getRequest()->getParam('clientId'));
		$subsidiaryList = array();
		foreach($subsidiaries as $subsidiary){
			if($this->_acl->isAllowed($this->_user, $subsidiary)){
				$subsidiaryList[] = $subsidiary;
			}
		}
		$this->view->subsidiaries = $subsidiaryList;
		$this->view->subtitle = 'Seznam poboček klienta ' . $subsidiaries[0]->getSubsidiaryName();
	}
	
	public function newcontactpersonAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newcontactperson', 'html')->initContext();
		
		$id = $this->_getParam('id_contact_person', null);
		
		$element = new My_Form_Element_ContactPerson("newContactPerson$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		$element->setAttrib('calledFrom', 'subs');
		
		$this->view->field = $element->__toString();
	}
	
	public function newdoctorAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newdoctor', 'html')->initContext();
		
		$id = $this->_getParam('id_doctor', null);
		
		$element = new My_Form_Element_Doctor("newDoctor$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		$element->setAttrib('calledFrom', 'subs');
		
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
		$element->setAttrib('calledFrom', 'subs');
		
		$this->view->field = $element->__toString();
	}
	
	public function addresponsibilityAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('addresponsibility', 'html')->initContext();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		$data = $this->_getAllParams();
		$responsibility = new Application_Model_Responsibility($data);
		$responsibility->setClientId($this->getParam('clientId'));
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
		$employee->setClientId($data['clientId']);
		$employees = new Application_Model_DbTable_Employee();
		$employees->addEmployee($employee);
	}
	
	public function populateresponsibleemployeeAction(){
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		$clientId = $this->getRequest()->getParam('clientId', null);
		
		$this->_employeeList = array();
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
			$form = new Application_Form_Subsidiary();
		}
		return $form;
	}
	
}





