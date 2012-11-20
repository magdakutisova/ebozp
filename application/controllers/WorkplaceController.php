<?php

class WorkplaceController extends Zend_Controller_Action
{

    private $_headquarters = null;
    private $_clientId = null;
    private $_acl = null;
    private $_user = null;
    private $_username = null;    
    private $_positionList = null;
    private $_workList = null;
    private $_sortList = null;
    private $_typeList = null;
    private $_chemicalList = null;

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        $this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
        
        //získání odkazu na centrálu - instance Application_Model_Subsidiary
        $action = $this->getRequest()->getActionName();
        $this->_acl = new My_Controller_Helper_Acl();
        $this->_clientId = $this->getRequest()->getParam('clientId');
        $subsidiaries = new Application_Model_DbTable_Subsidiary();
        $this->_headquarters = $subsidiaries->getHeadquarters($this->_clientId);
        
        //získání seznamu pracovních pozic
        $positions = new Application_Model_DbTable_Position();
        $this->_positionList = $positions->getPositions($this->_clientId);
        
        //získání seznamu pracovních činností
        $works = new Application_Model_DbTable_Work();
        $this->_workList = $works->getWorks($this->_clientId);
        
        //získání seznamů druhů a typů technických prostředků
        $technicalDevices = new Application_Model_DbTable_TechnicalDevice();
        $this->_sortList = $technicalDevices->getSorts($this->_clientId);
        $this->_typeList = $technicalDevices->getTypes($this->_clientId);
        
        //získání seznamu chemických látek
        $chemicals = new Application_Model_DbTable_Chemical();
        $this->_chemicalList = $chemicals->getChemicals($this->_clientId);
        
