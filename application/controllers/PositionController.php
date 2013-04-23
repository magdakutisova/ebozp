<?php

class PositionController extends Zend_Controller_Action
{

    private $_headquarters = null;

    private $_clientId = null;

    private $_acl = null;

    private $_user = null;

    private $_username = null;

    private $_yesNoList = array();

    private $_sexList = array();

    private $_yearOfBirthList = array();

    private $_canViewPrivate = false;

    private $_employeeList = null;

    private $_environmentFactorList = null;

    private $_categoryList = null;

    private $_schoolingList = null;

    private $_workList = null;

    private $_workplaceList = null;

    private $_frequencyList = null;

    private $_sortList = null;

    private $_typeList = null;

    private $_chemicalList = null;

    private $_positionList = null;

    private $_technicalDeviceList = null;

    private $_folderList = null;

    public function init()
    {
    	//globální nastavení view
    	$this->view->title = 'Pracovní pozice';
    	$this->view->headTitle($this->view->title);
    	$this->_helper->layout()->setLayout('clientLayout');
    	$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
    	
    	//získání odkazu na centrálu - instance Application_Model_Subsidiary
    	$action = $this->getRequest()->getActionName();
    	$this->_acl = new My_Controller_Helper_Acl();
    	$this->_clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$this->_headquarters = $subsidiaries->getHeadquarters($this->_clientId);
    	
    	//získání seznamu ano/ne
    	$this->_yesNoList[0] = 'Ne';
    	$this->_yesNoList[1] = 'Ano';
    	
    	//získání seznamu pohlaví
    	$this->_sexList[0] = 'Muž';
    	$this->_sexList[1] = 'Žena';
    	
    	//získání seznamu roků narození
    	for ($i=1920; $i<=date('Y'); $i++){
    		$this->_yearOfBirthList[$i] = $i;
    	}
    	
    	//získání seznamu zaměstnanců
    	$employees = new Application_Model_DbTable_Employee();
    	$this->_employeeList = $employees->getEmployees($this->_clientId);
    	
    	//získání seznamu FPP
    	$this->_environmentFactorList = My_EnvironmentFactor::getEnvironmentFactors();
    	
    	//získání kategorií FPP
    	$this->_categoryList = My_EnvironmentFactor::getCategories();
    	
    	//získání seznamu školení
    	$defaultSchoolings = My_Schooling::getSchoolings();
    	$schoolings = new Application_Model_DbTable_Schooling();
    	$extraSchoolings = $schoolings->getExtraSchoolings($this->_clientId);
    	$this->_schoolingList = $defaultSchoolings + $extraSchoolings;
    	
    	//získání seznamu pracovních činností
    	$works = new Application_Model_DbTable_Work();
    	$this->_workList = $works->getWorks($this->_clientId);
    	
    	//získání seznamu pracovišť
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$this->_workplaceList = $workplaces->getWorkplacesWithSubsidiaryName($this->_clientId);
    	
    	//získání seznamu četností
    	$this->_frequencyList = My_Frequency::getFrequencies();
    	
    	//získání seznamu technických prostředků    	   
    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
    	$this->_sortList = $technicalDevices->getSorts($this->_clientId);
    	$this->_typeList = $technicalDevices->getTypes($this->_clientId);
    	$this->_technicalDeviceList = $technicalDevices->getTechnicalDevices($this->_clientId);   	
    	
    	//získání seznamu chemických látek
    	$chemicals = new Application_Model_DbTable_Chemical();
    	$this->_chemicalList = $chemicals->getChemicals($this->_clientId);
    	
    	//získání seznamu pracovních pozic
    	$positions = new Application_Model_DbTable_Position();
    	$this->_positionList = $positions->getPositions($this->_clientId);
    	
    	//získání seznamu umístění
    	$folders = new Application_Model_DbTable_Folder();
    	$this->_folderList = $folders->getFolders($this->_clientId);
    	
    	//přístupová práva
    	$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
    	$users = new Application_Model_DbTable_User();
    	$this->_user = $users->getByUsername($this->_username);
    	
    	//soukromá poznámka
    	$this->_canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
    	$this->view->canViewPrivate = $this->_canViewPrivate;
    }

    public function newAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace();
    	$this->view->subtitle = "Zadat pracovní pozici";
    	$form = $this->loadOrCreateForm($defaultNamespace);
    	
