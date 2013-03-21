<?php

class PositionController extends Zend_Controller_Action{
	
	private $_headquarters = null;
    private $_clientId = null;
    private $_acl = null;
    private $_user = null;
    private $_username = null;
    private $_yesNoList = array();
    private $_sexList = array();
    private $_yearOfBirthList = array();
    private $_canViewPrivate = false;
    private $_employeeList;
    private $_environmentFactorList;
    private $_categoryList;
    private $_schoolingList;
    private $_workList;
    private $_workplaceList;
    private $_frequencyList;
    private $_sortList;
    private $_typeList;
    private $_chemicalList;
    private $_positionList;
    private $_technicalDeviceList;
    private $_folderList;
    
    public function init(){
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
    	$this->_schoolingList = array_merge($defaultSchoolings, $extraSchoolings);
    	
    	//získání seznamu pracovních činností
    	$works = new Application_Model_DbTable_Work();
    	$this->_workList = $works->getWorks($this->_clientId);
    	
    	//získání seznamu pracovišť
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$this->_workplaceList = $workplaces->getWorkplaces($this->_clientId);
    	
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
    
    public function newAction(){
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
    	
    	$form->new_workplace->setAttrib('class', $subsidiaryId);
    	
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
    	 
    	$formEmployee = new Application_Form_Employee();
    	$formEmployee->clientId->setValue($this->_clientId);
    	$formEmployee->year_of_birth->setMultiOptions($this->_yearOfBirthList);
    	$formEmployee->manager->setMultiOptions($this->_yesNoList);
    	$formEmployee->sex->setMultiOptions($this->_sexList);
    	$formEmployee->save_employee->setAttrib('class', array('employee', 'position', 'ajaxSave'));
    	$this->view->formEmployee = $formEmployee;
    	
    	$form = $this->fillMultiselects($form);
    	
    	$form->save->setLabel('Uložit');
    	
    	$form->preValidation($this->getRequest()->getPost(), $this->_canViewPrivate, $this->_categoryList,
    			$this->_yesNoList);
    	
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
    	
    	//TODO odtud dále
    	
    	$this->view->form = $form;
    }
    
    public function addemployeeAction(){
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
    
    public function populateemployeesAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$employees = new Application_Model_DbTable_Employee();
    	$this->_employeeList = $employees->getEmployees($this->_clientId);
    	echo Zend_Json::encode($this->_employeeList);
    }
    
    public function addworkplaceAction(){
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
    
    public function populateworkplacesAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$this->_workplaceList = $workplaces->getWorkplaces($this->_clientId);
    	echo Zend_Json::encode($this->_workplaceList);
    }
    
    public function environmentfactordetailAction(){
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
       
    public function newschoolingAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newschooling', 'html')->initContext();
    	
    	$id = $this->_getParam('id_schooling', null);
    	
    	$element = new My_Form_Element_Schooling("newSchooling$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_schoolingList);
    	$element->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	
    	$this->view->field = $element->__toString();
    } 
    
    public function newnewschoolingAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newnewschooling', 'html')->initContext();
    	
    	$id = $this->_getParam('id_newSchooling', null);
    	
    	$element = new My_Form_Element_NewSchooling("newNewSchooling$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newworkAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newwork', 'html')->initContext();
    	
    	$id = $this->_getParam('id_work', null);
    	
    	$element = new My_Form_Element_WorkComplete("newWork$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_workList);
    	$element->setAttrib('multiOptions2', $this->_frequencyList);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newtechnicaldeviceAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newtechnicaldevice', 'html')->initContext();
    	
    	$id = $this->_getParam('id_technical_device', null);
    	
    	$element = new My_Form_Element_TechnicalDevice("newTechnicalDevice$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_sortList);
    	$element->setAttrib('multiOptions2', $this->_typeList);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newchemicalAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newchemical', 'html')->initContext();
    	
    	$id = $this->_getParam('id_chemical', null);
    	
    	$element = new My_Form_Element_Chemical("newChemical$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_chemicalList);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function listAction(){
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
    	
    	//TODO vypisování pracovních pozic
    }
    
    private function filterSubsidiarySelect($formContent){
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	foreach ($formContent as $key => $subsidiary){
    		if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
    			unset($formContent[$key]);
    		}
    	}
    	return $formContent;
    }
    
    private function initSubsidiarySwitch($formContent, $subsidiaryId){
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
    
    private function loadOrCreateForm($defaultNamespace){
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
	
    private function fillMultiselects($form){
    	if($form->workplaceList != null){
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
    	if($form->work != null){
    		$form->work->setAttrib('multiOptions', $this->_workList);
    		$form->work->setAttrib('multiOptions2', $this->_frequencyList);
    	}
    	if($form->technical_device != null){
    		$form->technical_device->setAttrib('multiOptions', $this->_sortList);
    		$form->technical_device->setAttrib('multiOptions2', $this->_typeList);
    	}
    	if($form->chemical != null){
    		$form->chemical->setAttrib('multiOptions', $this->_chemicalList);
    	}
    	
    	return $form;
    }
    
    private function findChemicalDetails($chemicalDetail){
    	if(strpos($chemicalDetail, "chemicalDetail") !== false){
    		return $chemicalDetail;
    	}
    }
    
}