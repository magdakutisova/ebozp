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
		
		$form = $this->fillMultiselects($form);
		
		$form->save->setLabel('Uložit');
    	
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
    		$form->populate($this->getRequest()->getPost());
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
    
    public function removepositionAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('removeposition', 'html')->initContext();
    	
    	$workplaceId = $this->_getParam('workplaceId', null);
    	$positionId = $this->_getParam('positionId', null);

    	$workplaceHasPosition = new Application_Model_DbTable_WorkplaceHasPosition();
    	$workplaceHasPosition->removeRelation($workplaceId, $positionId);
    }
    
	public function removeworkAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('removework', 'html')->initContext();
    	
    	$workplaceId = $this->_getParam('workplaceId', null);
    	$workId = $this->_getParam('workId', null);

    	$workplaceHasWork = new Application_Model_DbTable_WorkplaceHasWork();
    	$workplaceHasWork->removeRelation($workplaceId, $workId);
    }
    
	public function removetechnicaldeviceAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('removetechnicaldevice', 'html')->initContext();
    	
    	$workplaceId = $this->_getParam('workplaceId', null);
    	$technicalDeviceId = $this->_getParam('technicalDeviceId', null);

    	$workplaceHasTechnicalDevice = new Application_Model_DbTable_WorkplaceHasTechnicalDevice();
    	$workplaceHasTechnicalDevice->removeRelation($workplaceId, $technicalDeviceId);
    }
    
	public function removechemicalAction(){
    	$ajaxContext = $this->_helper->getHelper('AjaxContext');
    	$ajaxContext->addActionContext('removechemical', 'html')->initContext();
    	
    	$workplaceId = $this->_getParam('workplaceId', null);
    	$chemicalId = $this->_getParam('chemicalId', null);

    	$workplaceHasChemical = new Application_Model_DbTable_WorkplaceHasChemical();
    	$workplaceHasChemical->removeRelation($workplaceId, $chemicalId);
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
        //die();
    	$form = $this->loadOrCreateForm($defaultNamespace);
    	//Zend_Debug::dump($form);
        
        //získání parametrů ID klienta a pobočky
    	$clientId = $this->getRequest()->getParam('clientId');
    	$subsidiaryId = $this->getRequest()->getParam('subsidiaryId');
    	$workplaceId = $this->getRequest()->getParam('workplaceId');
        
    	$form->client_id->setValue($clientId);
    	$form->id_workplace->setValue($workplaceId);
        
        $workplaces = new Application_Model_DbTable_Workplace();
        $workplace = $workplaces->getWorkplace($workplaceId);
        
        //naplnění comboboxu s pobočkami
   		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$formContent = $subsidiaries->getSubsidiaries ( $this->_clientId, 0, 1 );
		if ($formContent != 0){
			$formContent = $this->filterSubsidiarySelect($formContent);
			$form->subsidiary_id->setMultiOptions ( $formContent );
		}
		
		$form = $this->fillMultiselects($form);
		$form->removeElement('other');
		$form->getElement('save')->setLabel('Uložit');
		
		//zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost(), $this->_positionList, $this->_workList, $this->_sortList, $this->_typeList, $this->_chemicalList, true);
		$form->removeElement('position');
		$form->removeElement('work');
		$form->removeElement('technical_device');
		$form->removeElement('chemical');
    	
		//když není odeslán, naplníme daty z databáze nebo ze session
		if(!$this->getRequest()->isPost()){
			$this->view->form = $form;
			if (isset ( $defaultNamespace->formData )) {
				$form->populate ( $defaultNamespace->formData );
				unset ( $defaultNamespace->formData );
			}
			else{
				//naplnění základních polí pro pracoviště
				$form->populate($workplace->toArray());
				$positions = new Application_Model_DbTable_Position();
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
				$form->getElement('id_chemical')->setValue($order);
			}
			return;
		}
		
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form->populate($this->getRequest()->getPost());
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
    	if($form->position != null){
    		$form->position->setAttrib('multiOptions', $this->_positionList);
    	}
    	if($form->work != null){
			$form->work->setAttrib('multiOptions', $this->_workList);
    	}
    	if($form->technical_device != null){
			$form->technical_device->setAttrib('multiOptions', $this->_sortList);
			$form->technical_device->setAttrib('multiOptions2', $this->_typeList);
    	}
    	if($form->chemical != null){
			$form->chemical->setAttrib('multiOptions', $this->_chemicalList);
    	}
		return $form;
    }
    
    private function processCustomElements($form, $formData, $workplaceId){
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
			//vložení pracovních pozic
			if($key == "position" || preg_match('/newPosition\d+/', $key)){
				//pokud je vybraná nebo vyplněná nějaká pozice
				if($value['position'] != 0 || $value['position'] != '' || $value['new_position'] != ''){
					$position = new Application_Model_Position($value);
					//pokud je vypsaná v textboxu při editaci
					$position->setPosition($value['position']);
					//pokud pozice je vybraná v multiselectu
					if(preg_match('/\d+/', $value['position']) && $value['position'] != 0){
						$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
						$label = $listNameOptions[$value['position']];
						$position->setPosition($label);
					}
					//pokud jsou obě políčka prázdná
					elseif(($value['position'] == 0 || $value['position'] == '') && $value['new_position'] == ''){
						continue;
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
				if($value['work'] != 0 || $value['work'] != '' || $value['new_work'] != ''){
					$work = new Application_Model_Work($value);
					$work->setWork($value['work']);
					if(preg_match('/\d+/', $value['work']) && $value['work'] != 0){
						$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
						$label = $listNameOptions[$value['work']];
						$work->setWork($label);
					}
					elseif(($value['work'] == 0 || $value['work'] == '') && $value['new_work'] == ''){
						continue;
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
				if($value['sort'] != 0 || $value['sort'] != '' || $value['new_sort'] != '' ||
					$value['type'] != 0 || $value['type'] != '' || $value['new_type'] != ''){
					$technicalDevice = new Application_Model_TechnicalDevice($value);
					$technicalDevice->setSort($value['sort']);
					$technicalDevice->setType($value['type']);
					if(preg_match('/\d+/', $value['sort']) && $value['sort'] != 0){
						$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
						$label = $listNameOptions[$value['sort']];
						$technicalDevice->setSort($label);
					}
					elseif(($value['sort'] == 0 || $value['sort'] == '') && $value['new_sort'] == ''){
						$technicalDevice->setSort('');
					}
					if($value['new_sort'] != ''){
						$technicalDevice->setSort($value['new_sort']);
					}
					if(preg_match('/\d+/', $value['type']) && $value['type'] != 0){
						$listNameOptions = $form->getElement($key)->getAttrib('multiOptions2');
						$label = $listNameOptions[$value['type']];
						$technicalDevice->setType($label);
					}
					elseif(($value['type'] == 0 || $value['type'] == '') && $value['new_type'] == '')
					{
						$technicalDevice->setType('');
					}	
					if($value['new_type'] != ''){
						$technicalDevice->setType($value['new_type']);
					}
					if($technicalDevice->getSort() == '' && $technicalDevice->getType() == ''){
						continue;
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
				if($value['chemical'] != 0 || $value['chemical'] != '' || $value['new_chemical'] != ''){
					$chemical = new Application_Model_Chemical($value);
					$chemical->setChemical($value['chemical']);
					if(preg_match('/\d+/', $value['chemical']) && $value['chemical'] != 0){
						$listNameOptions = $form->getElement($key)->getAttrib('multiOptions');
						$label = $listNameOptions[$value['chemical']];
						$chemical->setChemical($label);
					}
					//pokud jsou obě políčka prázdná
					elseif(($value['chemical'] == 0 || $value['chemical'] == '') && $value['new_chemical'] == ''){
						continue;
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
    }
    
}