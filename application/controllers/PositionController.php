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
    	$this->_schoolingList = My_Schooling::getSchoolings();
    	
    	//získání seznamu pracovních činností
    	$works = new Application_Model_DbTable_Work();
    	$this->_workList = $works->getWorks($this->_clientId);
    	
    	//získání seznamu pracovišť
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$this->_workplaceList = $workplaces->getWorkplaces($this->_clientId);
    	
    	//získání seznamu četností
    	$this->_frequencyList = My_Frequency::getFrequencies();
    	
    	//získání seznamů druhů a typů technických prostředků
    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
    	$this->_sortList = $technicalDevices->getSorts($this->_clientId);
    	$this->_typeList = $technicalDevices->getTypes($this->_clientId);
    	
    	//získání seznamu chemických látek
    	$chemicals = new Application_Model_DbTable_Chemical();
    	$this->_chemicalList = $chemicals->getChemicals($this->_clientId);
    	
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
    	
    	$formEmployee = new Application_Form_Employee(array('yearOfBirthList' => $this->_yearOfBirthList,
    			'yesNoList' => $this->_yesNoList,
    			'sexList' => $this->_sexList,
    			'clientId' => $this->_clientId));
    	$formEmployee->save_employee->setAttrib('class', array('employee', 'ajaxSave'));
    	$this->view->formEmployee = $formEmployee;
    	
    	//získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	 
    	$form->client_id->setValue($clientId);
    	 
    	//naplnění multiselectu pobočkami
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    		$form->subsidiary_id->setMultiOptions ( $formContent );
    	}
    	$form->subsidiary_id->setValue($subsidiaryId);
    	
    	$form = $this->fillMultiselects($form);
    	
    	$form->save->setLabel('Uložit');
    	
    	$form->preValidation($this->getRequest()->getPost(),
    			$this->_canViewPrivate, $this->_employeeList, $this->_environmentFactorList, $this->_categoryList,
    			$this->_schoolingList, $this->_workList, $this->_workplaceList, $this->_frequencyList, $this->_sortList,
    			$this->_typeList, $this->_chemicalList, $this->_yesNoList);
    	
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
    	
    	$data = $this->_getAllParams();
    	$employee = new Application_Model_Employee($data);
    	$employees = new Application_Model_DbTable_Employee();
    	$employee->setClientId($this->_getParam('clientId'));
    	$employeeId = $employees->addEmployee($employee);
    }
    
    public function populateemployeesAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	//aktualizovat employeeList
    	$employees = new Application_Model_DbTable_Employee();
    	$this->_employeeList = $employees->getEmployees($this->_clientId);
    	echo Zend_Json::encode($this->_employeeList);
    }
    
    public function newenvironmentfactorAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newenvironmentfactor', 'html')->initContext();
    	
    	$id = $this->_getParam('id_environment_factor', null);
    	
    	$element = new My_Form_Element_EnvironmentFactor("newEnvironmentFactor$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_environmentFactorList);
    	$element->setAttrib('multiOptions2', $this->_categoryList);
    	$element->setAttrib('multiOptions3', $this->_yesNoList);
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
    		$form = new Application_Form_Position(array('workplaceList' => $this->_workplaceList));
    	}
    	return $form;
    }
	
    private function fillMultiselects($form){
    	if($form->employeeList != null){
    		$form->employeeList->setMultiOptions($this->_employeeList);
    	}
    	if($form->environment_factor != null){
    		$form->environment_factor->setAttrib('multiOptions', $this->_environmentFactorList);
    		$form->environment_factor->setAttrib('multiOptions2', $this->_categoryList);
    		$form->environment_factor->setAttrib('multiOptions3', $this->_yesNoList);
    		$form->environment_factor->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	}
    	if($form->schooling != null){
    		$form->schooling->setAttrib('multiOptions', $this->_schoolingList);
    		$form->schooling->setAttrib('canViewPrivate', $this->_canViewPrivate);
    		$form->schooling->setValue(array('id_schooling' => '',
    				'schooling' => '1',
    				'note' => '',
    				'private' => ''));
    	}
    	if($form->schooling2 != null){
    		$form->schooling2->setAttrib('multiOptions', $this->_schoolingList);
    		$form->schooling2->setAttrib('canViewPrivate', $this->_canViewPrivate);
    		$form->schooling2->setValue(array('id_schooling' => '',
    				'schooling' => '2',
    				'note' => '',
    				'private' => ''));
    	}
    	if($form->newSchooling != null){
    		$form->newSchooling->setAttrib('canViewPrivate', $this->_canViewPrivate);
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
    
}