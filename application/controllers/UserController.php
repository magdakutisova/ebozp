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
    	$this->view->subtitle = 'Administrace uživatelů';
    	
		$form = new Application_Form_Register ();
		$this->view->form = $form;
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$username = $form->getValue ( 'username' );
				$password = $form->getValue ( 'password' );
				$confirmPassword = $form->getValue ( 'confirmPassword' );							
				$role = $form->getValue ( 'role' );	
				
				$users = new Application_Model_DbTable_User ();
				$user = $users->getByUsername ( $username );
				if ($user == null) {
					$salt = $this->generateSalt ();
					$password = $this->encrypt ( $password, $salt );
					$salt = base64_encode($salt);
					$users->addUser ( $username, $password, $salt, $role );
					$this->_helper->FlashMessenger ( 'Uživatel <strong>' . $username . '</strong> vytvořen' );
					$this->_helper->redirector->gotoRoute ( array (), 'userRegister' );
				} else {
					$this->_helper->FlashMessenger ( 'Uživatel s tímto uživatelským jménem již existuje, zvolte prosím jiné.' );
					$this->_helper->redirector->gotoRoute(array(), 'userAdmin');
				}
			}
		}
    }

    public function rightsAction()
    {
    	$this->view->subtitle = 'Administrace uživatelů';
    }

    public function deleteAction()
    {
    	$this->view->subtitle = 'Administrace uživatelů';
    	
    	$users = new Application_Model_DbTable_User();
    	$formContent = $users->getUsernames();
    	
    	if ($formContent != 0){
    		$form = new Application_Form_Select ();
			$form->select->setMultiOptions ( $formContent );
			$form->select->setLabel('Vyberte uživatele:');
			$form->submit->setLabel('Smazat');
			$form->submit->setAttrib('onClick', 'return confirm("Opravdu chcete uživatele smazat?")');
			$this->view->form = $form;
			   	
    		$this->renderScript ( 'user/delete.phtml' );
    		
    		if ($this->getRequest ()->isPost ()) {
				$formData = $this->getRequest ()->getPost ();
				if ($form->isValid($formData)){
					if ($this->getRequest ()->getMethod () == 'POST') {
						$userId = $this->getRequest ()->getParam ( 'select' );
						$users->deleteUser($userId);
						$this->_helper->FlashMessenger('Uživatel byl smazán.');
						$this->_helper->redirector->gotoRoute(array(), 'userDelete');
					} else {
						throw new Zend_Controller_Action_Exception ( 'Nekorektní pokus o smazání klienta.', 500 );
					}
				}
    		}
    	}
    	else{
    		$form = '<p>Neexistují žádní uživatelé, kontaktujte podporu.</p>';
    		$this->view->form = $form;
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

    public function passwordAction()
    {
    	$this->view->subtitle = 'Změna hesla';
        $form = new Application_Form_Password();
        $this->view->form = $form;
        
        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		$username = Zend_Auth::getInstance()->getIdentity()->username;
        		$oldPass = $form->getValue('oldPassword');
        		$newPass = $form->getValue('newPassword');
        		
        		$users = new Application_Model_DbTable_User();
    			$user = $users->getByUsername($username);
    			$salt = base64_decode($user['salt']);
    			$password = $this->encrypt($oldPass, $salt);
    			$dbPass = $user['password'];

    			if ($password == $dbPass){
        			$salt = $this->generateSalt();
        			$password = $this->encrypt($newPass, $salt);
        			$salt = base64_encode($salt);
        			$users->updatePassword ( $username, $password, $salt);
					$this->_helper->FlashMessenger ( 'Heslo změněno.' );
					$this->_helper->redirector->gotoRoute ( array (), 'home' );
        		}
        		else{
        			$this->_helper->FlashMessenger ( 'Zadal(a) jste špatné původní heslo.' );
					$this->_helper->redirector->gotoRoute ( array (), 'userPassword' );
        		}
        	}
        }
    }
}









