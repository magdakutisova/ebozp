<?php

class SubsidiaryController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Správa poboček';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {
        // action body
    }

    public function newAction()
    {
        $this->view->subtitle = 'Nová pobočka';
        
        $form = new Application_Form_Subsidiary();
        $form->save->setLabel('Přidat');
        $this->view->form = $form;
        
        $clientId = $this->_getParam('clientId');
        
    	// naplnění formuláře daty ze session, pokud existují
        $defaultNamespace = new Zend_Session_Namespace();
        if(isset($defaultNamespace->formData)){
        	$form->populate($defaultNamespace->formData);
        	unset($defaultNamespace->formData);
        }
        
        //TODO jak ošetřit přidání stejné pobočky dvakrát
        if($this->getRequest()->isPost()) {
        	$formData = $this->getRequest()->getPost();
        	if($form->isValid($formData)){
        		$subsidiaryName = $form->getValue('subsidiary_name');
        		$subsidiaryAddress = $form->getValue('subsidiary_address');
        		$invoiceAddress = $form->getValue('invoice_address');
        		$contactPerson = $form->getValue('contact_person');
        		$phone = $form->getValue('phone');
        		$email = $form->getValue('email');
        		$supervisionFrequency = $form->getValue('supervision_frequency');
        		$private = $form->getValue('private');
        		//TODO upravit pobočky tak, aby šlo pak filtrovat podle města - dohodnout se podle kterého atd.
        		if ($subsidiaryName == null){
        			$clients = new Application_Model_DbTable_Client();
        			$subsidiaryName = $clients->getCompanyName($clientId);
        		}
        		
        		$subsidiaries = new Application_Model_DbTable_Subsidiary();
        		$subsidiaries->addSubsidiary($subsidiaryName, $subsidiaryAddress,
        			$invoiceAddress, $contactPerson, $phone, $email,
        			$supervisionFrequency, $clientId, $private, null);
        			
        		//TODO přidání další pobočky
        			
        		//TODO zápis do bezpečnostního deníku
        		$this->_helper->FlashMessenger('Pobočka <strong>' . $subsidiaryName . ', ' . $subsidiaryAddress . '</strong> přidána');
        		$this->_helper->redirector->gotoRoute(array('clientId' => $clientId), 'clientAdmin');
        	}
        }
    }


}