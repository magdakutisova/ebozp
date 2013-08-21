<?php
class TechnicalController extends Zend_Controller_Action{
	
	private $_clientId = null;
	private $_user = null;
	private $_acl = null;
	private $_username = null;
	
	public function init(){
		//globální nastavení view
		$this->view->title = 'Technické prostředky';
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
		
		$this->view->subtitle = "Databáze technických prostředků - " . $client->getCompanyName();
		$this->view->clientId = $this->_clientId;
		$filter = $this->getRequest()->getParam('filter');
		$this->view->filter = $filter;
		
		$defaultNamespace = new Zend_Session_Namespace();
		$defaultNamespace->refererTechnical = $this->_request->getPathInfo();
		
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
			
			//vypisování technických prostředků
			$technicalDb = new Application_Model_DbTable_TechnicalDevice();
			$technicalDevices = null;
			if($filter == 'podle-pracovist'){
				$technicalDevices = $technicalDb->getBySubsidiaryWithPositions($subsidiaryId);
			}
			if($filter == 'podle-pracovnich-pozic'){
				$technicalDevices = $technicalDb->getBySubsidiaryWithWorkplaces($subsidiaryId);
			}
			$this->view->technicalDevices = $technicalDevices;
		}
	}
	
	public function editAction(){
		$this->view->subtitle = 'Editace technických prostředků';
		
		$form = new Application_Form_TechnicalDevice();
		
		$elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				);
		
		$form->removeElement('save_technical_device');
		$form->addElement('submit', 'save_technicaldevice', array(
				'label' => 'Uložit technický prostředek',
				'decorators' => $elementDecorator,
				));
		$this->view->form = $form;
		
		$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
		$technicalDevice = $technicalDevices->getTechnicalDevice($this->getParam('technicalDeviceId'));
		
		$form->populate($technicalDevice->toArray());
		
		if($this->getRequest()->isPost()){
			$formData = $this->getRequest()->getPost();
			if($form->isValid($formData)){
				$technicalDevice = new Application_Model_TechnicalDevice($formData);
				$technicalDevices->updateTechnicalDevice($technicalDevice);
				$this->_helper->FlashMessenger('Technický prostředek ' . $technicalDevice->getSort() . ' ' . $technicalDevice->getType() . ' byl upraven.');
				
				$defaultNamespace = new Zend_Session_Namespace();
				if(isset($defaultNamespace->refererTechnical)){
					$path = $defaultNamespace->refererTechnical;
					unset($defaultNamespace->refererTechnical);
					$this->_redirect($path);
				}
				else{
					$this->_helper->redirector->gotoRoute(array('clientId' => $this->getParam('clientId'), 'subsidiaryId' => $this->getParam('subsidiaryId'), 'filter' => 'podle-pracovist'), 'technicalList');
				}
			}
		}
	}
	
	public function deleteAction(){
		if($this->getRequest()->getMethod() == 'POST'){
			$clientId = $this->_getParam('clientId');
			$subsidiaryId = $this->getParam('subsidiaryId');
			$technicalDeviceId = $this->getParam('technicalDeviceId');
			
			$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
			$technicalDevices->deleteTechnicalDevice($technicalDeviceId);
			
			$this->_helper->FlashMessenger('Technický prostředek byl vymazán.');
			
			$defaultNamespace = new Zend_Session_Namespace();
			if(isset($defaultNamespace->refererTechnical)){
				$path = $defaultNamespace->refererTechnical;
				unset($defaultNamespace->refererTechnical);
				$this->_redirect($path);
			}
			else{
				$this->_helper->redirector->gotoRoute(array('clientId' => $this->getParam('clientId'), 'subsidiaryId' => $this->getParam('subsidiaryId'), 'filter' => 'podle-pracovist'), 'technicalDelete');
			}
		}
		else{
			throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání technického prostředku.', 500);
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
			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workList');
		}
		else{
			$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
			$selectForm->select->setValue($subsidiaryId);
		}
		$this->view->subsidiaryId = $subsidiaryId;
		return $subsidiaryId;
	}
	
}