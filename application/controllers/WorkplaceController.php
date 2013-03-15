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
        
        //získání seznamu technických prostředků
        $technicalDevices = new Application_Model_DbTable_TechnicalDevice();
        $this->_technicalDeviceList = $technicalDevices->getTechnicalDevices($this->_clientId);
        
        //získání seznamu chemických látek
        $chemicals = new Application_Model_DbTable_Chemical();
        $this->_chemicalList = $chemicals->getChemicals($this->_clientId);
        
        //získání seznamu zaměstnanců
        $employees = new Application_Model_DbTable_Employee();
        $this->_employeeList = $employees->getEmployees($this->_clientId);
        
        //získání seznamu pracovišť
        $workplaces = new Application_Model_DbTable_Workplace();
        $this->_workplaceList = $workplaces->getWorkplaces($this->_clientId);
        
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
        
        //přístupová práva
        $this->_username = Zend_Auth::getInstance()->getIdentity()->username;
        $users = new Application_Model_DbTable_User();
		$this->_user = $users->getByUsername($this->_username);
		
		//soukromá poznámka
		$this->view->canViewPrivate = $this->_acl->isAllowed($this->_user, 'private');
    }

    public function indexAction()
    {
        // action body
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

	    	$workplaceId = $workplaces->addWorkplace($workplace);
	    	if(!$workplaceId){
	    		$this->_helper->FlashMessenger('Chyba! Pracoviště s tímto názvem již existuje. Zvolte prosím jiný název.');
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
	    	}
	    	
	    	$this->processCustomElements($form, $formData, $workplaceId);
			
			//uložení transakce
			$adapter->commit();
			
			$subsidiary = $subsidiaries->getSubsidiary($workplace->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, 'přidal pracoviště "' . $workplace->getName() . '" k pobočce ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary(), 'filter' => 'vse'), 'workplaceList', '(databáze pracovišť)', $workplace->getSubsidiaryId());
	    	
	    	$this->_helper->FlashMessenger('Pracoviště ' . $workplace->getName() . ' přidáno.');
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
    		$this->_helper->FlashMessenger('Uložení pracoviště do databáze selhalo. Zkuste to prosím znovu nebo kontaktujte administrátora. ' . $e . $e->getMessage() . $e->getTraceAsString());
    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceNew');
    	}
    	
    }

    
    public function addworkAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addwork', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$work = new Application_Model_Work($data);
    	//$work->setWork($this->_getParam('name'));
    	$works = new Application_Model_DbTable_Work();
    	$workId = $works->addWork($work);
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
    
    public function addtechnicaldeviceAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addtechnicaldevice', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$technicalDevice = new Application_Model_TechnicalDevice($data);
    	$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
    	$technicalDeviceId = $technicalDevices->addTechnicalDevice($technicalDevice);
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
    
    public function addchemicalAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('addchemical', 'html')->initContext();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	$data = $this->_getAllParams();
    	$chemical = new Application_Model_Chemical($data);
    	$chemicals = new Application_Model_DbTable_Chemical();
    	$chemicalId = $chemicals->addChemical($chemical);
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
    	$this->view->subtitle = "Upravit pracoviště";
        $form = $this->loadOrCreateForm($defaultNamespace);
    	
        //získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$workplaceId = $this->getRequest()->getParam('workplaceId');
        
    	$form->client_id->setValue($clientId);
    	$form->id_workplace->setValue($workplaceId);
        
        $workplaces = new Application_Model_DbTable_Workplace();
        $workplace = $workplaces->getWorkplaceComplete($workplaceId);
        
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
				/* $positions = new Application_Model_DbTable_Position();
				$works = new Application_Model_DbTable_Work();
				$technicalDevices = new Application_Model_DbTable_TechnicalDevice();
				$chemicals = new Application_Model_DbTable_Chemical();
				
				//přidání pracovních pozic
				$order = 16;
				$positionData = $positions->getByWorkplace($workplaceId);
				if(count($positionData) > 0){
					foreach($positionData as $position){
						$newPosition = new My_Form_Element_Position('newPosition' . $order, array(
							'order' => $order,
							'validators' => array(new My_Validate_Position()),
							'toEdit' => true,
						));
						$value = array('id_position' => $position->getIdPosition(),
										'position' => $position->getPosition(),
										'new_position' => '');
						$newPosition->setValue($value);
						$form->addElement($newPosition);
						$order++;
					}
				}
				$form->getElement('id_position')->setValue($order);
				
				//přidání pracovních činností
				$order = 102;
				$workData = $works->getByWorkplace($workplaceId);
				if(count($workData) > 0){
					foreach($workData as $work){
						$newWork = new My_Form_Element_Work('newWork' . $order, array(
							'order' => $order,
							'validators' => array(new My_Validate_Work()),
							'toEdit' => true,
						));
						$value = array('id_work' => $work->getIdWork(),
										'work' => $work->getWork(),
										'new_work' => '');
						$newWork->setValue($value);
						$form->addElement($newWork);
						$order++;
					}
				}
				$form->getElement('id_work')->setValue($order);
				
				//přidání technických prostředků
				$order = 202;
				$technicalDeviceData = $technicalDevices->getByWorkplace($workplaceId);
				if(count($technicalDeviceData) > 0){
					foreach($technicalDeviceData as $technicalDevice){
						$newTechnicalDevice = new My_Form_Element_TechnicalDevice('newTechnicalDevice' . $order, array(
							'order' => $order,
							'validators' => array(new My_Validate_TechnicalDevice()),
							'toEdit' => true,
						));
						$value = array('id_technical_device' => $technicalDevice->getIdTechnicalDevice(),
										'sort' => $technicalDevice->getSort(),
										'type' => $technicalDevice->getType(),
										'new_sort' => '',
										'new_type' => '');
						$newTechnicalDevice->setValue($value);
						$form->addElement($newTechnicalDevice);
						$order++;
					}
				}
				$form->getElement('id_technical_device')->setValue($order);
				
				//přidání chemických látek
				$order = 302;
				$chemicalData = $chemicals->getByWorkplace($workplaceId);
				if(count($chemicalData) > 0){
					foreach($chemicalData as $chemical){
						$newChemical = new My_Form_Element_ChemicalComplete('newChemical' . $order, array(
							'order' => $order,
							'validators' => array(new My_Validate_Chemical()),
							'toEdit' => true,
						));
						$value = array('id_chemical' => $chemical['chemical']->getIdChemical(),
										'chemical' => $chemical['chemical']->getChemical(),
										'usual_amount' => $chemical['usual_amount'],
										'use_purpose' => $chemical['use_purpose'],
										'new_chemical' => '');
						$newChemical->setValue($value);
						$form->addElement($newChemical);
						$order++;
					}
				}
				$form->getElement('id_chemical')->setValue($order); */
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
    		$differentName = true;
    		if($workplace->getName() == $workplaceNew->getName()){
    			$differentName = false;
    		}
    		if(!$workplaces->updateWorkplace($workplaceNew, $differentName)){
    			$this->_helper->FlashMessenger('Chyba! Pracoviště s tímto názvem již existuje. Zvolte prosím jiný název.');
	    		$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId), 'workplaceEdit');
    		}
    		
    		$this->processCustomElements($form, $formData, $workplaceId);
			
    		//uložení transakce
			$adapter->commit();
    		
    		$subsidiary = $subsidiaries->getSubsidiary($workplaceNew->getSubsidiaryId());
	    	$this->_helper->diaryRecord($this->_username, 'upravil pracoviště ' . $workplaceNew->getName() . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiary->getIdSubsidiary(), 'filter' => 'vse'), 'workplaceList', '(databáze pracovišť)', $workplaceNew->getSubsidiaryId());
    		
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
	    	$this->_helper->diaryRecord($this->_username, ' smazal pracoviště ' . $name . ' pobočky ' . $subsidiary->getSubsidiaryName() . ' ', array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList', '(databáze pracovišť)', $subsidiaryId);
    		
        	$this->_helper->FlashMessenger('Pracoviště <strong>' . $name . '</strong> bylo vymazáno.');
        	$this->_helper->redirector->gotoRoute(array('clientId' => $this->_clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'), 'workplaceList');
        }
        else{
        	throw new Zend_Controller_Action_Exception('Nekorektní pokus o smazání pracoviště.', 500);
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
			$this->_helper->redirector->gotoSimple('newfolder', 'workplace', null, array(
				'clientId' => $this->_clientId,
				'subsidiaryId' => $subsidiaryId,
				'filter' => $this->getRequest()->getParam('filter'),
				'folder' => $formData['text'],
			));
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
		return $form;
    }
    
    private function processCustomElements($form, $formData, $workplaceId){
    	$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
    	$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
    	$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
    	$workplaceHasChemical = new Application_Model_DbTable_WorkplaceHasChemical();
    	
		foreach ($formData['positionList'] as $positionId){
			$workplaceHasPosition->addRelation($workplaceId, $positionId);
		}
		foreach ($formData['workList'] as $workId){
			$workplaceHasWork->addRelation($workplaceId, $workId);
		}
		foreach ($formData['technicaldeviceList'] as $technicalDeviceId){
			$workplaceHasTechnicalDevice->addRelation($workplaceId, $technicalDeviceId);
		}
		foreach ($formData['chemicalList'] as $chemicalId){
			$usePurpose = "";
			$usualAmount = "";
			$chemicalDetails = array_filter(array_keys($formData), array($this, 'findChemicalDetails'));
			foreach($chemicalDetails as $detail){
				if($detail['id_chemical'] == $chemicalId){
					$usePurpose = $detail['use_purpose'];
					$usualAmount = $detail['usual_amount'];
					break 1;
				}
			}
			$workplaceHasChemical->addRelation($workplaceId, $chemicalId, $usePurpose, $usualAmount);
		}		
    }
    
    private function findChemicalDetails($chemicalDetail){
    	if(strpos($chemicalDetail, "chemicalDetail") !== false){
    		return $chemicalDetail;
    	}
    }
    
    private function initFloatingForms($formContent, $subsidiaryId){
    	$formPosition = new Application_Form_Position();
    	$formPosition->clientId->setValue($this->_clientId);
    	$formPosition->subsidiaryList->setMultiOptions($formContent);
    	$formPosition->subsidiaryList->setValue($subsidiaryId);
    	$formPosition->workplace->setAttrib('multiOptions', $this->_workplaceList);
    	$formPosition->employeeList->setMultiOptions($this->_employeeList);
    	$formPosition->save->setAttrib('class', array('position', 'ajaxSave'));
    	$formPosition->save->setLabel('Uložit');
    	$this->view->formPosition = $formPosition;
    	 
    	$formEmployee = new Application_Form_Employee();
    	$formEmployee->clientId->setValue($this->_clientId);
    	$formEmployee->year_of_birth->setMultiOptions($this->_yearOfBirthList);
    	$formEmployee->manager->setMultiOptions($this->_yesNoList);
    	$formEmployee->sex->setMultiOptions($this->_sexList);
    	$formEmployee->save_employee->setAttrib('class', array('employee', 'ajaxSave'));
    	$this->view->formEmployee = $formEmployee;
    	 
    	$formWork = new Application_Form_Work();
    	$formWork->clientId->setValue($this->_clientId);
    	$formWork->belongsTo->setValue('workplace');
    	$formWork->save_work->setAttrib('class', array('work', 'ajaxSave'));
    	$this->view->formWork = $formWork;
    	 
    	$formTechnicalDevice = new Application_Form_TechnicalDevice();
    	$formTechnicalDevice->clientId->setValue($this->_clientId);
    	$formTechnicalDevice->belongsTo->setValue('workplace');
    	$formTechnicalDevice->save_technicaldevice->setAttrib('class', array('technicaldevice', 'ajaxSave'));
    	$this->view->formTechnicalDevice = $formTechnicalDevice;
    	 
    	$formChemical = new Application_Form_Chemical();
    	$formChemical->clientId->setValue($this->_clientId);
    	$formChemical->belongsTo->setValue('workplace');
    	$formChemical->save_chemical->setAttrib('class', array('chemical', 'ajaxSave'));
    	$this->view->formChemical = $formChemical;
    	 
    	$formFolder = new Application_Form_Folder();
    	$formFolder->clientId->setValue($this->_clientId);
    	$formFolder->belongsTo->setValue('workplace');
    	$formFolder->save_folder->setAttrib('class', array('folder', 'ajaxSave'));
    	$this->view->formFolder = $formFolder;
    }
    
}