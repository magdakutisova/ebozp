<?php
class My_Controller_Helper_Diary extends Zend_Controller_Action_Helper_Abstract{
	
	private $request;
	private $view;
	
	public function __construct(){
		$this->request = Zend_Controller_Front::getInstance()->getRequest();
		$this->view = Zend_Layout::getMvcInstance()->getView();
	}
	
	public function direct($messages){
		$df = new My_Controller_Helper_DiaryFiltering();
		if ($this->request->isPost() && in_array('Filtrovat', $this->request->getPost())){
    		$formData = $this->request->getPost();
    		$df->direct($messages, $formData['users'], $formData['subsidiaries']);
    	}
    	else{
    		$df->direct($messages, 0, 0);
    	}
    	
    	$ds = new My_Controller_Helper_DiarySearch();
    	$formSearch = new Application_Form_Search();
    	$this->view->formSearch = $formSearch;
    	if ($this->request->isPost() && in_array('Hledat', $this->request->getPost())){
    		$formData = $this->request->getPost();
    		if($formSearch->isValid($formData)){
    			$query = $formSearch->getValue('query');
    			$ds->direct($query);
    		}
    	}
	}
	
}