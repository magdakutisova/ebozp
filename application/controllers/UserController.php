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
		// nacteni seznamu uzivatelu
		$tableUsers = new Application_Model_DbTable_User();
		$users = $tableUsers->fetchAll(null, "name");
		
		$this->view->users = $users;
    }
    
    public function putAction() {
    	// vytvoreni formulare a nacteni dat z databaze
    	$form = new Application_Form_User();
    	$tableUsers = new Application_Model_DbTable_User();
    	$user = $tableUsers->find($this->_request->getParam("userId", 0))->current();
    	
    	if (!$user) throw new Zend_Db_Table_Exception("User not found");
    	
        // pripojeni k elearningovemu serveru
        $config = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOption("elearning");
        $adapter = Zend_Db::factory($config["db"]["adapter"], $config["db"]["params"]);
        
    	if (strtolower($this->_request->getMethod()) == "post") {
    		// kontrola validity
            try {
                if ($form->isValid($this->_request->getParams())) {
                    // nacteni dat
                    $data = $form->getValues(true);

                    // pokud je vyplneno prihlasovaci jmeno, kontrola existence v elearningu
                    if ($data["elearning_user_login"]) {
                        // nacteni radku
                        $sql = sprintf("SELECT id FROM %s WHERE login like %s", $adapter->quoteIdentifier("users"), $adapter->quote($data["elearning_user_login"]));
                        $userRow = $adapter->query($sql)->fetch();

                        if (!$userRow) {
                            $form->getElement("elearning_user_login")->setErrors(array("Uživatel " . $data["elearning_user_login"] . " nebyl nalezen"));
                            throw new Zend_Db_Table_Row_Exception("User not found");
                        }
                        
                        $data["elearning_user_id"] = $userRow["id"];
                    }

                    // zapis dat
                    $user->setFromArray($data);
                    $user->save();
                }
            } catch (Exception $e) {
                
            }
    	} else {
            // separace dat a pripadne nacteni jmena uzivatele
            $data = $user->toArray();
            
            if ($data["elearning_user_id"]) {
                $sql = sprintf("select * from %s where id = %s", $adapter->quoteIdentifier("users"), $adapter->quote($data["elearning_user_id"]));
                $userRow = $adapter->query($sql)->fetch();
                
                if ($userRow) {
                    $data["elearning_user_login"] = $userRow["login"];
                }
            }
            
    		$form->populate($data);
    	}
    	
    	$this->view->form = $form;
    	$this->view->user = $user;
    }

    public function registerAction()
    {
    	$this->view->subtitle = 'Administrace uživatelů';
    	
		$form = new Application_Form_Register ();
		$this->view->form = $form;
		
		if ($this->getRequest ()->isPost ()) {
			$formData = $this->getRequest ()->getPost ();
			if ($form->isValid ( $formData )) {
				$user = new Application_Model_User($formData);
				
				$users = new Application_Model_DbTable_User ();
				$existingUser = $users->getByUsername ( $user->getUsername() );
				if ($existingUser == null) {
					$salt = $this->generateSalt ();
					$password = $this->encrypt ( $user->getPassword(), $salt );
					$salt = base64_encode($salt);
					$user->setPassword($password);
					$user->setSalt($salt);
					$users->addUser ( $user );
					$this->_helper->FlashMessenger ( 'Uživatel <strong>' . $user->getUsername() . '</strong> vytvořen' );
					$this->_helper->redirector->gotoRoute ( array (), 'userRegister' );
				} else {
					$this->_helper->FlashMessenger ( 'Uživatel s tímto uživatelským jménem již existuje, zvolte prosím jiné.' );
					$this->_helper->redirector->gotoRoute(array(), 'userRegister');
				}
			}
		}	
    }

    public function rightsAction()
    {
    	$this->view->subtitle = 'Administrace uživatelů';
    	$form = new Application_Form_Rights();
    	$this->view->form = $form;
    	
    	if ($this->getRequest()->isPost()){
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)){
    			$users = $formData['users']['userCheckboxes'];
    			$subsidiaries = $formData['subsidiaries']['subsidiaryCheckboxes'];
   				$userSubsidiaries = new Application_Model_DbTable_UserHasSubsidiary();
   				foreach($users as $user){
   					foreach($subsidiaries as $subsidiary){
   						$userSubsidiaries->addRelation($user, $subsidiary);
    				}
    			}
    			$this->_helper->flashMessenger('Práva úspěšně přidělena.');
    			$this->_helper->redirector->gotoRoute(array(), 'userRights');
    		}    		
    	}	
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
        
        if ($this->getRequest()->isPost()){
        	$formData = $this->getRequest()->getPost();
        	if ($form->isValid($formData)){
        		if($this->_process($form->getValues())){
        			My_FileLogger::info("Uživatel " . $form->getValue('username') . " přihlášen.");
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
    	if(!$user){
    		$this->_helper->FlashMessenger('Uživatel se jménem "' . $values['username'] . '" neexistuje.');
    		$this->_helper->redirector->gotoRoute(array(), 'userLogin');
    	}
    	else{
	    	$password = $values['password'];
	    	$salt = base64_decode($user->getSalt());
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
    	My_FileLogger::info("Uživatel odhlášen.");
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
    			$salt = base64_decode($user->getSalt());
    			$password = $this->encrypt($oldPass, $salt);
    			$dbPass = $user->getPassword();

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

    public function revokeAction()
    {
        $this->view->subtitle = 'Administrace uživatelů';
    	$form = new Application_Form_Select();
    	$users = new Application_Model_DbTable_User();
    	$userList = $users->getUsernames();
    	$form->select->setMultiOptions($userList);
    	$form->select->setLabel('Vyberte uživatele:');
    	$form->submit->setLabel('Vybrat');
    	$this->view->form = $form;
    	
    	if ($this->getRequest()->isPost()){
    		$formData = $this->getRequest()->getPost();
    		if (array_key_exists('submit', $formData)){
    			if ($form->isValid($formData)){
    				$userId = $formData['select'];
    				
    				$subsidiaries = new Application_Model_DbTable_Subsidiary();
    				$subsidiaryList = $subsidiaries->getSubsidiaries(0, $userId);
    				
    				$form2 = new Application_Form_RightsSubsidiaries();
    				if($subsidiaryList != 0){
    					$form2->subsidiaries->setMultiOptions($subsidiaryList);
    					$form2->userId->setValue($userId);
    				}
    				else{
    					$form2 = "Uživatel nemá přiděleny žádné pobočky.";
    				}
    				$this->view->form2 = $form2;

    			}
    		}
    		if (array_key_exists('revoke', $formData)){
    			if($form->isValid($formData)){
    				$userId = $formData['userId'];
    				$subsidiaries = $formData['subsidiaries'];
    				
    				$userSubs = new Application_Model_DbTable_UserHasSubsidiary();
    				foreach ($subsidiaries as $subsidiary){
    					$userSubs->removeRelation($userId, $subsidiary);
    				}
    				$this->_helper->flashMessenger("Práva byla odebrána.");
    				$this->_helper->redirector->gotoRoute(array(), 'userRevoke');
    			}
    		}
    	}
    }


}











