<?php

class ClientController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Guardian';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {
        // action body
    }

    public function searchAction()
    {
        // action body
    }

    public function listAction()
    {
        $clients = new Application_Model_DbTable_Client();
        $this->view->clients = $clients->fetchAll();
    }

    public function newAction()
    {
        $form = new Application_Form_Client();
        $form->save->setLabel('Přidat');
        $this->view->form = $form;
        //TODO ošetření proti vložení stejného klienta dvakrát
        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		$companyName = $form->getValue('companyName');
        		$invoiceAddress = $form->getValue('invoiceAddress');
        		$companyNumber = $form->getValue('companyNumber');
        		$taxNumber = $form->getValue('taxNumber');
        		$headquartersAddress = $form->getValue('headquartersAddress');
        		$business = $form->getValue('business');
        		$contactPerson = $form->getValue('contactPerson');
        		$phone = $form->getValue('phone');
        		$email = $form->getValue('email');
        		$private = $form->getValue('private');
        		
        		//přidání klienta
        		$clients = new Application_Model_DbTable_Client();
        		$clientId = $clients->addClient($companyName, $invoiceAddress, $companyNumber,
        			$taxNumber, $headquartersAddress, $business, $private);
        		
        		//přidání pobočky
        		$subsidiaries = new Application_Model_DbTable_Subsidiary();
        		$subsidiaries->addSubsidiary($companyName, $headquartersAddress, $contactPerson,
        			$phone, $email, null, $clientId, $private, true);
        		        		
        		//TODO zápis do bezpečnostního deníku
        		//TODO redirect na nově vytvořeného klienta
        		//TODO flashmessenger
        		$this->_helper->FlashMessenger('Klient ' . $companyName . ' přidán');
        		$this->_helper->redirector->gotoRoute(array(), 'clientList');
        	}
        	else {
        		$form->populate($formData);
        	}
        }
    }


}







