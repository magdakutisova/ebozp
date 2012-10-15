<?php

class WorkplaceController extends Zend_Controller_Action
{

    private $_client = null;

    private $_clientId = null;

    private $_acl = null;

    private $_user = null;

    private $_username = null;

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        $this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
        
        //získání odkazu na centrálu
        $action = $this->getRequest()->getActionName();
        $this->_acl = new My_Controller_Helper_Acl();
        if ($action != 'newfactor' && $action != 'newrisk'){
        	$this->_clientId = $this->getRequest()->getParam('clientId');
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	$this->_client = $subsidiaries->getHeadquarters($this->_clientId);
        	
        	//přístupová práva
        	$this->_username = Zend_Auth::getInstance()->getIdentity()->username;
        	$users = new Application_Model_DbTable_User();
			$this->_user = $users->getByUsername($this->_username);

			//do new může jen ten, kdo má přístup k centrále
			if ($action == 'new'){
				if(!$this->_acl->isAllowed($this->_user, $this->_client)){
					$this->_helper->redirector('denied', 'error');
				}
			}
			
			//soukromá poznámka
			$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
        }
        
    }

    public function indexAction()
    {
        // action body
    }

    public function newAction()
    {
    	$this->view->subtitle = "Zadat pracoviště";
    	
    	$form = $this->_helper->workplaceFormInit();
    	
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
		if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
			$form->subsidiary_id->setMultiOptions ( $formContent );
		}
    	$defaultNamespace = new Zend_Session_Namespace();
		
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
    	
    	//pokud je odeslán, zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost());
    	
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form->populate($this->getRequest()->getPost());
    		$this->view->form = $form;
    		return;
    	}
    	
    	//zpracování formuláře  	
    	try{
	    	$formData = $this->getRequest()->getPost();
	    		    	
	    	//vložení pracoviště
	    	$workplace = new Application_Model_Workplace($formData);
	    	$workplaces = new Application_Model_DbTable_Workplace();
	    	$workplaceId = $workplaces->addWorkplace($workplace);
	    	
	    	$factors = new Application_Model_DbTable_WorkplaceFactor();
	    	$risks = new Application_Model_DbTable_WorkplaceRisk();
			foreach($formData as $key => $value){
				//vložení FPP
				if(preg_match('/factor\d+/', $key) || preg_match('/newFactor\d+/', $key)){
					if($value['applies'] == "1"){
						$factor = new Application_Model_WorkplaceFactor($value);
						$factor->setWorkplaceId($workplaceId);
						$factors->addWorkplaceFactor($factor);
					}
				}
				//vložení rizik
				if(preg_match('/risk\d+/', $key) || preg_match('/newRisk\d+/', $key)){
					if($value['risk'] != ''){
	    				$risk = new Application_Model_WorkplaceRisk($value);
	    				$risk->setWorkplaceId($workplaceId);
	    				$risks->addWorkplaceRisk($risk);
					}
	    		}				
			}
			
			$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, 'přidal pracoviště ' . $workplace->getName() . ' k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
	    	
	    	$this->_helper->FlashMessenger('Pracoviště ' . $workplace->getName() . ' přidáno.');
	    	if ($form->getElement('other')->isChecked()){
	    		$this->_helper->redirector->gotoRoute ( array ('clientId' => $this->_clientId), 'workplaceNew' );
	    	}
	    	else{
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId), 'clientAdmin');
	    	}
    	}
    	catch(Zend_Exception $e){
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. Zkuste to prosím znovu nebo kontaktujte administrátora.' . $e->getMessage());
    		$defaultNamespace->formData = $formData;
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId), 'workplaceNew');
    	}
    	
    }

    public function newfactorAction()
    {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newfactor', 'html')->initContext();
		
		$id = $this->_getParam('id_factor', null);
		
		$element = new My_Form_Element_WorkplaceFactor("newFactor$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		
		$this->view->field = $element->__toString();
    }

    public function newriskAction()
    {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newrisk', 'html')->initContext();
		
		$id = $this->_getParam('id_risk', null);
		
		$element = new My_Form_Element_WorkplaceRisk("newRisk$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		
		$this->view->field = $element->__toString();
    }

    public function listAction()
    {
        $clientId = $this->getRequest()->getParam('clientId');
        $clients = new Application_Model_DbTable_Client();
        $client = $clients->getClient($clientId);
        $this->view->subtitle = "Databáze pracovišť - " . $client->getCompanyName();
        $this->view->clientId = $clientId;
        $workplacesDb = new Application_Model_DbTable_Workplace();
        $workplaces = $workplacesDb->getByClientDetails($clientId);
        if($workplaces != null){
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
	        foreach ($workplaces as $key => $workplace){
	        	if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($workplace['id_subsidiary']))){
	        		unset($workplaces[$key]);
	        	}
	        }
        }
        $this->view->workplaces = $workplaces;
    }

    public function editAction()
    {
        $this->view->subtitle = "Upravit pracoviště";
        $workplaceId = $this->getRequest()->getParam('workplaceId');
        
        $form = new Application_Form_Workplace();
        $workplaces = new Application_Model_DbTable_Workplace();
        $workplace = $workplaces->getWorkplace($workplaceId);
        
        //naplnění comboboxu s pobočkami
   		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
		if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
			$form->subsidiary_id->setMultiOptions ( $formContent );
		}
		$defaultNamespace = new Zend_Session_Namespace();
		
		//když není odeslán, naplníme daty z databáze nebo ze session
		if(!$this->getRequest()->isPost()){
			if (isset ( $defaultNamespace->formData )) {
				$form = $this->prepareFormWithFormData($form, $defaultNamespace->formData);
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			}
			else{
				//naplnění základních polí pro pracoviště
				$form->populate($workplace->toArray());
				
				//přidání FPP
				$order = 7;
				$workplaceFactors = $workplaces->getWorkplaceFactors($workplaceId);
				if(count($workplaceFactors) > 0){
					foreach ($workplaceFactors as $workplaceFactor){
						$form->addElement('workplaceFactor', 'factor' . $order, array(
							'idWorkplaceFactor' => $workplaceFactor->getIdWorkplaceFactor(),
							'factor' => $workplaceFactor->getFactor(),
							'applies' => true,
							'note' => $workplaceFactor->getNote(),
							'order' => $order,
							'validators' => array(new My_Validate_WorkplaceFactor()),
						));
						$order++;
					}			
				}
		        $form->addElement('hidden', 'id_factor', array(
		        	'value' => $order,
		        ));
		        
		        //přidání rizik
		        $order = 102;
		        $workplaceRisks = $workplaces->getWorkplaceRisks($workplaceId);
		        if(count($workplaceRisks) > 0){
		        	foreach($workplaceRisks as $workplaceRisk){
		        		$form->addElement('workplaceRisk', 'risk' . $order, array(
		        			'idWorkplaceRisk' => $workplaceRisk->getIdWorkplaceRisk(),
		        			'risk' => $workplaceRisk->getRisk(),
		        			'note' => $workplaceRisk->getNote(),
		        			'order' => $order,
		        			'validators' => array(new My_Validate_WorkplaceRisk()),
		        		));
		        		$order++;
		        	}
		        }
		        $form->addElement('hidden', 'id_risk', array(
		        	'value' => $order,
		        ));
		        
		        $form->removeElement('other');
		        $form->save->setLabel('Uložit');
			}
	        $this->view->form = $form;
	        return;
		}
		
    	//pokud je odeslán, zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost());
    	
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form = $this->prepareFormWithFormData($form, $this->getRequest()->getPost());
    		$form->populate($this->getRequest()->getPost());
    		$this->view->form = $form;
    		return;
    	}
    	
    	//zpracování formuláře
    	try{
    		$formData = $this->getRequest()->getPost();
    		
    		//update pracoviště
    		$workplace = new Application_Model_Workplace($formData);
    		$workplaces->updateWorkplace($workplace);
    		
    		$factors = new Application_Model_DbTable_WorkplaceFactor();
    		$risks = new Application_Model_DbTable_WorkplaceRisk();
    		foreach ($formData as $key => $value){
    			//update FPP
    			if(preg_match('/factor\d+/', $key)){
    				if($value['applies'] == "1"){
    					$factor = new Application_Model_WorkplaceFactor($value);
    					$factor->setWorkplaceId($workplaceId);
    					$factors->updateWorkplaceFactor($factor);
    				}
    				//mazání FPP
    				else{
    					$factors->deleteWorkplaceFactor($value['id_workplace_factor']);
    				}
    			}
    			//nové FPP
    			if(preg_match('/newFactor\d+/', $key)){
    				if($value['applies'] == "1"){
    					$factor = new Application_Model_WorkplaceFactor($value);
    					$factor->setWorkplaceId($workplaceId);
    					$factors->addWorkplaceFactor($factor);
    				}
    			}
    			//update rizik
    			if(preg_match('/risk\d+/', $key)){
    				if($value['risk'] != ''){
    					$risk = new Application_Model_WorkplaceRisk($value);
    					$risk->setWorkplaceId($workplaceId);
    					$risks->updateWorkplaceRisk($risk);
    				}
    			}
    			//nová rizika
    			if(preg_match('/newRisk\d+/', $key)){
    				$risk = new Application_Model_WorkplaceRisk($value);
    				$risk->setWorkplaceId($workplaceId);
    				$risks->addWorkplaceRisk($risk);
    			}
    		}
    		$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, 'upravil pracoviště ' . $workplace->getName() . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
    		
    		$this->_helper->FlashMessenger('Pracoviště ' . $workplace->getName() . ' upraveno.');
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId), 'clientAdmin');
    	}
    	catch (Exception $e){
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. Zkuste to prosím znovu nebo kontaktujte administrátora.' . $e->getMessage());
    		$defaultNamespace->formData = $formData;
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'workplaceId' => $workplaceId), 'workplaceEdit');
    	}
    }

    public function deleteAction()
    {
        if($this->getRequest()->getMethod() == 'POST'){
        	$workplaceId = $this->_getParam('select');
        	$workplaces = new Application_Model_DbTable_Workplace();
        	$workplace = $workplaces->getWorkplace($workplaceId);
        	$name = $workplace->getName();
        	$workplaces->deleteWorkplace($workplaceId);
        	
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, ' smazal pracoviště ' . $workplace->getName() . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
    		
        	$this->_helper->FlashMessenger('Pracoviště <strong>' . $name . '</strong> bylo vymazáno.');
        	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId), 'clientAdmin');
        }
        else{
        	throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání pracoviště.', 500);
        }
    }
    
    private function prepareFormWithFormData($form, $formData){
    	$orderFactor = 7;
    	$orderRisk = 102;
    	foreach($formData as $key => $value){
    		if (preg_match('/factor\d+/', $key) || preg_match('/newFactor\d+/', $key)){
    			$form->addElement('workplaceFactor', $key, array(
    				'order' => $orderFactor,
    				'validators' => array(new My_Validate_WorkplaceFactor()),
    			));
    			$orderFactor++;
    		}
    		if (preg_match('/risk\d+/', $key) || preg_match('/newRisk\d+/', $key)){
    			$form->addElement('workplaceRisk', $key, array(
    				'order' => $orderRisk,
    				'validators' => array(new My_Validate_WorkplaceRisk()),
    			));
    			$orderRisk++;
    		}
    	}
    	$form->addElement('hidden', 'id_factor', array(
		    'value' => $orderFactor,
		));
		$form->addElement('hidden', 'id_risk', array(
			'value' => $orderRisk,
		));
		$form->removeElement('other');
		$form->save->setLabel('Uložit');
		
		return $form;
    }

}





