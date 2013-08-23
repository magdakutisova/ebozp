<?php
class EmployeeController extends Zend_Controller_Action{
	
	private $_clientId = null;
	private $_user = null;
	private $_acl = null;
	private $_username = null;
	
	public function init(){
		//globální nastavení view
		$this->view->title = 'Zaměstnanci';
		$this->view->headTitle($this->view->title);
		$this->_helper->layout()->setLayout('clientLayout');
		
		$this->_acl = new My_Controller_Helper_Acl();
		$this->_clientId = $this->getRequest()->getParam('clientId');
		
		//přístupová práva
		$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
		$users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
	}
	
	public function listAction(){
		$clients = new Application_Model_DbTable_Client();
		$client = $clients->getClient($this->_clientId);
		
		$this->view->subtitle = "Databáze zaměstnanců - " . $client->getCompanyName();
		$this->view->clientId = $this->_clientId;
		
		//výběr poboček
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		$formContent = $subsidiaries->getSubsidiaries($this->_clientId, 0, 1);
		
		if($formContent != 0){
			$formContent = $this->filterSubsidiarySelect($formContent);
		}
		
		$subsidiaryId = null;
		
		if($formContent != 0){
			$subsidiaryId = $this->initSubsidiarySwitch($formContent, $subsidiaryId);
		}
		else{
			$selectForm = '<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>';
			$this->view->selectForm = $selectForm;
		}
		
		if($subsidiaryId != null){
			if(!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($subsidiaryId))){
				$this->_helper->redirector->gotoSimple('denied', 'error');
			}
			
			//vypisování zaměstnanců
			$employeeDb = new Application_Model_DbTable_Employee();
			$unassignedEmployees = $employeeDb->getUnassignedEmployees($this->_clientId);
			$this->view->unassignedEmployees = $unassignedEmployees;
			$employees = $employeeDb->getBySubsidiaryAndPositions($subsidiaryId);
			$this->view->employees = $employees;
		}
	}
	
	public function editAction(){
		$this->view->subtitle = 'Editace zaměstnanců';
		
		$form = new Application_Form_Employee();
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				);
		
		$form->removeElement('save_employee');
		$form->addElement('submit', 'save_employee', array(
				'label' => 'Uložit zaměstnance',
				'decorators' => $elementDecorator,
				));
		
		//získání seznamu ano/ne
		$yesNoList = array();
		$yesNoList[0] = 'Ne';
		$yesNoList[1] = 'Ano';
		 
		//získání seznamu pohlaví
		$sexList = array();
		$sexList[0] = 'Muž';
		$sexList[1] = 'Žena';
		 
		//získání seznamu roků narození
		$yearOfBirthList = array();
		for ($i=1920; $i<=date('Y'); $i++){
			$yearOfBirthList[$i] = $i;
		}
		
		$form->year_of_birth->setMultiOptions($yearOfBirthList);
		$form->manager->setMultiOptions($yesNoList);
		$form->sex->setMultiOptions($sexList);
		
		$this->view->form = $form;
		
		$employees = new Application_Model_DbTable_Employee();
		$employee = $employees->getEmployee($this->getParam('employeeId'));
		
		$form->populate($employee->toArray());
		
		if($this->getRequest()->isPost()){
			$formData = $this->getRequest()->getPost();
			if($form->isValid($formData)){
				$employee = new Application_Model_Employee($formData);
				if($employee->getPositionId() == ''){
					$employee->setPositionId(null);
				}
				$employees->updateEmployee($employee);
				$this->_helper->FlashMessenger('Zaměstnanec ' . $employee->getFirstName() . ' ' . $employee->getSurname() . ' byl upraven.');
				$this->_helper->redirector->gotoRoute(array('clientId' => $this->getParam('clientId'), 'subsidiaryId' => $this->getParam('subsidiaryId')), 'employeeList');
			}
		}
	}
	
	public function deleteAction(){
		if($this->getRequest()->getMethod() == 'POST'){
			$clientId = $this->getParam('clientId');
			$subsidiaryId = $this->getParam('subsidiaryId');
			$employeeId = $this->getParam('employeeId');
			
			$employees = new Application_Model_DbTable_Employee();
			$employees->deleteEmployee($employeeId);
			
			$this->_helper->FlashMessenger('Zaměstnanec byl vymazán.');
			
			$this->_helper->redirector->gotoRoute(array('clientId' => $clientId, 'subsidiaryId' => $subsidiaryId), 'employeeList');
		}
		else{
			throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání zaměstnance.', 500);
		}
	}
	
	private function filterSubsidiarySelect($formContent){
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		foreach($formContent as $key => $subsidiary){
			if(!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
				unset($formContent[$key]);
			}
		}
		return $formContent;
	}
	
	private function initSubsidiarySwitch($formContent, $subsidiaryId){
		$selectForm = new Application_Form_Select();
		$selectForm->select->setMultiOptions($formContent);
		$selectForm->select->setLabel('Vyberte pobočku:');
		$selectForm->submit->setLabel('Vybrat');
		$this->view->selectForm = $selectForm;
		$subsidiaryId = array_shift(array_keys($formContent));
		
		if($this->getRequest()->isPost() && in_array('Vybrat', $this->getRequest()->getPost())){
			$formData = $this->getRequest()->getPost();
			$subsidiaryId = $formData['select'];
			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'employeeList');
		}
		else{
			$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
			$selectForm->select->setValue($subsidiaryId);
		}
		$this->view->subsidiaryId = $subsidiaryId;
		return $subsidiaryId;
	}
	
}