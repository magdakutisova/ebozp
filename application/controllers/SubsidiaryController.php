<?php

class SubsidiaryController extends Zend_Controller_Action {
	private $_username;
	
	public function init() {
		$this->view->title = 'Správa poboček';
		$this->view->headTitle ( $this->view->title );
		$this->_helper->layout()->setLayout('clientLayout');
		
		if(Zend_Auth::getInstance()->hasIdentity()){
			$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
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
		
		$this->view->subtitle = $client['company_name'];
		$this->view->client = $client;
		$this->view->subsidiary = $subsidiary;
		
		$diary = new Application_Model_DbTable_Diary();

		$this->view->records = $diary->getDiaryBySubsidiary($subsidiaryId);
		
		$defaultNamespace = new Zend_Session_Namespace();
		$defaultNamespace->referer = $this->_request->getPathInfo();		
	}
	
	public function newAction() {
		$this->view->subtitle = 'Nová pobočka';
		
		$form = new Application_Form_Subsidiary ();
		$form->save->setLabel ( 'Přidat' );
		$this->view->form = $form;
		
		$clientId = $this->_getParam ( 'clientId' );
		
		$clients = new Application_Model_DbTable_Client ();
		
		$client = $clients->getClient ( $clientId );
		
		// naplnění formuláře daty ze session, pokud existují
		$defaultNamespace = new Zend_Session_Namespace ();
		if (isset ( $defaultNamespace->formData )) {
			$form->populate ( $defaultNamespace->formData );
			unset ( $defaultNamespace->formData );
		}
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$subsidiaryName = $form->getValue ( 'subsidiary_name' );
				$subsidiaryStreet = $form->getValue ( 'subsidiary_street' );
				$subsidiaryCode = $form->getValue ( 'subsidiary_code' );
				$subsidiaryTown = $form->getValue ( 'subsidiary_town' );
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$supervisionFrequency = $form->getValue ( 'supervision_frequency' );
				$doctor = $form->getValue('doctor');
				$private = $form->getValue ( 'private' );
				
				if ($subsidiaryName == null) {
					$subsidiaryName = $clients->getCompanyName ( $clientId );
				}
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaryId = $subsidiaries->addSubsidiary ( $subsidiaryName, $subsidiaryStreet,
					$subsidiaryCode, $subsidiaryTown, $contactPerson, $phone, $email,
					$supervisionFrequency, $doctor,	$clientId, $private, 0 );
				
				$this->_helper->diaryRecord($this->_username, 'přidal novou pobočku', array ('clientId' => $clientId, 'subsidiary' => $subsidiaryId ), 'subsidiaryIndex', $subsidiaryName . ', ' . $subsidiaryTown, $subsidiaryId);
				
				$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiaryName . ', ' . $subsidiaryTown . '</strong> přidána' );
				if ($form->getElement ( 'other' )->isChecked ()) {
					$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'subsidiaryNew' );
				} else {
					$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
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
				$subsidiaryName = $form->getValue ( 'subsidiary_name' );
				$subsidiaryStreet = $form->getValue ( 'subsidiary_street' );
				$subsidiaryCode = $form->getValue ( 'subsidiary_code' );
				$subsidiaryTown = $form->getValue ( 'subsidiary_town' );
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$supervisionFrequency = $form->getValue ( 'supervision_frequency' );
				$doctor = $form->getValue('doctor');
				$private = $form->getValue ( 'private' );
				
				if ($subsidiaryName == null) {
					$clients = new Application_Model_DbTable_Client ();
					$subsidiaryName = $clients->getCompanyName ( $clientId );
				}
				
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaries->updateSubsidiary ( $subsidiaryId, $subsidiaryName, $subsidiaryStreet,
					$subsidiaryCode, $subsidiaryTown, $contactPerson, $phone, $email,
					$supervisionFrequency, $doctor, $clientId, $private, 0 );

				$this->_helper->diaryRecord($this->_username, 'upravil pobočku', array ('clientId' => $clientId, 'subsidiary' => $subsidiaryId ), 'subsidiaryIndex', $subsidiaryName . ', ' . $subsidiaryTown, $subsidiaryId);
				
				$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiaryName . ', ' . $subsidiaryTown . '</strong> upravena' );
				
				$defaultNamespace = new Zend_Session_Namespace();
				if (isset($defaultNamespace->referer)){
					$path = $defaultNamespace->referer;
					unset($defaultNamespace->referer);
					$this->_redirect($path);
				}
				else{
					
				}
				//$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
			}
		} else {
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			$subsidiary = $subsidiaries->getSubsidiary ( $subsidiaryId );
					
			$form->populate ( $subsidiary );
		}
	}
	
	public function deleteAction() {
		if ($this->getRequest ()->getMethod () == 'POST') {
			$clientId = $this->_getParam ( 'clientId' );
			$subsidiaryId = $this->_getParam ( 'subsidiary' );
			
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			
			$subsidiary = $subsidiaries->getSubsidiary ( $subsidiaryId );
			$subsidiaryName = $subsidiary ['subsidiary_name'];
			$subsidiaryTown = $subsidiary ['subsidiary_town'];
			
			$subsidiaries->deleteSubsidiary ( $subsidiaryId );
			
			$this->_helper->diaryRecord($this->_username, 'smazal pobočku', null, null, $subsidiaryName . ', ' . $subsidiaryTown, $subsidiaryId);
			
			$this->_helper->FlashMessenger ( 'Pobočka <strong>' . $subsidiaryName . ', ' . $subsidiaryTown . '</strong> smazána' );
			$this->_helper->redirector->gotoRoute (array ('clientId' => $clientId ), 'clientAdmin' );
		} else {
			throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání pobočky.', 500 );
		}
	}

}





