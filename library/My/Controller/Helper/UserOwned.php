<?php
class My_Controller_Helper_UserOwned implements Zend_Acl_Assert_Interface{
	/**
	 * @param Zend_Acl $acl
	 * @param Zend_Acl_Role_Interface $role
	 * @param Zend_Acl_Resource_Interface $resource
	 * @param unknown_type $privilege
	 */
	public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null) {
		if (!$resource instanceof Application_Model_UserOwnedInterface){
			//Zend_Debug::dump($role);
			//Zend_Debug::dump($resource);
			//die();
			throw new Exception('UserOwnedInterface not implemented');
		}
		
		$auth = Zend_Auth::getInstance();
		if(!$auth->hasIdentity()){
			return false;
		}
		$user = new Application_Model_User($auth->getIdentity());
		
		return $resource->isOwnedByUser($user);
	}

	
	
	
}