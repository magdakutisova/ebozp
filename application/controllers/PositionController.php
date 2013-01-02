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
    		$form->subsidiary_id->setMultiOptions ( $formContent );
    	}
    	$form->subsidiary_id->setValue($subsidiaryId);
    	
    	$form = $this->fillMultiselects($form);
    	
    	$form->save->setLabel('Uložit');
    	
    	$form->preValidation($this->getRequest()->getPost(), $this->_yesNoList, $this->_sexList, $this->_yearOfBirthList,
    			$this->_canViewPrivate, $this->_employeeList);
    	
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
    		$form->populate($this->getRequest()->getPost());
    		$this->view->form = $form;
    		return;
    	}
    	
    	//TODO odtud dále
    	
    	$this->view->form = $form;
    }
    
    public function newemployeeAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newemployee', 'html')->initContext();
    	
    	$id = $this->_getParam('id_employee', null);
    	
    	$element = new My_Form_Element_Employee("newEmployee$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_yesNoList);
    	$element->setAttrib('multiOptions2', $this->_sexList);
    	$element->setAttrib('multiOptions3', $this->_yearOfBirthList);
    	$element->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newcurrentemployeeAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newcurrentemployee', 'html')->initContext();
    	
    	$id = $this->_getParam('id_current_employee', null);
    	
    	$element = new My_Form_Element_CurrentEmployee("newCurrentEmployee$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_employeeList);
    	
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
    	if($form->employee != null){
    		$form->employee->setAttrib('multiOptions', $this->_yesNoList);
    		$form->employee->setAttrib('multiOptions2', $this->_sexList);
    		$form->employee->setAttrib('multiOptions3', $this->_yearOfBirthList);
    		$form->employee->setAttrib('canViewPrivate', $this->_canViewPrivate);
    	}
    	if($form->current_employee != null){
    		$form->current_employee->setAttrib('multiOptions', $this->_employeeList);
    	}
    	
    	return $form;
    }
    
}