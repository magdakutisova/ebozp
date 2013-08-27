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
    	if($multiOptions != null){
    		$formMessages->tree->setMultiOptions($multiOptions);
    	}
    	else{
    		$formMessages->removeElement('tree');
    		$formMessages->addElement('hidden', 'tree', array(
    				'label' => 'Nelze zasílat zprávy neaktivním pobočkám.',
    				'order' => 1,
    				));
    		$formMessages->getElement('message')->setAttrib('disabled', true);
    		$formMessages->getElement('send')->setAttrib('disabled', true);
    	}
    	
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
    			$this->sendEmails($recipients, $username, $message);
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
	
	private function sendEmails($recipients, $username, $message){
		//TODO přidat podklady@guard7.cz
		
		//adresy příjemců
		$addresses = array();
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		foreach($recipients as $subsidiaryId){
			$newAddresses = $subsidiaries->getContactEmails($subsidiaryId);
			$addresses = array_merge($addresses, $newAddresses);
		}
		$addresses = array_unique($addresses);
		
		$settings = array(
				'ssl' => 'ssl',
				'port' => 465,
				'auth' => 'login',
				'username' => 'guardian@guard7.cz',
				'password' => 'guardian',
				);
		$transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $settings);
		
		foreach($addresses as $to){
			$mail = new Zend_Mail('utf-8');
			$mail->setFrom('guardian@guard7.cz', 'Guardian');
			$mail->addTo($to);
			$mail->setSubject('Guardian: Nová zpráva v bezpečnostním deníku');
			$mail->setBodyHtml('Uživatel ' . $username . ' zaslal následující zprávu do bezpečnostního deníku:<br/><br/>'
					. $message
					. '<br/><br/>Na tuto zprávu, prosím, neodpovídejte.');
			$mail->send($transport);
		}
	}
	
}