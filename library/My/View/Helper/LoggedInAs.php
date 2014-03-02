<?php
class My_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract{
	
	public function loggedInAs(){
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()){
			$username = $auth->getIdentity()->username;
			$role = $auth->getIdentity()->role;
			$rolename = My_Role::getRoleName($role);
			
			$passwordUrl = $this->view->url(array(), 'userPassword');
			$logoutUrl = $this->view->url(array(), 'userLogout');
			
			return '<p class="no-margin"><span class="bold">Přihlášen: </span>' . $username
				. '</p><p class="no-margin"><span class="bold">Práva: </span>' . $rolename
				. '</p><p class="no-margin"><a href="' . $logoutUrl
				. '">Odhlásit se</a></p>';
		}
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		if ($controller == 'user' && $action == 'login'){
			return '';
		}
		$loginUrl = $this->view->url(array(), 'userLogin');
		return '<a href="' . $loginUrl . '">Přihlásit se</a>';
	}
	
}