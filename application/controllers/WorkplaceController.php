<?php

class WorkplaceController extends Zend_Controller_Action
{

    public function init()
    {
    	//globální nastavení view
        $this->view->title = 'Pracoviště';
        $this->view->headTitle($this->view->title);
        $this->_helper->layout()->setLayout('clientLayout');
        
        //nastavit přístupová práva
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

    	$form->getSubForm('secondHalf')->save->setLabel('Přidat pracoviště');
    	
    	if(!$this->getRequest()->isPost()){
    		$this->view->form = $form;
    		return;
    	}
    	
    	$form->preValidation($this->getRequest()->getPost());
    	
    	if(!$form->isValid($this->getRequest()->getPost())){
    		$this->view->form = $form;
    		return;
    	}
    	
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