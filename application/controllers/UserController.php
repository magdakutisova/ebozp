<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
		$this->view->title = 'Uživatel';
		$this->view->headTitle ( $this->view->title );
    }

    public function indexAction()
    {
		// action body
    }

    public function registerAction()
    {
		$this->view->subtitle = 'Registrace';
		
		$form = new Application_Form_Register ();
		$this->view->form = $form;
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$username = $form->getValue ( 'username' );
				$password = $form->getValue ( 'password' );
				$confirmPassword = $form->getValue ( 'confirmPassword' );							
				$role = $form->getValue ( 'role' );	
				
				//if ($password == $confirmPassword) {
					$users = new Application_Model_DbTable_User ();
					$user = $users->getByUsername ( $username );
					if ($user == null) {
						$salt = $this->generateSalt ();
						$password = $this->encrypt ( $password, $salt );
						$salt = base64_encode($salt);
						$users->addUser ( $username, $password, $salt, $role );
						$this->_helper->FlashMessenger ( 'Uživatel <strong>' . $username . '</strong> vytvořen' );
						$this->_helper->redirector->gotoRoute ( array (), 'home' );
					} else {
						$this->_helper->FlashMessenger ( 'Uživatel s tímto uživatelským jménem již existuje, zvolte prosím jiné.' );
						$this->_helper->redirector->gotoRoute(array(), 'userRegister');
					}
				//} else {
					//$this->_helper->FlashMessenger ( 'Hesla se neshodují.' );
					//$this->_helper->redirector->gotoRoute(array(), 'userRegister');
				//}
			}
		}
    }

    private function generateSalt()
    {
		$salt = mcrypt_create_iv ( 64 );
		return $salt;
    }

    private function encrypt($password, $salt)
    {
		$password = hash ( 'sha256', $salt . $password );
		return $password;
    }

    public function loginAction()
    {
        $form = new Application_Form_Login();
        $this->view->form = $form;
        $this->view->subtitle = 'Přihlášení';
        
        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		if($this->_process($form->getValues())){
        			$this->_helper->FlashMessenger ( 'Přihlášení bylo úspěšné.' );
        			$this->_helper->redirector->gotoRoute(array(), 'home');
        		}
        		else{
        			$this->_helper->FlashMessenger ( 'Chybné uživatelské jméno nebo heslo.' );
        			$this->_helper->redirector->gotoRoute(array(), 'userLogin');
        		}
        	}
        }
    }

    private function _process($values)
    {
    	$users = new Application_Model_DbTable_User();
    	$user = $users->getByUsername($values['username']);
    	$password = $values['password'];
    	$salt = base64_decode($user['salt']);
    	$password = $this->encrypt($password, $salt);
    	
    	$adapter = $this->_getAuthAdapter();
    	$adapter->setIdentity($values['username']);
    	$adapter->setCredential($password);
    	
    	$auth = Zend_Auth::getInstance();
    	$result = $auth->authenticate($adapter);
    	
    	if ($result->isValid()){
    		$loggedUser = $adapter->getResultRowObject();
    		$auth->getStorage()->write($loggedUser);
    		
    		return true;
    	}
    	return false;
    }

    private function _getAuthAdapter()
    {
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
    	
    	$authAdapter->setTableName('user')
    		->setIdentityColumn('username')
    		->setCredentialColumn('password');
    		
    	return $authAdapter;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector->gotoRoute(array(), 'userLogin');
    }


}