        //přístupová práva
        $this->_username = Zend_Auth::getInstance()->getIdentity()->username;
        $users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);

		//do new může jen ten, kdo má přístup k centrále
		if ($action == 'new'){
			if(!$this->_acl->isAllowed($this->_user, $this->_headquarters)){
				$this->_helper->redirector('denied', 'error');
			}
		}
		
		//soukromá poznámka
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
    }

    public function indexAction()
    {
        // action body
    }

    public function newAction()
    {
    	$this->view->subtitle = "Zadat pracoviště";
    	
    	$form = new Application_Form_Workplace();
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$form->client_id->setValue($clientId);
    	//$form = $this->_helper->workplaceFormInit();
    	
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
		$form->subsidiary_id->setValue($subsidiaryId);
		$form->position->setAttrib('multiOptions', $this->_positionList);
		$form->work->setAttrib('multiOptions', $this->_workList);
		$form->technical_device->setAttrib('multiOptions', $this->_sortList);
		$form->technical_device->setAttrib('multiOptions2', $this->_typeList);
		$form->chemical->setAttrib('multiOptions', $this->_chemicalList);
		$form->save->setLabel('Uložit');
		
    	$defaultNamespace = new Zend_Session_Namespace();
    	
    	//zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost(), $this->_positionList, $this->_workList, $this->_sortList, $this->_typeList, $this->_chemicalList);
		
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
    	
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		//Zend_Debug::dump($this->getRequest()->getPost());
    		$form->populate($this->getRequest()->getPost());
    		$this->view->form = $form;
    		return;
    	}
    	
    	//zpracování formuláře  	
    	try{
	    	$formData = $this->getRequest()->getPost();
	    	//My_Debug::dump($formData);
	    		    	
	    	//vložení pracoviště
	    	$workplace = new Application_Model_Workplace($formData);
	    	$workplace->setClientId($this->_clientId);
	    	$workplaces = new Application_Model_DbTable_Workplace();
	    	$workplaceId = $workplaces->addWorkplace($workplace);
	    	if(!$workplaceId){
	    		$defaultNamespace->formData = $formData;
	    		$this->_helper->FlashMessenger('Chyba! Pracoviště s tímto názvem již existuje. Zvolte prosím jiný název.');
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
	    	}
	    	
	    	$positions = new Application_Model_DbTable_Position();
	    	$works = new Application_Model_DbTable_Work();
	    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
	    	$chemicals = new Application_Model_DbTable_Chemical();
	    	$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
	    	$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
	    	$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
	    	$workplaceHasChemical = new Application_Model_DbTable_WorkplaceHasChemical();
	    	$clientHasChemical = new Application_Model_DbTable_ClientHasChemical();
	    	$clientHasWork = new Application_Model_DbTable_ClientHasWork();
	    	$clientHasTechnicalDevice = new Application_Model_DbTable_ClientHasTechnicalDevice();
	    	
			foreach($formData as $key => $value){
				//Zend_Debug::dump($formData);
				//Zend_Debug::dump($key);
				//Zend_Debug::dump($value);
				//vložení pracovních pozic
				if($key == "position" || preg_match('/newPosition\d+/', $key)){
					if($value['position'] != 0 || $value['new_position'] != ''){
						//Zend_Debug::dump($value);
						$position = new Application_Model_Position($value);
						if($value['position'] != 0){
							$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
							$label = $listNameOptions[$value['position']];
							$position->setPosition($label);
						}
						elseif($value['new_position'] == ''){
							$position->setPosition('');
						}
						if($value['new_position'] != ''){
							$position->setPosition($value['new_position']);
						}
						$position->setClientId($this->_clientId);
						
						$existingPosition = $positions->existsPosition($position->getPosition(), $this->_clientId);
						if($existingPosition){
							$workplaceHasPosition->addRelation($workplaceId, $existingPosition);
						}
						else{
							$positionId = $positions->addPosition($position);
							$workplaceHasPosition->addRelation($workplaceId, $positionId);
						}
					}
				}
				
				//vložení pracovních činností
				if($key == "work" || preg_match('/newWork\d+/', $key)){
					if($value['work'] != 0 || $value['new_work'] != ''){
						$work = new Application_Model_Work($value);
						if($value['work'] != 0){
							$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
							$label = $listNameOptions[$value['work']];
							$work->setWork($label);
						}
						elseif($value['new_work'] == ''){
							$work->setWork('');
						}
						if($value['new_work'] != ''){
							$work->setWork($value['new_work']);
						}
						$existingWork = $works->existsWork($work->getWork());
						if($existingWork){
							$workplaceHasWork->addRelation($workplaceId, $existingWork);
							$clientHasWork->addRelation($this->_clientId, $existingWork);
						}
						else{
							$workId = $works->addWork($work);
							$workplaceHasWork->addRelation($workplaceId, $workId);
							$clientHasWork->addRelation($this->_clientId, $workId);
						}
					}
				}

				//vložení technických zařízení
				if($key == "technical_device" || preg_match('/newTechnicalDevice\d+/', $key)){
					if($value['sort'] != 0 || $value['new_sort'] != '' || $value['type'] != 0 || $value['new_type'] != ''){
						$technicalDevice = new Application_Model_TechnicalDevice($value);
						if($value['sort'] != 0){
							$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
							$label = $listNameOptions[$value['sort']];
							$technicalDevice->setSort($label);
						}
						elseif($value['new_sort'] == ''){
							$technicalDevice->setSort('');
						}
						if($value['new_sort'] != ''){
							$technicalDevice->setSort($value['new_sort']);
						}
						if($value['type'] != 0){
							$listNameOptions = $form->getElement($key)->getAttrib('multiOptions2');
							$label = $listNameOptions[$value['type']];
							$technicalDevice->setType($label);
						}
						elseif($value['new_type'] == '')
						{
							$technicalDevice->setType('');
						}	
						if($value['new_type'] != ''){
							$technicalDevice->setType($value['new_type']);
						}
						$existingTechnicalDevice = $technicalDevices->existsTechnicalDevice($technicalDevice->getSort(), $technicalDevice->getType());
						if($existingTechnicalDevice){
							$workplaceHasTechnicalDevice->addRelation($workplaceId, $existingTechnicalDevice);
							$clientHasTechnicalDevice->addRelation($this->_clientId, $existingTechnicalDevice);
						}
						else{
							$technicalDeviceId = $technicalDevices->addTechnicalDevice($technicalDevice);
							$workplaceHasTechnicalDevice->addRelation($workplaceId, $technicalDeviceId);
							$clientHasTechnicalDevice->addRelation($this->_clientId, $technicalDeviceId);
						}
					}
				}
				
				//vložení chemických látek
				if($key == "chemical" || preg_match('/newChemical\d+/', $key)){
					if($value['chemical'] != 0 || $value['new_chemical']){
						$chemical = new Application_Model_Chemical($value);
						if($value['chemical'] != 0){
							$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
							$label = $listNameOptions[$value['chemical']];
							$chemical->setChemical($label);
						}
						elseif($value['new_chemical'] == ''){
							$chemical->setChemical('');
						}
						if($value['new_chemical'] != ''){
							$chemical->setChemical($value['new_chemical']);
						}
						$existingChemical = $chemicals->existsChemical($chemical->getChemical());
						if($existingChemical){
							$workplaceHasChemical->addRelation($workplaceId, $existingChemical, $value['use_purpose'], $value['usual_amount']);
							$clientHasChemical->addRelation($this->_clientId, $existingChemical);
						}
						else{
							$chemicalId = $chemicals->addChemical($chemical);
							$workplaceHasChemical->addRelation($workplaceId, $chemicalId, $value['use_purpose'], $value['usual_amount']);
							$clientHasChemical->addRelation($this->_clientId, $chemicalId);
						}
					}
				}
			}
			
			$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, 'přidal pracoviště "' . $workplace->getName() . '" k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary()), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
	    	
	    	$this->_helper->FlashMessenger('Pracoviště ' . $workplace->getName() . ' přidáno.');
	    	if ($form->getElement('other')->isChecked()){
	    		$this->_helper->redirector->gotoRoute ( array ('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew' );
	    	}
	    	else{
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsididaryId' => $subsidiary->getIdSubsidiary()), 'workplaceList');
	    	}
    	}
    	catch(Zend_Exception $e){
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. Zkuste to prosím znovu nebo kontaktujte administrátora. ' . $e->getMessage() . $e->getTraceAsString());
    		$defaultNamespace->formData = $formData;
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
    	}
    	
    }

    public function newpositionAction()
    {
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newposition', 'html')->initContext();
		
		$id = $this->_getParam('id_position', null);
		
		$element = new My_Form_Element_Position("newPosition$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		$element->setAttrib('multiOptions', $this->_positionList);
		
		$this->view->field = $element->__toString();
    }
    
    public function newworkAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newwork', 'html')->initContext();
    	
    	$id = $this->_getParam('id_work', null);
    	
    	$element = new My_Form_Element_Work("newWork$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_workList);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newtechnicaldeviceAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newtechnicaldevice', 'html')->initContext();
    	
    	$id = $this->_getParam('id_technical_device', null);
    	
    	$element = new My_Form_Element_TechnicalDevice("newTechnicalDevice$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_sortList);
    	$element->setAttrib('multiOptions2', $this->_typeList);
    	
    	$this->view->field = $element->__toString();
    }
    
    public function newchemicalAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('newchemical', 'html')->initContext();
    	
    	$id = $this->_getParam('id_chemical', null);
    	
    	$element = new My_Form_Element_ChemicalComplete("newChemical$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setAttrib('multiOptions', $this->_chemicalList);
    	
    	$this->view->field = $element->__toString();
    }

    public function listAction()
    {
        $clients = new Application_Model_DbTable_Client();
        $client = $clients->getClient($this->_clientId);
        
        $this->view->subtitle = "Databáze pracovišť - " . $client->getCompanyName();
        $this->view->clientId = $this->_clientId;
        
        //výběr poboček
        $subsidiaries = new Application_Model_DbTable_Subsidiary();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
		
    	if ($formContent != 0){
			foreach ($formContent as $key => $subsidiary){
				if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
					unset($formContent[$key]);
				}
			}
    	}
		
    	$subsidiaryId = null;
    	
		if ($formContent != 0) {
			$selectForm = new Application_Form_Select ();
			$selectForm->select->setMultiOptions ( $formContent );
			$selectForm->select->setLabel('Vyberte pobočku:');
			$selectForm->submit->setLabel('Vybrat');
			$this->view->selectForm = $selectForm;
			$subsidiaryId = array_shift(array_keys($formContent));
						
			if ($this->getRequest ()->isPost () && in_array('Vybrat', $this->getRequest()->getPost())) {
				$formData = $this->getRequest ()->getPost ();
				$subsidiaryId = $formData['select'];
				$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceList');
			}
			else{
				$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
				$selectForm->select->setValue($subsidiaryId);
			}
			$this->view->subsidiaryId = $subsidiaryId;
		}
    	else{
			$selectForm = "<p>Klient nemá žádné pobočky nebo k nim nemáte přístup.</p>";
			$this->view->selectForm = $selectForm;
		}
		
		if($subsidiaryId != null){
			//vkládání podadresářů
			$textForm = new Application_Form_Text();
			$textForm->text->setLabel('Název adresáře:');
			$textForm->submit->setLabel('Přidat');
			$this->view->textForm = $textForm;
			if($this->getRequest()->isPost() && in_array('Přidat', $this->getRequest()->getPost())){
				$formData = $this->getRequest()->getPost();
				$this->_helper->redirector->gotoSimple('newfolder', 'workplace', null, array(
					'clientId' => $this->_clientId,
					'subsidiaryId' => $subsidiaryId,
					'folder' => $formData['text'],
				));
			}
			
			//mazání podadresářů
			$deleteForm = new Application_Form_Select();
			$deleteForm->select->setLabel('Vyberte adresář:');
			$deleteForm->submit->setLabel('Smazat');
			$folders = new Application_Model_DbTable_Folder();
			$folderList = $folders->getFolders($this->_clientId);
			Zend_Debug::dump($folderList);
			$deleteForm->select->setMultiOptions($folderList);
			$this->view->deleteForm = $deleteForm;
			
			//vypisování pracovišť
			$workplaceDb = new Application_Model_DbTable_Workplace();
			$workplaces = $workplaceDb->getBySubsidiaryWithDetails($subsidiaryId);
			$this->view->workplaces = $workplaces;
		}
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
	    	$this->_helper->diaryRecord($this->_username, 'upravil pracoviště ' . $workplace->getName() . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary()), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
    		
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
	    	$this->_helper->diaryRecord($this->_username, ' smazal pracoviště ' . $workplace->getName() . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary()), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
    		
        	$this->_helper->FlashMessenger('Pracoviště <strong>' . $name . '</strong> bylo vymazáno.');
        	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId), 'clientAdmin');
        }
        else{
        	throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání pracoviště.', 500);
        }
    }
    
    public function newfolderAction(){
    	$this->_helper->layout->disableLayout();
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$folder = new Application_Model_Folder();
    	$folder->setFolder($this->getRequest()->getParam('folder'));
    	$folders = new Application_Model_DbTable_Folder();
    	$folders->addFolder($folder);
    	$this->_helper->FlashMessenger('Adresář ' . $folder->getFolder() . ' přidán.');
    	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceList');
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