    	//získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	 
    	$form->client_id->setValue($clientId);
    	 
    	//naplnění multiselectu pobočkami
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    		$form->subsidiaryList->setMultiOptions ( $formContent );
    	}
    	$form->subsidiaryList->setValue($subsidiaryId);
    	
    	//inicializace plovoucích formulářů
    	$this->initFloatingForms($formContent, $subsidiaryId);
    	
    	//naplnění formuláře hodnotami z DB
    	$form = $this->fillMultiselects($form);
    	$form->new_workplace->setAttrib('class', $subsidiaryId);
    	$form->save->setLabel('Uložit');
    	
    	$form->preValidation($this->getRequest()->getPost(), $this->_canViewPrivate, $this->_categoryList,
    			$this->_yesNoList, $this->_frequencyList);
    	
    	//pokud formulář není odeslán, předáme formulář do view
    	if(!$this->getRequest()->isPost()){
    		$this->view->form = $form;
    	
    		// naplnění formuláře daty ze session, pokud existují
    		if (isset ( $defaultNamespace->formData )) {
    			$form->populate ( $defaultNamespace->formData );
    			unset ( $defaultNamespace->formData );
    		}
    		return;
    	}
    	 
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form->populate($form->getValues());
    		$this->view->form = $form;
    		return;
    	}
    	
    	$positions = new Application_Model_DbTable_Position();
    	$adapter = $positions->getAdapter();
    	
    	//zpracování formuláře
    	try{
    		//init session pro případ selhání ukládání
    		$formData = $this->getRequest()->getPost();
    		$defaultNamespace->formData = $formData;
    		$defaultNamespace->form = $form;
    		
    		//zahájení transakce
    		$adapter->beginTransaction();
    		
    		if(!array_key_exists('subsidiaryList', $formData)){
    			throw new Exception("Vyberte alespoň jednu pobočku.");
    		}
    		
    		//kontrola na pracoviště na pobočkách
    		if(isset($formData['workplaceList'])){
    			$workplaces = new Application_Model_DbTable_Workplace();
    			foreach($formData['workplaceList'] as $workplaceId){
    				$result = $workplaces->existsWithinSubsidiaries($workplaceId, $formData['subsidiaryList']);
    				if($result != "OK"){
    					throw new Exception($result);
    				}
    			}
    		}
    		
    		//vložení pracovní pozice
    		$position = new Application_Model_Position($formData);
    		$position->setClientId($this->_clientId);
    		$positionId = $positions->addPosition($position);
    		
    		$this->processCustomElements($formData, $positionId);
    		
    		//uložení transakce
    		$adapter->commit();
    		foreach($formData['subsidiaryList'] as $subs){
    			$subsidiary = $subsidiaries->getSubsidiary($subs);
    			$this->_helper->diaryRecord($this->_username, 'přidal pracovní pozici "' . $position->getPosition() . '" k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subs, 'filter' => 'vse'), 'positionList', '(databáze pracovních pozic)', $subs);
    		}
    		$this->_helper->FlashMessenger('Pracovní pozice ' . $position->getPosition() . ' přidána.');
    		unset($defaultNamespace->form);
    		unset($defaultNamespace->formData);
    		if($form->getElement('other')->isChecked()){
    			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'positionNew');
    		}
    		else{
    			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'positionList');
    		}
    	}
    	catch(Exception $e){
    		//zrušení transakce
    		$adapter->rollback();
    		$this->_helper->FlashMessenger($e->getMessage() . ' Uložení pracovní pozice do databáze selhalo.');
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'positionNew');
    	}
    	
    	$this->view->form = $form;
    }

    public function addemployeeAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addemployee', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$employee = new Application_Model_Employee($data);
    	$employees = new Application_Model_DbTable_Employee();
    	$employee->setClientId($this->_getParam('clientId'));
    	$employeeId = $employees->addEmployee($employee);
    }

    public function populateemployeesAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$employees = new Application_Model_DbTable_Employee();
    	$this->_employeeList = $employees->getEmployees($this->_clientId);
    	echo Zend_Json::encode($this->_employeeList);
    }

    public function addworkplaceAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addworkplace', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$workplace = new Application_Model_Workplace($data);
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$workplace->setClientId($this->_getParam('clientId'));
    	if($data['folder_id'] == 0){
    		$workplace->setFolderId(null);
    	}
    	$workplaceId = $workplaces->addWorkplace($workplace);
    	if(!$workplaceId){
    		//TODO nějaké ošetření pokud bude třeba
    		return;
    	}
    	
    	$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
    	$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
    	$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
    	$workplaceHasChemical = new Application_Model_DbTable_WorkplaceHasChemical();
    	 
    	foreach ($data['positionList'] as $positionId){
    		$workplaceHasPosition->addRelation($workplaceId, $positionId);
    	}
    	foreach ($data['workList'] as $workId){
    		$workplaceHasWork->addRelation($workplaceId, $workId);
    	}
    	foreach ($data['technicaldeviceList'] as $technicalDeviceId){
    		$workplaceHasTechnicalDevice->addRelation($workplaceId, $technicalDeviceId);
    	}
    	foreach ($data['chemicalList'] as $chemicalId){
    		$usePurpose = "";
    		$usualAmount = "";
    		$chemicalDetails = array_filter(array_keys($data), array($this, 'findChemicalDetails'));
    		foreach($chemicalDetails as $detail){
    			if($data[$detail]['id_chemical'] == $chemicalId){
    				$usePurpose = $data[$detail]['use_purpose'];
    				$usualAmount = $data[$detail]['usual_amount'];
    				break 1;
    			}
    		}
    		$workplaceHasChemical->addRelation($workplaceId, $chemicalId, $usePurpose, $usualAmount);
    	}
    	
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
    	$this->_helper->diaryRecord($this->_username, 'přidal pracoviště "' . $workplace->getName() . '" k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary(), 'filter' => 'vse'), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
    }

    public function populateworkplacesAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$this->_workplaceList = $workplaces->getWorkplaces($this->_clientId);
    	echo Zend_Json::encode($this->_workplaceList);
    }

    public function environmentfactordetailAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('environmentfactordetail', 'html')->initContext();
    	
    	$id = $this->getParam('id_environment_factor', null);
    	
    	$element = new My_Form_Element_EnvironmentFactorDetail("environmentFactorDetail$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setIdEnvironmentFactor($this->_getParam('idEnvironmentFactor'));
    	$element->setFactor($this->_getParam('environmentFactor'));
    	$element->setAttrib('multiOptions', $this->_categoryList);
    	$element->setAttrib('multiOptions2', $this->_yesNoList);
    	$element->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	
    	$this->view->field = $element->__toString();
    }

    public function addschoolingAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addschooling', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$schoolings = new Application_Model_DbTable_Schooling();
    	$schooling = new Application_Model_Schooling($data);
    	$schooling->setClientId($this->_getParam('clientId'));
    	$schoolings->addSchooling($schooling);
    }

    public function populateschoolingsAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$defaultSchoolings = My_Schooling::getSchoolings();
    	$schoolings = new Application_Model_DbTable_Schooling();
    	$extraSchoolings = $schoolings->getExtraSchoolings($this->_clientId);
    	$this->_schoolingList = $defaultSchoolings + $extraSchoolings;
    	echo Zend_Json::encode($this->_schoolingList);
    }

    public function schoolingdetailAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('schoolingdetail', 'html')->initContext();
    	
    	$id = $this->_getParam('id_schooling', null);
    	
    	$element = new My_Form_Element_SchoolingDetail("schoolingDetail$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setIdSchooling($this->_getParam('idSchooling'));
    	$element->setSchooling($this->_getParam('schooling'));
    	$element->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	
    	$this->view->field = $element->__toString();
    }

    public function workdetailAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('workdetail', 'html')->initContext();
    	
    	$id = $this->_getParam('id_work', null);
    	
    	$element = new My_Form_Element_WorkDetail("workDetail$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setIdWork($this->_getParam('idWork'));
    	$element->setWork($this->_getParam('work'));
    	$element->setAttrib('multiOptions', $this->_frequencyList);
    	
    	$this->view->field = $element->__toString();
    }

    public function chemical2detailAction()
    {
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('chemical2detail', 'html')->initContext();
    	
    	$id = $this->_getParam('id_chemical2', null);
    	
    	$element = new My_Form_Element_Chemical2Detail("chemical2Detail$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setIdChemical($this->_getParam('idChemical'));
    	$element->setChemical($this->_getParam('chemical'));
    	
    	$this->view->field = $element->__toString();
    }

    public function listAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace();
    	if (isset($defaultNamespace->form)){
    		unset($defaultNamespace->form);
    	}
    	if (isset($defaultNamespace->formData)){
    		unset($defaultNamespace->formData);
    	}
    	
    	$clients = new Application_Model_DbTable_Client();
    	$client = $clients->getClient($this->_clientId);
    	
    	$this->view->subtitle = "Databáze pracovních pozic - " . $client->getCompanyName();
    	$this->view->clientId = $this->_clientId;
    	$filter = $this->getRequest()->getParam('filter');
    	$this->view->filter = $filter;
    	
    	//výběr poboček
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    	}
    	
    	$subsidiaryId = null;
    	 
    	if ($formContent != 0) {
    		$subsidiaryId = $this->initSubsidiarySwitch($formContent, $subsidiaryId);
    	}
    	else{
    		$selectForm = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
    		$this->view->selectForm = $selectForm;
    	}
    	
    	if($subsidiaryId != null){
    		if(!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($subsidiaryId))){
    			$this->_helper->redirector->gotoSimple('denied', 'error');
    		}
    		
    		//vypisování pracovních pozic
    		$positionDb = new Application_Model_DbTable_Position();
    		if($filter == 'vse'){
    			$positions = $positionDb->getBySubsidiaryWithDetails($subsidiaryId);
    		}
    		if($filter == 'neuplne'){
    			$positions = $positionDb->getBySubsidiaryWithDetails($subsidiaryId, true);
    		}
    		$this->view->positions = $positions;
    	}
    }

    private function filterSubsidiarySelect($formContent)
    {
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	foreach ($formContent as $key => $subsidiary){
    		if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
    			unset($formContent[$key]);
    		}
    	}
    	return $formContent;
    }

    private function initSubsidiarySwitch($formContent, $subsidiaryId)
    {
    	$selectForm = new Application_Form_Select ();
    	$selectForm->select->setMultiOptions ( $formContent );
    	$selectForm->select->setLabel('Vyberte pobočku:');
    	$selectForm->submit->setLabel('Vybrat');
    	$this->view->selectForm = $selectForm;
    	$subsidiaryId = array_shift(array_keys($formContent));
    		
    	if ($this->getRequest ()->isPost () && in_array('Vybrat', $this->getRequest()->getPost())) {
    		$formData = $this->getRequest ()->getPost ();
    		$subsidiaryId = $formData['select'];
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'positionList');
    	}
    	else{
    		$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    		$selectForm->select->setValue($subsidiaryId);
    	}
    	$this->view->subsidiaryId = $subsidiaryId;
    	return $subsidiaryId;
    }

    private function loadOrCreateForm($defaultNamespace)
    {
    	//pokud předtím selhalo odeslání, tak se načte aktuální formulář se všemi dodatečně vloženými elementy
    	if (isset ( $defaultNamespace->form )) {
    		$form = $defaultNamespace->form;
    		unset ( $defaultNamespace->form );
    	}
    	//jinak se vytvoří nový
    	else{
    		$form = new Application_Form_Position();
    	}
    	return $form;
    }

    private function fillMultiselects($form)
    {
    	if($form->workplaceList != null && $this->_workplaceList != 0){
    		$form->workplaceList->setMultiOptions($this->_workplaceList);
    	}
    	if($form->employeeList != null){
    		$form->employeeList->setMultiOptions($this->_employeeList);
    	}
    	if($form->environmentfactorList != null){
    		$form->environmentfactorList->setMultiOptions($this->_environmentFactorList);
    	}
    	if($form->schoolingList != null){
    		$form->schoolingList->setMultiOptions($this->_schoolingList);
    	}
    	if($form->workList != null){
    		$form->workList->setMultiOptions($this->_workList);
    	}
    	if($form->technicaldeviceList != null){
    		$form->technicaldeviceList->setMultiOptions($this->_technicalDeviceList);
    	}
    	if($form->chemicalList != null){
    		$form->chemicalList->setMultiOptions($this->_chemicalList);
    	}
    	
    	return $form;
    }

    private function findChemicalDetails($chemicalDetail)
    {
    	if(strpos($chemicalDetail, "chemicalDetail") !== false){
    		return $chemicalDetail;
    	}
    }

    private function initFloatingForms($formContent, $subsidiaryId)
    {
    	$formWorkplace = new Application_Form_Workplace();
    	$formWorkplace->clientId->setValue($this->_clientId);
    	$formWorkplace->subsidiary_id->setMultiOptions($formContent);
    	$formWorkplace->subsidiary_id->setValue($subsidiaryId);
    	$formWorkplace->positionList->setMultiOptions($this->_positionList);
    	$formWorkplace->workList->setMultiOptions($this->_workList);
    	$formWorkplace->technicaldeviceList->setMultiOptions($this->_technicalDeviceList);
    	$formWorkplace->chemicalList->setMultiOptions($this->_chemicalList);
    	$formWorkplace->folder_id->setMultiOptions($this->_folderList);
    	$formWorkplace->removeElement('new_position');
    	$formWorkplace->removeElement('other');
    	$formWorkplace->removeElement('save');
    	$formWorkplace->addElement('button', 'save', array(
    			'decorators' => array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5)),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    			'order' => 2004,
    			'class' => array('workplace', 'position', 'ajaxSave'),
    			'label' => 'Uložit',
    	));
    	$this->view->formWorkplace = $formWorkplace;
    	 
    	$formWork = new Application_Form_Work();
    	$formWork->clientId->setValue($this->_clientId);
    	$formWork->save_work->setAttrib('class', array('work', 'workplace', 'ajaxSave'));
    	$this->view->formWork = $formWork;
    	 
    	$formTechnicalDevice = new Application_Form_TechnicalDevice();
    	$formTechnicalDevice->clientId->setValue($this->_clientId);
    	$formTechnicalDevice->save_technicaldevice->setAttrib('class', array('technicaldevice', 'workplace', 'ajaxSave'));
    	$this->view->formTechnicalDevice = $formTechnicalDevice;
    	 
    	$formChemical = new Application_Form_Chemical();
    	$formChemical->clientId->setValue($this->_clientId);
    	$formChemical->save_chemical->setAttrib('class', array('chemical', 'workplace', 'ajaxSave'));
    	$this->view->formChemical = $formChemical;
    	 
    	$formFolder = new Application_Form_Folder();
    	$formFolder->clientId->setValue($this->_clientId);
    	$formFolder->save_folder->setAttrib('class', array('folder', 'workplace', 'ajaxSave'));
    	$this->view->formFolder = $formFolder;
    	 
    	$formSchooling = new Application_Form_Schooling();
    	$formSchooling->clientId->setValue($this->_clientId);
    	$formSchooling->save_schooling->setAttrib('class', array('schooling', 'position', 'ajaxSave'));
    	$this->view->formSchooling = $formSchooling;
    	
    	$formEmployee = new Application_Form_Employee();
    	$formEmployee->clientId->setValue($this->_clientId);
    	$formEmployee->year_of_birth->setMultiOptions($this->_yearOfBirthList);
    	$formEmployee->manager->setMultiOptions($this->_yesNoList);
    	$formEmployee->sex->setMultiOptions($this->_sexList);
    	$formEmployee->save_employee->setAttrib('class', array('employee', 'position', 'ajaxSave'));
    	$this->view->formEmployee = $formEmployee;
    }

    private function processCustomElements($formData, $positionId, $toEdit = false)
    {
    	$subsidiaryHasPosition = new Application_Model_DbTable_SubsidiaryHasPosition();
    	$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
    	$positionHasEnvironmentFactor = new Application_Model_DbTable_PositionHasEnvironmentFactor();
    	$positionHasSchooling = new Application_Model_DbTable_PositionHasSchooling();
    	$positionHasWork = new Application_Model_DbTable_PositionHasWork();
    	$positionHasTechnicalDevice = new Application_Model_DbTable_PositionHasTechnicalDevice();
    	$positionHasChemical = new Application_Model_DbTable_PositionHasChemical();
    	$employees = new Application_Model_DbTable_Employee();
    	
    	foreach($formData['subsidiaryList'] as $subsidiaryId){
    		$subsidiaryHasPosition->addRelation($subsidiaryId, $positionId);
    	}
    	foreach($formData['workplaceList'] as $workplaceId){
    		$workplaceHasPosition->addRelation($workplaceId, $positionId);
    	}
    	if($formData['categorization'] == 1){
    		$environmentFactorDetails = array_filter(array_keys($formData), array($this, 'findEnvironmentFactorDetails'));
    		foreach($formData['environmentfactorList'] as $environmentFactorId){
    			$category = '';
    			$measurementTaken = '';
    			$protectionMeasures = '';
    			$note = '';
    			$private = '';
    			foreach ($environmentFactorDetails as $detail){
    				if($formData[$detail]['id_environment_factor'] == $environmentFactorId){
    					$category = $formData[$detail]['category'];
    					$measurementTaken = $formData[$detail]['measurement_taken'];
    					$protectionMeasures = $formData[$detail]['protection_measures'];
    					$note = $formData[$detail]['note'];
    					$private = $formData[$detail]['private'];
    					break 1;
    				}
    			}
    			$positionHasEnvironmentFactor->addRelation($positionId, $environmentFactorId, $category, $protectionMeasures, $measurementTaken, $note, $private);
    		}
    	}
    	$schoolingDetails = array_filter(array_keys($formData), array($this, 'findSchoolingDetails'));
    	$schoolingList = array();
    	if(array_key_exists('schoolingList', $formData)){
    		$schoolingList = $formData['schoolingList'];
    	}
    	$schoolingList[] = 1;
    	$schoolingList[] = 2;
    	foreach($schoolingList as $schoolingId){
    		$note = '';
    		$private = '';
    		foreach($schoolingDetails as $detail){
    			if($formData[$detail]['id_schooling'] == $schoolingId){
    				$note = $formData[$detail]['note'];
    				$private = $formData[$detail]['private'];
    				break 1;
    			}
    		}
    		$positionHasSchooling->addRelation($positionId, $schoolingId, $note, $private);
    	}
    	$workDetails = array_filter(array_keys($formData), array($this, 'findWorkDetails'));
    	foreach($formData['workList'] as $workId){
    		$frequency = null;
    		foreach($workDetails as $detail){
    			if($formData[$detail]['id_work'] == $workId){
    				$frequencyKey = $formData[$detail]['frequency'];
    				if($frequencyKey != 0){
    					if($frequencyKey != 6){
    						$frequencies = My_Frequency::getFrequencies();
    						$frequency = $frequencies[$frequencyKey];
    					}
    					else{
    						$frequency = $formData[$detail]['new_frequency'];
    					}
    				}
    				break 1;
    			}
    		}
    		$positionHasWork->addRelation($positionId, $workId, $frequency);
    	}
    	foreach($formData['technicaldeviceList'] as $technicalDeviceId){
    		$positionHasTechnicalDevice->addRelation($positionId, $technicalDeviceId);
    	}
    	$chemical2Details = array_filter(array_keys($formData), array($this, 'findChemical2Details'));
    	foreach($formData['chemicalList'] as $chemicalId){
    		$exposition = '';
    		foreach($chemical2Details as $detail){
    			if($formData[$detail]['id_chemical'] == $chemicalId){
    				$exposition = $formData[$detail]['exposition'];
    				break 1;
    			}
    		}
    		$positionHasChemical->addRelation($positionId, $chemicalId, $exposition);
    	}
    	foreach($formData['employeeList'] as $employeeId){
    		$employee = $employees->getEmployee($employeeId);
    		$employee->setPositionId($positionId);
    		$employees->updateEmployee($employee);
    	}
    }

    private function findEnvironmentFactorDetails($environmentFactorDetail)
    {
    	if(strpos($environmentFactorDetail, "environmentFactorDetail") !== false){
    		return $environmentFactorDetail;
    	}
    }

    private function findSchoolingDetails($schoolingDetail)
    {
    	if(strpos($schoolingDetail, "schoolingDetail") !== false){
    		return $schoolingDetail;
    	}
    }

    private function findWorkDetails($workDetail)
    {
    	if(strpos($workDetail, "workDetail") !== false){
    		return $workDetail;
    	}
    }

    private function findChemical2Details($chemical2Detail)
    {
    	if(strpos($chemical2Detail, "chemical2Detail") !== false){
    		return $chemical2Detail;
    	}
    }

    public function editAction()
    {
        // action body
    }

    public function deleteAction()
    {
        // action body
    }


}



