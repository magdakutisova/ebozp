<?php

class WorkplaceController extends Zend_Controller_Action
{
	
	private $_subsidiary;

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        
        //získání odkazu na pobočku
        if ($this->getRequest()->getActionName() == 'newfactor' || $this->getRequest()->getActionName() == 'newrisk'){
        	//nic
        }
        else{
        	$clientId = $this->getRequest()->getParam('clientId');
        	$subsidiaries = new Application_Model_DbTable_Subsidiary();
        	$this->_subsidiary = $subsidiaries->getHeadquarters($clientId);
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
    	
    	$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
    	$form = new Application_Form_Workplace();
    	
    	$form->save->setLabel('Přidat pracoviště');
    	
    	if(!$this->getRequest()->isPost()){
    		$this->view->form = $form;
    		return;
    	}
    	
    	$form->preValidation($this->getRequest()->getPost());
    	
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$this->view->form = $form;
    		$formData = $this->getRequest()->getPost();
    		return;
    	}
    	
    	//zpracování formuláře
    	$formData = $this->getRequest()->getPost();
    	//My_Debug::dump($formData);
    	
    	//vložení pracoviště - NEOTESTOVÁNO!!
    	$workplace = new Application_Model_Workplace($formData);
    	$workplace->setSubsidiaryId($this->_subsidiary->getIdSubsidiary());
    	$workplaces = new Application_Model_DbTable_Workplace();
    	$workplaceId = $workplaces->addWorkplace($workplace);
    	
    	//vložení FPP
		foreach($formData as $key => $value){
			if(preg_match('/factor\d+/', $key) || preg_match('/newFactor\d+/', $key)){
				if(isset($value['applies'])){
					$factor = new Application_Model_WorkplaceFactor();
					//dodělat vložení do DB
					Zend_Debug::dump($value['factor']);
				}
			}
		}
		die();
    	
    	//vložení rizik
    	
    	
    	$this->view->form = $form;
    }

	public function newfactorAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newfactor', 'html')->initContext();
		
		$id = $this->_getParam('id_factor', null);
		
		$element = new My_Form_Element_WorkplaceFactor("newFactor$id");
		
		$this->view->field = $element->__toString();
	}
	
	public function newriskAction(){
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('newrisk', 'html')->initContext();
		
		$id = $this->_getParam('id_risk', null);
		
		$element = new My_Form_Element_WorkplaceRisk("newRisk$id");
		
		$this->view->field = $element->__toString();
	}
}