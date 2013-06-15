<?php
class My_Controller_Helper_DiaryMessages extends Zend_Controller_Action_Helper_Abstract{
	
	private $request;
	private $view;
	
	public function __construct(){
		$this->request = Zend_Controller_Front::getInstance()->getRequest();
		$this->view = Zend_Layout::getMvcInstance()->getView();
	}
	
	public function direct(){
		$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
    	
    	$formMessages = new Application_Form_DiaryMessages();
    	
    	$addressBook = new My_Controller_Helper_AddressBook();
    	$multiOptions = $addressBook->direct();
    	$formMessages->tree->setMultiOptions($multiOptions);
    	
    	$this->view->formMessages = $formMessages;
    	
    	if($this->request->isPost() && in_array('Odeslat', $this->request->getPost())){
    		$formData = $this->request->getPost();
    		$username = Zend_Auth::getInstance()->getIdentity()->username;
    		$diary = new Application_Model_DbTable_Diary();
    		if ($formMessages->isValid($formData)){
    			$recipients = $formData['tree'];
    			$message = $formData['message'];
    			foreach($recipients as $recipient){
    				if ($recipient != 0){
    					$toSave = new Application_Model_Diary();
    					$toSave->setMessage('Zpráva od uživatele ' . $username . ': "' . $message . '"');
    					$toSave->setSubsidiaryId($recipient);
    					$toSave->setAuthor($username);
    					$diary->addMessage($toSave);
    				}
    			}
    			$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    			$flashMessenger->addMessage('Zpráva odeslána');
				$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
				$module = $this->request->getModuleName();
				$controller = $this->request->getControllerName();
				$action = $this->request->getActionName();
				$params = $this->request->getParams();
				$redirector->gotoSimple($action, $controller, $module, $params);
    		}
    	}  
	}
	
}