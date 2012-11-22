<?php
class My_Plugin_Acl extends Zend_Controller_Plugin_Abstract{
	
	private $_acl;

	public function __construct(Zend_Acl $acl){
		$this->_acl = $acl; 
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request){
		if (Zend_Auth::getInstance()->hasIdentity()){
			$role = Zend_Auth::getInstance()->getIdentity()->role;
		}
		else{
			$role = My_Role::ROLE_GUEST;
		}
		
		$resource = $request->getControllerName();
		$action = $request->getActionName();
		
		// zahrnuti modulu do ACL
		$module = $request->getModuleName();
		$defModule = Zend_Controller_Front::getInstance()->getDefaultModule();
		
		// vyhodnoceni modulu
		if (!(empty($module) || $module == $defModule)) {
			$resource = $module . ":" . $resource;
		}
		
		if(!$this->_acl->isAllowed($role, $resource, $action)){
			if ($role == My_Role::ROLE_GUEST){
				$request->setControllerName('user')->setActionName('login')->setModuleName("default");
			}
			else{
				$request->setControllerName('error')->setActionName('denied');
			}
		}
	}
	
}