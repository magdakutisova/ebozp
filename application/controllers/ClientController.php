<?php

class ClientController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->title = 'Správa klientů';
		$this->view->headTitle ( $this->view->title );
		
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$this->_helper->layout->disableLayout ();
			$this->_helper->viewRenderer->setNoRender ( true );
		}
		
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'list', 'html' )->initContext ();
	}
	
	public function indexAction() {
		// action body
	}
	

	public function listAction() {
		$this->view->subtitle = 'Výběr klienta';
		
		$mode = $this->_getParam ( 'mode' );
		
		if ($mode == '' || $mode == 'nazev') {
			$clients = new Application_Model_DbTable_Client ();
			$this->view->clients = $clients->getClients ();
			$this->renderScript ( 'client/list.phtml' );
		} else if ($mode == 'bt') {
			$this->renderScript ( 'client/technician.phtml' );
		} else if ($mode == 'koo') {
			$this->renderScript ( 'client/coordinator.phtml' );
		} else if ($mode == 'obec') {
			$subsidiaries = new Application_Model_DbTable_Subsidiary ();
			$this->view->subsidiaries = $subsidiaries->getByTown ();
			$this->renderScript ( 'client/town.phtml' );
		} else if ($mode == 'naposledy') {
			$clients = new Application_Model_DbTable_Client ();
			$this->view->clients = $clients->getLastOpen ();
			$this->renderScript ( 'client/list.phtml' );
		}
	}
	
	public function newAction() {
		$this->view->subtitle = 'Nový klient';
		
		$form = new Application_Form_Client ();
		$form->save->setLabel ( 'Přidat' );
		$this->view->form = $form;
		
		// naplnění formuláře daty ze session, pokud existují
		$defaultNamespace = new Zend_Session_Namespace ();
		if (isset ( $defaultNamespace->formData )) {
			$form->populate ( $defaultNamespace->formData );
			unset ( $defaultNamespace->formData );
		}
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$companyName = $form->getValue ( 'company_name' );
				$invoiceStreet = $form->getValue ( 'invoice_street' );
				$invoiceCode = $form->getValue ( 'invoice_code' );
				$invoiceTown = $form->getValue ( 'invoice_town' );
				$companyNumber = $form->getValue ( 'company_number' );
				$taxNumber = $form->getValue ( 'tax_number' );
				$headquartersStreet = $form->getValue ( 'headquarters_street' );
				$headquartersCode = $form->getValue ( 'headquarters_code' );
				$headquartersTown = $form->getValue ( 'headquarters_town' );
				$business = $form->getValue ( 'business' );
				$insuranceCompany = $form->getValue ('insurance_company');
				$doctor = $form->getValue('doctor');
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$private = $form->getValue ( 'private' );
				
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $companyNumber )) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientNew' );
				}
				
				//přidání klienta
				$clientId = $clients->addClient ( $companyName, $companyNumber, $taxNumber,
					$headquartersStreet, $headquartersCode, $headquartersTown, $business,
					$insuranceCompany, $private );
				
				//přidání pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaryId = $subsidiaries->addSubsidiary ( $companyName, $headquartersStreet,
					$headquartersCode, $headquartersTown, $invoiceStreet, $invoiceCode, $invoiceTown,
					$contactPerson, $phone, $email, null, $doctor, $clientId, $private, true );
				
				//TODO dát k zápisu uživatele
				

				$diary = new Application_Model_DbTable_Diary ();
				$username = 'admin';
				$diary->addMessage ( $username . ' přidal nového klienta <a href="' . $this->_helper->url->url ( array ('clientId' => $clientId ), 'clientAdmin' ) . '">' . $companyName . "</a>.", $subsidiaryId, $username );
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> přidán' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
			} else {
				$form->populate ( $formData );
			}
		}
	
	}
	
	public function adminAction() {
		$this->view->subtitle = 'Administrace klienta';
		
		$clientId = $this->_getParam ( 'clientId' );
		
		$clients = new Application_Model_DbTable_Client ();
		$clients->openClient ( $clientId );
		$client = $clients->getClient ( $clientId );
		
		if ($client ['deleted']) {
			throw new Zend_Controller_Action_Exception ( 'Klient neexistuje.', 404 );
		}
		
		$this->view->companyName = $client ['company_name'];
		$this->view->clientId = $clientId;
		
		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $clientId );
		
		if ($formContent != 0) {
			$form = new Application_Form_SubsidiaryList ();
			$form->subsidiary->setMultiOptions ( $formContent );
			$this->view->form = $form;
			
			if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if ($form->isValid ( $formData )) {
					$subsidiary = $this->getRequest ()->getParam ( 'subsidiary' );
					if (isSet ( $formData ['edit'] )) {
						$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId, 'subsidiary' => $subsidiary ), 'subsidiaryEdit' );
					}
					if (isSet ( $formData ['delete'] )) {
						//jen forward kvůli metodě POST
						$this->_forward ( 'delete', 'subsidiary' );
					}
				}
			}
		}
	
	}
	
	public function editAction() {
		$this->view->subtitle = 'Editace základních údajů klienta';
		
		$form = new Application_Form_Client ();
		$form->save->setLabel ( 'Uložit' );
		$this->view->form = $form;
		
		$defaultNamespace = new Zend_Session_Namespace ();
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$clientId = $form->getValue ( 'id_client' );
				$subsidiaryId = $form->getValue ( 'id_subsidiary' );
				$companyName = $form->getValue ( 'company_name' );
				$invoiceStreet = $form->getValue ( 'invoice_street' );
				$invoiceCode = $form->getValue ( 'invoice_code' );
				$invoiceTown = $form->getValue ( 'invoice_town' );
				$companyNumber = $form->getValue ( 'company_number' );
				$taxNumber = $form->getValue ( 'tax_number' );
				$headquartersStreet = $form->getValue ( 'headquarters_street' );
				$headquartersCode = $form->getValue ( 'headquarters_code' );
				$headquartersTown = $form->getValue ( 'headquarters_town' );
				$business = $form->getValue ( 'business' );
				$insuranceCompany = $form->getValue ('insurance_company');
				$doctor = $form->getValue('doctor');
				$contactPerson = $form->getValue ( 'contact_person' );
				$phone = $form->getValue ( 'phone' );
				$email = $form->getValue ( 'email' );
				$private = $form->getValue ( 'private' );
				
				$clients = new Application_Model_DbTable_Client ();
				
				//kontrola IČO
				if ($clients->existsCompanyNumber ( $companyNumber ) && ($clients->getCompanyNumber ( $clientId ) != $companyNumber)) {
					$defaultNamespace->formData = $formData;
					$this->_helper->FlashMessenger ( 'Chyba! Klient s tímto IČO již existuje.' );
					$this->_helper->redirector->gotoRoute ( array (), 'clientEdit' );
				}
				
				//update klienta
				$clients->updateClient ( $clientId, $companyName, $companyNumber, $taxNumber,
					$headquartersStreet, $headquartersCode, $headquartersTown, $business,
					$insuranceCompany, $private );
				
				//update pobočky
				$subsidiaries = new Application_Model_DbTable_Subsidiary ();
				$subsidiaries->updateSubsidiary ( $subsidiaryId, $companyName, $headquartersStreet,
					$headquartersCode, $headquartersTown, $invoiceStreet, $invoiceCode, $invoiceTown,
					$contactPerson, $phone, $email, null, $doctor, $clientId, $private, true );
				
				//TODO dát k zápisu uživatele				
				

				$diary = new Application_Model_DbTable_Diary ();
				$username = 'admin';
				$diary->addMessage ( $username . ' upravil klienta <a href="' . $this->_helper->url->url ( array ('clientId' => $clientId ), 'clientAdmin' ) . '">' . $companyName . "</a>.", $subsidiaryId, $username );
				
				$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> upraven' );
				$this->_helper->redirector->gotoRoute ( array ('clientId' => $clientId ), 'clientAdmin' );
			}
		
		} else {
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			} else {
				$clientId = $this->_getParam ( 'clientId' );
				$clients = new Application_Model_DbTable_Client ();
				
				$client = $clients->getClient ( $clientId );
				
				if ($client ['deleted']) {
					throw new Zend_Controller_Action_Exception ( 'Klient neexistuje.', 404 );
				}
				
				$form->populate ( $clients->getHeadquarters ( $clientId ) );
			}
		}
	}
	
	public function deleteAction() {
		if ($this->getRequest ()->getMethod () == 'POST') {
			$clientId = $this->_getParam ( 'clientId' );
			
			$clients = new Application_Model_DbTable_Client ();
			$client = $clients->getHeadquarters ( $clientId );
			$companyName = $client ['company_name'];
			$subsidiaryId = $client ['id_subsidiary'];
			$clients->deleteClient ( $clientId );
			
			//TODO dát k zápisu uživatele				
			

			$diary = new Application_Model_DbTable_Diary ();
			$username = 'admin';
			$diary->addMessage ( $username . ' smazal klienta ' . $companyName . ".", $subsidiaryId, $username );
			
			$this->_helper->FlashMessenger ( 'Klient <strong>' . $companyName . '</strong> smazán' );
			$this->_helper->redirector->gotoRoute ( array (), 'clientList' );
		
		} else {
			throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání klienta.', 500 );
		}
	
	}

}




