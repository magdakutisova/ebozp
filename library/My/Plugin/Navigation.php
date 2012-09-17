<?php
class My_Plugin_Navigation extends Zend_Controller_Plugin_Abstract{
	
	function preDispatch(Zend_Controller_Request_Abstract $request){
		$view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');		
		$navigation = new Zend_Navigation($config);
		
		$clientConfig = new Zend_Config_Xml(APPLICATION_PATH . '/configs/clientNavigation.xml', 'nav');
		$clientNavigation = new Zend_Navigation($clientConfig);
		$pages = $clientNavigation->findAllBy('clientId', 'clientId');
		$clientId = $request->getParam('clientId');
		foreach ($pages as $page){
			$page->setParams(array('clientId' => $clientId));
		}
		Zend_Registry::set('ClientNavigation', $clientNavigation);
		
		$auth = Zend_Auth::getInstance();
		$role = "5";
		if ($auth->hasIdentity()){
			$role = $auth->getIdentity()->role;
		}
		$view->navigation()->setAcl(new My_Controller_Helper_Acl())->setRole($role);
		$view->navigation($navigation);
	}
	
}