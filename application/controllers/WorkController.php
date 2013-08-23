<?php

class WorkController extends Zend_Controller_Action
{
	
	private $_clientId = null;
	private $_user = null;
	private $_acl = null;
	private $_username = null;
	private $_canEditWork;
	private $_canDeleteWork;

    public function init()
    {
    	//globální nastavení view
    	$this->view->title = 'Pracovní činnosti';
    	$this->view->headTitle($this->view->title);
    	$this->_helper->layout()->setLayout('clientLayout');
    	 
        $this->_acl = new My_Controller_Helper_Acl();
    	$this->_clientId = $this->getRequest()->getParam('clientId');
    	
    	//přístupová práva
    	$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
    	$users = new Application_Model_DbTable_User();
    	$this->_user = $users->getByUsername($this->_username);
    	
    	/* $this->_canEditWork = $this->_acl->isAllowed($this->_user, 'work', 'edit');
    	$this->view->canEditWork = $this->_canEditWork;
    	$this->_canDeleteWork = $this->_acl->isAllowed($this->_user, 'work', 'delete');
    	$this->view->canDeleteWork = $this->_canDeleteWork; */
    }
    
    public function editAction(){
    	$this->view->subtitle = 'Editace pracovní činnosti';
    	
    	$form = new Application_Form_Work();
    	
    	$elementDecorator = array(
    			'ViewHelper',
    			array('Errors'),
    			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	);
    	
    	$form->removeElement('save_work');
    	$form->addElement('submit', 'save_work', array(
    			'label' => 'Uložit pracovní činnost',
    			'decorators' => $elementDecorator,
    			));
    	$this->view->form = $form;
    	
    	$works = new Application_Model_DbTable_Work();
    	$work = $works->getWork($this->_getParam('workId'));
    	
    	$form->populate($work->toArray());
    	
    	if($this->getRequest()->isPost()){
    		$formData = $this->getRequest()->getPost();
    		if($form->isValid($formData)){
    			$work = new Application_Model_Work($formData);
    			$works->updateWorkAtClient($work, $this->_clientId);
    			$this->_helper->FlashMessenger('Pracovní činnost ' . $work->getWork() . ' byla upravena.');
    			
    			$defaultNamespace = new Zend_Session_Namespace();
    			if(isset($defaultNamespace->refererWork)){
    				$path = $defaultNamespace->refererWork;
    				unset($defaultNamespace->refererWork);
    				$this->_redirect($path);
    			}
    			else{
    				$this->_helper->redirector->gotoRoute(array('clientId' => $this->_getParam('clientId'), 'subsidiaryId' => $this->_getParam('subsidiaryId'), 'filter' => 'podle-pracovist'), 'workList');
    			}
    		}
    	}
    }
    
    public function deleteAction(){
    	if($this->getRequest()->getMethod() == 'POST'){
    		$clientId = $this->_getParam('clientId');
    		$subsidiaryId = $this->_getParam('subsidiaryId');
    		$workId = $this->_getParam('workId');
    		
    		$works = new Application_Model_DbTable_Work();
    		$works->deleteWorkFromClient($workId, $this->_clientId);
    		
    		$this->_helper->FlashMessenger('Pracovní činnost byla vymazána');
    		
    		$defaultNamespace = new Zend_Session_Namespace();
    		if(isset($defaultNamespace->refererWork)){
    			$path = $defaultNamespace->refererWork;
    			unset($defaultNamespace->refererWork);
    			$this->_redirect($path);
    		}
    		else{
    			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_getParam('clientId'), 'subsidiaryId' => $this->_getParam('subsidiaryId'), 'filter' => 'podle-pracovist'), 'workList');
    		}
    	}
    	else{
    		throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání pracovní činnosti.', 500);
    	}
    }

    public function listAction(){
    	$clients = new Application_Model_DbTable_Client();
    	$client = $clients->getClient($this->_clientId);
    	
    	$this->view->subtitle = "Databáze pracovních činností - " . $client->getCompanyName();
    	$this->view->clientId = $this->_clientId;
    	$filter = $this->getRequest()->getParam('filter');
    	$this->view->filter = $filter;
    	
    	$defaultNamespace = new Zend_Session_Namespace();
    	$defaultNamespace->refererWork = $this->_request->getPathInfo();
    	
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
    		
    		//vypisování pracovních činností
    		$workDb = new Application_Model_DbTable_Work();
    		$works = null;
    		if($filter == 'podle-pracovist'){
    			$works = $workDb->getBySubsidiaryWithPositions($subsidiaryId);
    		}
    		if($filter == 'podle-pracovnich-pozic'){
    			$works = $workDb->getBySubsidiaryWithWorkplaces($subsidiaryId);
    		}
    		$this->view->works = $works;
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
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->_getParam('filter')), 'workList');
    	}
    	else{
    		$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    		$selectForm->select->setValue($subsidiaryId);
    	}
    	$this->view->subsidiaryId = $subsidiaryId;
    	return $subsidiaryId;
    }
}