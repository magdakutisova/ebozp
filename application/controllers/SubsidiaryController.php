<?php

class SubsidiaryController extends Zend_Controller_Action {
	private $_username;
	private $_user;
	private $_acl;
	private $_canViewHeadquarters = false;
	
	public function init() {
		$this->view->title = 'Správa poboček';
		$this->view->headTitle ( $this->view->title );
		$this->_helper->layout()->setLayout('clientLayout');
		
		if(Zend_Auth::getInstance()->hasIdentity()){
			$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
		}
		
		$action = $this->getRequest()->getActionName();
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
		
		if($this->_acl->isAllowed($this->_user, $subsidiaries->getHeadquarters($this->_getParam('clientId')))){
				$this->_canViewHeadquarters = true;
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
		$subsidiary = $subsidiaries->getSubsidiary($subsidiaryId);
		if($subsidiary->getHq()){
			$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'clientIndex');
		}
		
		$this->view->subtitle = $client->getCompanyName();
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
		$this->view->canViewHeadquarters = $this->_canViewHeadquarters;
		
		$userSubs = new Application_Model_DbTable_UserHasSubsidiary(); 
		$this->view->technicians = $userSubs->getByRoleAndSubsidiary(My_Role::ROLE_TECHNICIAN, $subsidiary->getIdSubsidiary());
		
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
		$this->view->form = $form;
		
		$clientId = $this->_getParam ( 'clientId' );
		
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
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$adapter = $subsidiaries->getAdapter();
				
				try{
					//zahájení transakce
					$adapter->beginTransaction();
					
					$subsidiaryId = $subsidiaries->addSubsidiary ( $subsidiary);
					
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
			
		$form = new Application_Form_Subsidiary ();
		$form->save->setLabel ( 'Uložit' );
		$form->removeElement ( 'other' );
		$this->view->form = $form;
		
		$subsidiaryId = $this->_getParam ( 'subsidiary' );
		$clientId = $this->_getParam ( 'clientId' );
		
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
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$adapter = $subsidiaries->getAdapter();
				
				try{
					//zahájení transakce
					$adapter->beginTransaction();
					
					$subsidiaries->updateSubsidiary ( $subsidiary, true);
					
					$this->_helper->diaryRecord($this->_username, 'upravil pobočku', array ('clientId' => $clientId, 'subsidiary' => $subsidiaryId ), 'subsidiaryIndex', $subsidiary->getSubsidiaryName() . ', ' . $subsidiary->getSubsidiaryTown(), $subsidiaryId);
					
					//uložení transakce
					$adapter->commit();
					
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
					$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'subsidiaryNew');
				}
			}
		} else {
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			$subsidiary = $subsidiaries->getSubsidiary ( $subsidiaryId );
					
			$form->populate ( $subsidiary->toArray() );
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

}





