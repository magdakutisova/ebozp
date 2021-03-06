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
    private $_chemicalList = null;
    private $_employeeList = null;
    private $_workplaceList = null;
    private $_yesNoList = array();
    private $_sexList = array();
    private $_yearOfBirthList = array();
    private $_technicalDeviceList = array();
    private $_folderList = array();
    private $_schoolingList = array();
    private $_environmentFactorList = array();
    private $_canCreateWorkplace;
    private $_canEditWorkplace;
    private $_canDeleteWorkplace;
    private $_canCreateFolder;
    private $_canSwitchFolder;
    private $_canDeleteFolder;

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        
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
        
        //získání seznamu technických prostředků
        $technicalDevices = new Application_Model_DbTable_TechnicalDevice();
        $this->_technicalDeviceList = $technicalDevices->getTechnicalDevices($this->_clientId);
        
        //získání seznamu chemických látek
        $chemicals = new Application_Model_DbTable_Chemical();
        $this->_chemicalList = $chemicals->getChemicals($this->_clientId);
        
        //získání seznamu zaměstnanců
        $employees = new Application_Model_DbTable_Employee();
        $this->_employeeList[0] = '-----';
        $this->_employeeList = $this->_employeeList + $employees->getResponsibleEmployees($this->_clientId);
        
        //získání seznamu FPP
        $this->_environmentFactorList = My_EnvironmentFactor::getEnvironmentFactors();
        
        //získání seznamu pracovišť
        $workplaces = new Application_Model_DbTable_Workplace();
        $this->_workplaceList = $workplaces->getWorkplacesWithSubsidiaryName($this->_clientId);
        
        //získání seznamu umístění
        $folders = new Application_Model_DbTable_Folder();
        $this->_folderList = $folders->getFolders($this->_clientId);
        
        //získání seznamu ano/ne
        $this->_yesNoList[0] = 'Ne';
        $this->_yesNoList[1] = 'Ano';
         
        //získání seznamu pohlaví
        $this->_sexList[0] = 'Muž';
        $this->_sexList[1] = 'Žena';
         
        //získání seznamu roků narození
        for ($i=1920; $i<=date('Y'); $i++){
        	$this->_yearOfBirthList[$i] = $i;
        }
        
        //získání seznamu školení
        $defaultSchoolings = My_Schooling::getSchoolings();
        $schoolings = new Application_Model_DbTable_Schooling();
        $extraSchoolings = $schoolings->getExtraSchoolings($this->_clientId);
        $this->_schoolingList = $defaultSchoolings + $extraSchoolings;
         
        //přístupová práva
        $this->_username = Zend_Auth::getInstance()->getIdentity()->username;
        $users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
		
		$this->_canCreateWorkplace = $this->_acl->isAllowed($this->_user, 'workplace', 'new');
		$this->view->canCreateWorkplace = $this->_canCreateWorkplace;
		$this->_canEditWorkplace = $this->_acl->isAllowed($this->_user, 'workplace', 'edit');
		$this->view->canEditWorkplace = $this->_canEditWorkplace;
		$this->_canDeleteWorkplace = $this->_acl->isAllowed($this->_user, 'workplace', 'delete');
		$this->view->canDeleteWorkplace = $this->_canDeleteWorkplace;
		$this->_canCreateFolder = $this->_acl->isAllowed($this->_user, 'workplace', 'newfolder');
		$this->view->canCreateFolder = $this->_canCreateFolder;
		$this->_canEditFolder = $this->_acl->isAllowed($this->_user, 'workplace', 'switchfolder');
		$this->view->canEditFolder = $this->_canEditFolder;
		$this->_canDeleteFolder = $this->_acl->isAllowed($this->_user, 'workplace', 'deletefolder');
		$this->view->canDeleteFolder = $this->_canDeleteFolder;
		
		//soukromá poznámka
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
    }

    public function newAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace();
    	$this->view->subtitle = "Zadat pracoviště";
    	$form = $this->loadOrCreateForm($defaultNamespace);
    	
    	//získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	 
    	$form->client_id->setValue($clientId);
    	
    	//naplnění multiselectu pobočkami
    	$subsidiaries = new Application_Model_DbTable_Subsidiary ();
    	$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
    	if ($formContent != 0){
    		$formContent = $this->filterSubsidiarySelect($formContent);
    		$form->subsidiary_id->setMultiOptions ( $formContent );
    	}
    	$form->subsidiary_id->setValue($subsidiaryId);
    	
    	$form->folder_id->setMultiOptions($this->_folderList);
    	
    	//inicializace plovoucích formulářů
    	$this->initFloatingForms($formContent, $subsidiaryId);
    	
    	//naplnění formuláře hodnotami z DB
		$form = $this->fillMultiselects($form);
		$form->new_position->setAttrib('class', $subsidiaryId);
		$form->save->setLabel('Uložit');
    	
    	//zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost());
    	
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
    		$form->populate($form->getValues());
    		$this->view->form = $form;
    		return;
    	}
    	
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$adapter = $workplaces->getAdapter();
    	
    	//zpracování formuláře  	
    	try{
    		//init session pro případ selhání ukládání
	    	$formData = $this->getRequest()->getPost();
	    	$defaultNamespace->formData = $formData;
	    	$defaultNamespace->form = $form;

	    	//zahájení transakce
	    	$adapter->beginTransaction();
	    	
	    	//vložení pracoviště
	    	$workplace = new Application_Model_Workplace($formData);
	    	$workplace->setClientId($this->_clientId);
	    	if($formData['folder_id'] == 0){
	    		$workplace->setFolderId(null);
	    	}
	    	if($formData['boss_id'] == 0){
	    		$workplace->setBossId(null);
	    	}

	    	$workplaceId = $workplaces->addWorkplace($workplace);
	    	if(!$workplaceId){
	    		$this->_helper->FlashMessenger('Chyba! Pracoviště s tímto názvem již existuje. Zvolte prosím jiný název.');
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
	    	}
	    	
	    	$this->_helper->workplaceRelationships($formData, $workplaceId);
			
			//uložení transakce
			$adapter->commit();
            
            $identity = Zend_Auth::getInstance()->getIdentity();
            $username = $identity->name;
            
            if (in_array($identity->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
                $username .= " (G7)";
            }
			
			$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord->insertMessage('přidal pracoviště ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary(), 'filter' => 'vse'), 'workplaceList', $workplace->getName(), $workplace->getSubsidiaryId());
	    	
	    	$this->_helper->FlashMessenger('Pracoviště ' . $workplace->getName() . ' přidáno.');
	    	unset($defaultNamespace->form);
	    	unset($defaultNamespace->formData);
	    	if ($form->getElement('other')->isChecked()){
	    		$this->_helper->redirector->gotoRoute ( array ('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew' );
	    	}
	    	else{
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList');
	    	}
    	}
    	catch(Exception $e){
    		//zrušení transakce
    		$adapter->rollback();
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. ' . $e . $e->getMessage() . $e->getTraceAsString());
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
    	}
    	
    }

    
    public function addworkAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addwork', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$works = new Application_Model_DbTable_Work();
    	$work = new Application_Model_Work($data);
    	$existsWork = $works->existsWork($work->getWork());
    	$workId = 0;
    	if(!$existsWork){
    		$workId = $works->addWork($work);
    	}   	
    	else{
    		$workId = $existsWork;
    	}
    	$clientHasWork = new Application_Model_DbTable_ClientHasWork();
    	$clientHasWork->addRelation($this->_getParam('clientId'), $workId);    	
    }
    
    public function populateworksAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$works = new Application_Model_DbTable_Work();
    	$this->_workList = $works->getWorks($this->_clientId);
    	echo Zend_Json::encode($this->_workList);
    }
    
    public function validateAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$workplaces = new Application_Model_DbTable_Workplace();
    	if($workplaces->existsWorkplaceSub($this->getRequest()->getParam('name'), $this->getRequest()->getParam('subsidiaryId'))){
    		if($this->getRequest()->getParam('workplaceId')){
    			$workplace = $workplaces->getWorkplace($this->getRequest()->getParam('workplaceId'));
    			if($workplace->getName() == $this->getRequest()->getParam('name')){
    				echo Zend_Json::encode(true);
    			}
    			else{
    				echo Zend_Json::encode(false);
    			}
    		}
    		else{
    			echo Zend_Json::encode(false);
    		}
    	}
    	else{
    		echo Zend_Json::encode(true);
    	}
    }
    
    public function addtechnicaldeviceAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addtechnicaldevice', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$technicalDevice = new Application_Model_TechnicalDevice($data);
    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
    	$existsTechnicalDevice = $technicalDevices->existsTechnicalDevice($technicalDevice->getSort(), $technicalDevice->getType());
    	$technicalDeviceId = 0;
    	if(!$existsTechnicalDevice){
    		$technicalDeviceId = $technicalDevices->addTechnicalDevice($technicalDevice);
    	}
    	else{
    		$technicalDeviceId = $existsTechnicalDevice;
    	}
    	$clientHasTechnicalDevice = new Application_Model_DbTable_ClientHasTechnicalDevice();
    	$clientHasTechnicalDevice->addRelation($this->_getParam('clientId'), $technicalDeviceId);
    }
    
    public function populatetechnicaldevicesAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
    	$this->_technicalDeviceList = $technicalDevices->getTechnicalDevices($this->_clientId);
    	echo Zend_Json::encode($this->_technicalDeviceList);
    }
    
    public function addfolderAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addfolder', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$folder = new Application_Model_Folder($data);
    	$folders = new Application_Model_DbTable_Folder();
    	$folder->setClientId($this->_getParam('clientId'));
    	$folders->addFolder($folder);
    }
    
    public function populatefoldersAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$folders = new Application_Model_DbTable_Folder();
    	$this->_folderList = $folders->getFolders($this->_clientId);
    	echo Zend_Json::encode($this->_folderList);
    }
    
    public function addbossAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addboss', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$employee = new Application_Model_Employee($data);
    	$employees = new Application_Model_DbTable_Employee();
    	$employee->setClientId($this->_getParam('clientId'));
    	$employees->addEmployee($employee);
    }
    
    public function populatebosssAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$employees = new Application_Model_DbTable_Employee();
    	$this->_employeeList[0] = '-----';
        $this->_employeeList = $this->_employeeList + $employees->getResponsibleEmployees($this->_clientId);
        echo Zend_Json::encode($this->_employeeList);
    }
    
    public function addchemicalAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addchemical', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$chemical = new Application_Model_Chemical($data);
    	$chemicals = new Application_Model_DbTable_Chemical();
    	$existsChemical = $chemicals->existsChemical($chemical->getChemical());
    	$chemicalId = 0;
    	if(!$existsChemical){
    		$chemicalId = $chemicals->addChemical($chemical);
    	}
    	else{
    		$chemicalId = $existsChemical;
    	}
    	$clientHasChemical = new Application_Model_DbTable_ClientHasChemical();
    	$clientHasChemical->addRelation($this->_getParam('clientId'), $chemicalId);
    }
    
    public function populatechemicalsAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$chemicals = new Application_Model_DbTable_Chemical();
    	$this->_chemicalList = $chemicals->getChemicals($this->_clientId);
    	echo Zend_Json::encode($this->_chemicalList);
    }
    
    public function chemicaldetailAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('chemicaldetail', 'html')->initContext();
    	
    	$id = $this->_getParam('id_chemical', null);
    	
    	$element = new My_Form_Element_ChemicalDetail("chemicalDetail$id");
    	$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
    	$element->setIdChemical($this->_getParam('idChemical'));
    	$element->setChemical($this->_getParam('chemical'));
    	
    	$this->view->field = $element->__toString();
    }
    
    public function addpositionAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addposition', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	 
    	$data = $this->_getAllParams();
    	$position = new Application_Model_Position($data);
    	$positions = new Application_Model_DbTable_Position();
    	$position->setClientId($this->_getParam('clientId'));
    	$positionIds = array();
    	foreach($data['subsidiaryList'] as $subs){
    		$position->setSubsidiaryId($subs);
    		$positionIds[] = $positions->addPosition($position);
    	}
    	
    	$this->_helper->positionRelationships($data, $positionIds);
    	
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        $username = $identity->name;

        if (in_array($identity->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
            $username .= " (G7)";
        }
        
    	foreach($positionIds as $positionId){
    		$position = $positions->getPosition($positionId);
    		$subsidiary = $subsidiaries->getSubsidiary($position->getSubsidiaryId());
    		$this->_helper->diaryRecord($username, 'přidal pracovní pozici "' . $position->getPosition() . '" k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $position->getSubsidiaryId(), 'filter' => 'vse'), 'positionList', '(databáze pracovních pozic)', $position->getSubsidiaryId());
    	}
    }
    
    public function populatepositionsAction(){
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	$positions = new Application_Model_DbTable_Position();
    	$this->_positionList = $positions->getPositions($this->_clientId);
    	echo Zend_Json::encode($this->_positionList);
    }

    public function listAction()
    {
        $defaultNamespace = new Zend_Session_Namespace();
        if (isset($defaultNamespace->form)){
        	unset($defaultNamespace->form);
        }
        if (isset($defaultNamespace->formData)){
        	unset($defaultNamespace->formData);
        }
    	
    	$clients = new Application_Model_DbTable_Client();
        $client = $clients->getClient($this->_clientId);
        $this->view->archived = $client->getArchived();
        
        $this->view->subtitle = "Databáze pracovišť - " . $client->getCompanyName();
        $this->view->clientId = $this->_clientId;
        $filter = $this->getRequest()->getParam('filter');
        $this->view->filter = $filter;
        
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
			
			//vkládání podadresářů
			$this->initFolderInsert($subsidiaryId);
			
			//mazání podadresářů
			$this->initFolderDelete($subsidiaryId);
			
			//přesouvání do jiného podadresáře
			$this->initFolderSwitch($subsidiaryId);
			
			//vypisování pracovišť
			$workplaceDb = new Application_Model_DbTable_Workplace();
			if($filter == 'vse'){
				$workplaces = $workplaceDb->getBySubsidiaryWithDetails($subsidiaryId);
			}
			if($filter == 'neuplna'){
				$workplaces = $workplaceDb->getBySubsidiaryWithDetails($subsidiaryId, true);
			}
			$this->view->workplaces = $workplaces;
		}
    }

    public function editAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace();
    	$form = $this->loadOrCreateForm($defaultNamespace);
    	
        //získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$workplaceId = $this->getRequest()->getParam('workplaceId');
        
    	$form->client_id->setValue($clientId);
    	$form->id_workplace->setValue($workplaceId);
        
        $workplaces = new Application_Model_DbTable_Workplace();
        $workplace = $workplaces->getWorkplaceComplete($workplaceId);
        
        $this->view->subtitle = "Upravit pracoviště " . $workplace["name"];
        
        //naplnění comboboxu s pobočkami
   		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
		if ($formContent != 0){
			$formContent = $this->filterSubsidiarySelect($formContent);
			$form->subsidiary_id->setMultiOptions ( $formContent );
		}
		$form->subsidiary_id->setValue($subsidiaryId);
		 
		$form->folder_id->setMultiOptions($this->_folderList);
		 
		//inicializace plovoucích formulářů
		$this->initFloatingForms($formContent, $subsidiaryId);
		
		$form = $this->fillMultiselects($form);
		
		//přidat chemical details
		if(isset($workplace['chemicalDetails'])){
			$order = $form->getValue('id_chemical');
			foreach($workplace['chemicalDetails'] as $detail){
				$form->addElement('chemicalDetail', 'chemicalDetail' . $order, array(
						'value' => $detail,
						'order' => $order,
						));
				$order++;
			}
			$form->id_chemical->setValue($order);
		}
		
		$form->removeElement('other');
		$form->getElement('save')->setLabel('Uložit');
		
		//zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost());
		
		//když není odeslán, naplníme daty z databáze nebo ze session
		if(!$this->getRequest()->isPost()){
			$this->view->form = $form;
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			}
			else{
				//naplnění základních polí pro pracoviště
				$form->populate($workplace);
			}
			return;
		}
		
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form->populate($form->getValues());
    		$this->view->form = $form;
    		return;
    	}
    	
    	//zpracování formuláře
    	$adapter = $workplaces->getAdapter();
    	try{
    		//init session pro případ selhání ukládání
	    	$formData = $this->getRequest()->getPost();
	    	$defaultNamespace->formData = $formData;
	    	$defaultNamespace->form = $form;

	    	//zahájení transakce
	    	$adapter->beginTransaction();
	    	
    		//update pracoviště
    		$workplaceNew = new Application_Model_Workplace($formData);
    		if($formData['folder_id'] == 0){
    			$workplaceNew->setFolderId(null);
    		}
    		if($formData['boss_id'] == 0){
    			$workplaceNew->setBossId(null);
    		}
    		$differentName = true;
    		if($workplace['name'] == $workplaceNew->getName()){
    			$differentName = false;
    		}
    		if(!$workplaces->updateWorkplace($workplaceNew, $differentName)){
    			$this->_helper->FlashMessenger('Chyba! Pracoviště s tímto názvem již existuje. Zvolte prosím jiný název.');
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceEdit');
    		}
    		
    		$this->_helper->workplaceRelationships($formData, $workplaceId, true);
			
    		//uložení transakce
			$adapter->commit();
    		
            $identity = Zend_Auth::getInstance()->getIdentity();
            $username = $identity->name;
            
            if (in_array($identity->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
                $username .= " (G7)";
            }
            
    		$subsidiary = $subsidiaries->getSubsidiary($workplaceNew->getSubsidiaryId());
            $this->_helper->diaryRecord->insertMessage('upravil pracoviště ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList', $workplaceNew->getName(), $workplaceNew->getSubsidiaryId());

    		$this->_helper->FlashMessenger('Pracoviště ' . $workplaceNew->getName() . ' upraveno.');
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList');
    	}
    	catch (Exception $e){
    		$adapter->rollback();
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. Zkuste to prosím znovu nebo kontaktujte administrátora.' . $e->getMessage());
    		$defaultNamespace->formData = $formData;
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'workplaceId' => $workplaceId), 'workplaceEdit');
    	}
    }

    public function deleteAction()
    {
        if($this->getRequest()->getMethod() == 'POST'){
        	$subsidiaryId = $this->_getParam('subsidiaryId');
        	$workplaceId = $this->_getParam('workplaceId');
        	$workplaces = new Application_Model_DbTable_Workplace();
        	$workplace = $workplaces->getWorkplace($workplaceId);
        	$name = $workplace->getName();
        	$workplaces->deleteWorkplace($workplaceId);
        	
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	$subsidiary = $subsidiaries->getSubsidiary($subsidiaryId);
            
            $identity = Zend_Auth::getInstance()->getIdentity();
            $username = $identity->name;
            
            if (in_array($identity->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
                $username .= " (G7)";
            }
            
            $this->_helper->diaryRecord->insertMessage('smazal pracoviště ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList', $workplace->getName(), $workplace->getSubsidiaryId());
    		
        	$this->_helper->FlashMessenger('Pracoviště <strong>' . $name . '</strong> bylo vymazáno.');
        	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList');
        }
        else{
        	throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání pracoviště.', 403);
        }
    }
    
    public function newfolderAction(){
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$folder = new Application_Model_Folder();
    	$folder->setFolder($this->getRequest()->getParam('folder'));
    	$folder->setClientId($this->_clientId);
    	$folders = new Application_Model_DbTable_Folder();
    	$folders->addFolder($folder);
    	$this->_helper->FlashMessenger('Adresář ' . $folder->getFolder() . ' přidán.');
    	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workplaceList');
    }
    
    public function switchfolderAction(){
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$workplaceId = $this->getRequest()->getParam('workplaceId');
    	$folderId = $this->getRequest()->getParam('folderId');
    	
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$workplace = $workplaces->getWorkplace($workplaceId);
    	if($folderId == 0){
    		$workplace->setFolderId(null);
    	}
    	else{
    		$workplace->setFolderId($folderId);
    	}
    	$workplaces->updateWorkplace($workplace);
    	
    	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workplaceList');
    }
    
    public function deletefolderAction(){
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$folderId = $this->getRequest()->getParam('folderId');
    	if($folderId == 0){
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workplaceList');
    	}
    	$folders = new Application_Model_DbTable_Folder();
    	$folders->deleteFolder($folderId);
    	$this->_helper->FlashMessenger('Adresář smazán.');
    	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workplaceList');
    }
    
    private function filterSubsidiarySelect($formContent){
    	$subsidiaries = new Application_Model_DbTable_Subsidiary();
    	foreach ($formContent as $key => $subsidiary){
			if (!$this->_acl->isAllowed($this->_user, $subsidiaries->getSubsidiary($key))){
				unset($formContent[$key]);
			}
		}
		return $formContent;
    }
    
    private function initSubsidiarySwitch($formContent, $subsidiaryId){
    	$selectForm = new Application_Form_Select ();
		$selectForm->select->setMultiOptions ( $formContent );
		$selectForm->select->setLabel('Vyberte pobočku:');
		$selectForm->submit->setLabel('Vybrat');
		$this->view->selectForm = $selectForm;
		$subsidiaryId = array_shift(array_keys($formContent));
					
		if ($this->getRequest ()->isPost () && in_array('Vybrat', $this->getRequest()->getPost())) {
			$formData = $this->getRequest ()->getPost ();
			$subsidiaryId = $formData['select'];
			$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => $this->getRequest()->getParam('filter')), 'workplaceList');
		}
		else{
			$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
			$selectForm->select->setValue($subsidiaryId);
		}
		$this->view->subsidiaryId = $subsidiaryId;
		return $subsidiaryId;
    }

    private function initFolderInsert($subsidiaryId){
    	$textForm = new Application_Form_Text();
		$textForm->text->setLabel('Název umístění:');
		$textForm->submit->setLabel('Přidat');
		$this->view->textForm = $textForm;
		if($this->getRequest()->isPost() && in_array('Přidat', $this->getRequest()->getPost())){
			$formData = $this->getRequest()->getPost();
			if($textForm->isValid($formData)){
				$this->_helper->redirector->gotoSimple('newfolder', 'workplace', null, array(
					'clientId' => $this->_clientId,
					'subsidiaryId' => $subsidiaryId,
					'filter' => $this->getRequest()->getParam('filter'),
					'folder' => $formData['text'],
				));
			}
			else{
				$textForm->populate($formData);
			}
		}
    }
    
    private function initFolderDelete($subsidiaryId){
    	$deleteForm = new Application_Form_Select();
		$deleteForm->select->setLabel('Vyberte umístění:');
		$deleteForm->submit->setLabel('Smazat');
		$folders = new Application_Model_DbTable_Folder();
		$folderList = $folders->getFolders($this->_clientId);
		$deleteForm->select->setMultiOptions($folderList);
		$this->view->deleteForm = $deleteForm;
		if($this->getRequest()->isPost() && in_array('Smazat', $this->getRequest()->getPost())){
			$formData = $this->getRequest()->getPost();
			$this->_helper->redirector->gotoSimple('deletefolder', 'workplace', null, array(
				'clientId' => $this->_clientId,
				'subsidiaryId' => $subsidiaryId,
				'filter' => $this->getRequest()->getParam('filter'),
				'folderId' => $formData['select'],
			));
		}
    }
    
    private function initFolderSwitch($subsidiaryId){
    	if($this->getRequest()->isPost() && in_array('Uložit', $this->getRequest()->getPost())){
			$formData = $this->getRequest()->getPost();
			$this->_helper->redirector->gotoSimple('switchfolder', 'workplace', null, array(
				'clientId' => $this->_clientId,
				'subsidiaryId' => $subsidiaryId,
				'workplaceId' => $formData['workplace_id'],
				'filter' => $this->getRequest()->getParam('filter'),
				'folderId' => $formData['select'],
			));
		}
    }
    
    private function loadOrCreateForm($defaultNamespace){
    	//pokud předtím selhalo odeslání, tak se načte aktuální formulář se všemi dodatečně vloženými elementy
    	if (isset ( $defaultNamespace->form )) {
    		$form = $defaultNamespace->form;
			unset ( $defaultNamespace->form );
		}
		//jinak se vytvoří nový
		else{
    		$form = new Application_Form_Workplace();
		}
		return $form;
    }
    
    private function fillMultiselects($form){
    	if($form->positionList != null){
    		$form->positionList->setMultiOptions($this->_positionList);
    	}
    	if($form->workList != null){
			$form->workList->setMultiOptions($this->_workList);
    	}
    	if($form->technicaldeviceList != null){
			$form->technicaldeviceList->setMultiOptions($this->_technicalDeviceList);
    	}
    	if($form->chemicalList != null){
			$form->chemicalList->setMultiOptions($this->_chemicalList);
    	}
    	if($form->boss_id != null){
    		$form->boss_id->setMultiOptions($this->_employeeList);
    	}
		return $form;
    }
    
    private function initFloatingForms($formContent, $subsidiaryId){
    	$formPosition = new Application_Form_Position();
    	$formPosition->clientId->setValue($this->_clientId);
    	$formPosition->subsidiaryList->setMultiOptions($formContent);
    	$formPosition->subsidiaryList->setValue($subsidiaryId);
    	$formPosition->workplaceList->setMultiOptions($this->_workplaceList);
    	$formPosition->environmentfactorList->setMultiOptions($this->_environmentFactorList);
    	$formPosition->schoolingList->setMultiOptions($this->_schoolingList);
    	$formPosition->workList->setMultiOptions($this->_workList);
    	$formPosition->technicaldeviceList->setMultiOptions($this->_technicalDeviceList);
    	$formPosition->chemicalList->setMultiOptions($this->_chemicalList);
    	$formPosition->removeElement('new_workplace');
    	$formPosition->removeElement('other');
    	$formPosition->removeElement('save');
    	$formPosition->removeElement('employees');
    	$formPosition->removeElement('employeeList');
    	$formPosition->removeElement('new_employee');
    	$formPosition->addElement('button', 'save', array(
    			'decorators' => array(
       				'ViewHelper',
       				array('Errors'),
       				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 5)),
       				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
       			),
    			'order' => 9999,
    			'class' => array('position', 'workplace', 'ajaxSave'),
    			'label' => 'Uložit',
    			));
    	$this->view->formPosition = $formPosition;
    	 
    	$formSchooling = new Application_Form_Schooling();
    	$formSchooling->clientId->setValue($this->_clientId);
    	$formSchooling->save_schooling->setAttrib('class', array('schooling', 'position', 'ajaxSave'));
    	$this->view->formSchooling = $formSchooling;
    	 
    	$formWork = new Application_Form_Work();
    	$formWork->clientId->setValue($this->_clientId);
    	$formWork->save_work->setAttrib('class', array('work', 'workplace', 'ajaxSave'));
    	$this->view->formWork = $formWork;
    	 
    	$formTechnicalDevice = new Application_Form_TechnicalDevice();
    	$formTechnicalDevice->clientId->setValue($this->_clientId);
    	$formTechnicalDevice->save_technicaldevice->setAttrib('class', array('technicaldevice', 'workplace', 'ajaxSave'));
    	$this->view->formTechnicalDevice = $formTechnicalDevice;
    	 
    	$formChemical = new Application_Form_Chemical();
    	$formChemical->clientId->setValue($this->_clientId);
    	$formChemical->save_chemical->setAttrib('class', array('chemical', 'workplace', 'ajaxSave'));
    	$this->view->formChemical = $formChemical;
    	 
    	$formFolder = new Application_Form_Folder();
    	$formFolder->clientId->setValue($this->_clientId);
    	$formFolder->save_folder->setAttrib('class', array('folder', 'workplace', 'ajaxSave'));
    	$this->view->formFolder = $formFolder;
    	
    	$formBoss = new Application_Form_ResponsibleEmployee();
    	$formBoss->clientId->setValue($this->_clientId);
    	$formBoss->removeElement('save_responsible_employee');
    	$elementDecorator2 = array(
    			'ViewHelper',
    			array('Errors'),
    			array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
    			array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	);
    	$formBoss->addElement('button', 'save_boss', array(
				'decorators' => $elementDecorator2,
				'label' => 'Uložit zaměstnance',
    			));
    	$formBoss->save_boss->setAttrib('class', array('boss', 'workplace', 'ajaxSave'));
    	$formBoss->setName('boss');
    	$this->view->formBoss = $formBoss;
    }
    
}