<?php
class My_Controller_Helper_DiaryMessages extends Zend_Controller_Action_Helper_Abstract{
	
	private $request;
	private $view;
	private $controllerName;
    private $diaryDb;

    private $_messages = array();
	
	public function __construct(){
		$this->request = Zend_Controller_Front::getInstance()->getRequest();
		$this->view = Zend_Layout::getMvcInstance()->getView();
		$this->controllerName = $this->request->getControllerName();
        $this->diaryDb = new Application_Model_DbTable_Diary();
	}
	
	public function direct(){
		$this->view->addHelperPath('My/View/Helper', 'My_View_Helper');
    	
    	$formMessages = new Application_Form_DiaryMessages();
    	
    	if($this->controllerName != 'subsidiary'){
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
    	}
    	else{
    		$formMessages->removeElement('tree');
    	}
    	
    	$this->view->formMessages = $formMessages;
    	
    	if($this->request->isPost() && in_array('Odeslat', $this->request->getPost())){
    		$formData = $this->request->getPost();
    		
            $identity = Zend_Auth::getInstance()->getIdentity();
            $username = $identity->name;
            
            if (in_array($identity->role, array(My_Role::ROLE_ADMIN, My_Role::ROLE_COORDINATOR, My_Role::ROLE_TECHNICIAN))) {
                $username .= " (G7)";
            } else {
                $username .= " (klient)";
            }
            
    		$diary = new Application_Model_DbTable_Diary();
    		if ($formMessages->isValid($formData)){
    			$recipients = null;
    			if($this->controllerName != 'subsidiary'){
    				$recipients = $formData['tree'];
    			}
    			else{
    				$recipients[] = $this->request->getParam('subsidiary');
    			}
    			$message = $formData['message'];
                
                $tableMessages = new Application_Model_DbTable_DiaryMessage();
                $clientId = $this->request->getParam("clientId");
                
    			foreach($recipients as $recipient){
    				if ($recipient != 0){
                        $msg = $username . ' zaslal tuto zprávu: "' . $message . '"';
                        $this->_messages[] = array(
                            "msg" => $msg, 
                            "subsidiaryId" => $recipient, 
                            "author" => $username
                            );
                        
                        if ($clientId) $tableMessages->createMessage($clientId, $recipient, $message);
    				}
    			}
                $this->save();

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
    
    public function save() {
        // pokud nejsou k dispozici zadne zpravy k ulozeni, nic se delat nebude
        if (!$this->_messages) return;
        
        $sql = "insert into " . $this->diaryDb->info("name") . " (`message`, `subsidiary_id`, `author`) values ";
        $adapter = Zend_Db_Table::getDefaultAdapter();

        $records = array();
        
        foreach ($this->_messages as $message) {
            $records[] = sprintf("(%s, %d, %s)", $adapter->quote($message["msg"]), $message["subsidiaryId"], $adapter->quote($message["author"]));
        }
        
        $sql .= implode(",", $records);
        $adapter->query($sql);
        
        $this->_messages = array();
    }
    
    public function postDispatch() {
        parent::postDispatch();
        
        $this->save();
    }
	
	public function sendEmails($recipients, $username, $message){
		//adresy příjemců
		$addresses = array();
		$subsidiaries = new Application_Model_DbTable_Subsidiary();
		foreach($recipients as $subsidiaryId){
			$newAddresses = $subsidiaries->getContactEmails($subsidiaryId);
			$addresses = array_merge($addresses, $newAddresses);
		}
		$addresses = array_unique($addresses);
		$addresses[] = 'guardian@guard7.cz';
		
		$settings = array(
				'ssl' => 'ssl',
				'port' => 465,
				'auth' => 'login',
				'username' => 'guardian@guard7.cz',
				'password' => 'guardianG7',
				);
		$transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $settings);
		

        $mail = new Zend_Mail('utf-8');
        $mail->setFrom('guardian@guard7.cz', 'Guardian');

		foreach($addresses as $to){
			$mail->addTo($to);
		}

        $mail->setSubject('Guardian: Nová zpráva v bezpečnostním deníku');
        $mail->setBodyHtml('Uživatel ' . $username . ' zaslal následující zprávu do bezpečnostního deníku:<br/><br/>'
                . $message);
        $mail->send($transport);
	}
	
}