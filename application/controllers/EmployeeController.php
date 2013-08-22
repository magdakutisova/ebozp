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
			$employees = $employeeDb->getBySubsidiaryAndPosition($subsidiaryId);
			$this->view->employees = $employees;
		}
	}
	
	public function editAction(){
		$this->view->subtitle('Editace zaměstnanců');
		
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
		$this->view->form = $form;
		
		$employees = new Application_Model_DbTable_Employee();
		$employee = $employees->getEmployee($this->getParam('employeeId'));
		
		$form->populate($employee->toArray());
		
		if($this->getRequest()->isPost()){
			$formData = $this->getRequest()->getPost();
			if($form->isValid($formData)){
				$employee = new Application_Model_Employee($formData);
				$employees->updateEmployee($employee);
				$this->_helper->FlashMessenger('Zaměstnanec ' . $employee->getFirstName() . ' ' . $employee->getSurname() . ' byl upraven.');
				$this->_helper->redirector->gotoRoute(array('clientId' => $this->getParam('clientId'), 'subsidiaryId' => $this->getParam('subsidiaryId')), 'employeeList');
			}
		}
	}
	
	//TODO DELETE
	
}