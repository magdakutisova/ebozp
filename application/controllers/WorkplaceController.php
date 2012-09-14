<?php

class WorkplaceController extends Zend_Controller_Action
{
	
	private $_subsidiary;
	private $_clientId;

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        $this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
        
        //získání odkazu na pobočku
        if ($this->getRequest()->getActionName() == 'newfactor' || $this->getRequest()->getActionName() == 'newrisk'){
        	//nic
        }
        else{
        	$this->_clientId = $this->getRequest()->getParam('clientId');
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	$this->_subsidiary = $subsidiaries->getHeadquarters($this->_clientId);
        	//nastavit přístupová práva
        }
    }

    public function indexAction()
    {
        // action body
    }

    public function newAction()
    {
    	$this->view->subtitle = "Zadat pracoviště";
    	
    	$form = new Application_Form_Workplace(); 
    	
    	$form->save->setLabel('Přidat pracoviště');
    	
    	//pokud formulář není odeslán, předáme formulář do view
    	if(!$this->getRequest()->isPost()){
    		$this->view->form = $form;
    		return;
    	}
    	
    	//pokud je odeslán, zmapujeme nové prvky
    	$form->preValidation($this->getRequest()->getPost());
    	
    	//když není platný, vrátíme ho do view
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$form->populate($this->getRequest()->getPost());
    		$this->view->form = $form;
    		$formData = $this->getRequest()->getPost();
    		return;
    	}
    	
    	try{
	    	//zpracování formuláře
	    	$formData = $this->getRequest()->getPost();
	    	
	    	//vložení pracoviště
	    	$workplace = new Application_Model_Workplace($formData);
	    	$workplace->setSubsidiaryId($this->_subsidiary->getIdSubsidiary());
	    	$workplaces = new Application_Model_DbTable_Workplace();
	    	$workplaceId = $workplaces->addWorkplace($workplace);
	    	
	    	//vložení FPP
	    	$factors = new Application_Model_DbTable_WorkplaceFactor();
			foreach($formData as $key => $value){
				if(preg_match('/factor\d+/', $key) || preg_match('/newFactor\d+/', $key)){
					if($value['applies'] == "1"){
						$factor = new Application_Model_WorkplaceFactor();
						$factor->setFactor($value['factor']);
						$factor->setNote($value['note']);
						$factor->setWorkplaceId($workplaceId);
						$factors->addWorkplaceFactor($factor);
					}
				}
			}
	    	
	    	//vložení rizik
	    	$risks = new Application_Model_DbTable_WorkplaceRisk();
	    	foreach($formData as $key => $value){
	    		if(preg_match('/risk\d+/', $key) || preg_match('/newRisk\d+/', $key)){
	    			$risk = new Application_Model_WorkplaceRisk();
	    			$risk->setRisk($value['risk']);
	    			$risk->setNote($value['note']);
	    			$risk->setWorkplaceId($workplaceId);
	    			$risks->addWorkplaceRisk($risk);
	    		}
	    	}
	    	
	    	//TODO zápis do bezpečnostního deníku
	    	//TODO redirect
    	}
    	catch(Zend_Exception $e){
    		//TODO redirect
    	}
    	$this->view->form = $form;
    }

	public function newfactorAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newfactor', 'html')->initContext();
		
		$id = $this->_getParam('id_factor', null);
		
		$element = new My_Form_Element_WorkplaceFactor("newFactor$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		
		$this->view->field = $element->__toString();
	}
	
	public function newriskAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newrisk', 'html')->initContext();
		
		$id = $this->_getParam('id_risk', null);
		
		$element = new My_Form_Element_WorkplaceRisk("newRisk$id");
		$element->addPrefixPath('My_Form_Decorator', 'My/Form/Decorator', 'decorator');
		
		$this->view->field = $element->__toString();
	}
}