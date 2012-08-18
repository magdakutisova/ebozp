<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Index';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {          	
    	$diary = new Application_Model_DbTable_Diary();
    	
    	$messages = $diary->getDiary();
    	
    	if ($this->getRequest()->isPost() && in_array('Filtrovat', $this->getRequest()->getPost())){
    		$formData = $this->getRequest()->getPost();
    		$this->_helper->diaryFiltering($messages, $formData['users'], $formData['subsidiaries']);
    	}
    	else{
    		$this->_helper->diaryFiltering($messages, 0, 0);
    	}
    	
    	$formSearch = new Application_Form_Search();
    	$this->view->formSearch = $formSearch;
    	if ($this->getRequest()->isPost() && in_array('Hledat', $this->getRequest()->getPost())){
    		$formData = $this->getRequest()->getPost();
    		if($formSearch->isValid($formData)){
    			$query = $formSearch->getValue('query');
    			$this->_helper->diarySearch($query);
    		}
    	}
    }


}



