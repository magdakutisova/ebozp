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

		if($clientId != null){
			$subs = $clientNavigation->findOneBy('label', 'Pobočky');
			$username = Zend_Auth::getInstance()->getIdentity()->username;
			$users = new Application_Model_DbTable_User();
			$user = $users->getByUsername($username);
            
			$acl = new My_Controller_Helper_Acl();
            
            $subsidiaryId = $request->getParam("subsidiaryId", null);
            
            if (is_null($subsidiaryId)) {
                $subsidiaryId = $request->getParam("subsidiary", $subsidiaryId);
            }
			
            // pokud je id pobocky stale null, nacte se vychozi pobocka klienta
            if (is_null($subsidiaryId)) {
                $tableSubs = new Application_Model_DbTable_Subsidiary();
                $sub = $tableSubs->fetchRow(array(
                    "client_id = ?" => $clientId,
                    "hq"
                ));
                
                $subsidiaryId = $sub->id_subsidiary;
            }
            
			$pages = $clientNavigation->findAllBy('subsidiaryId', 'subsidiaryId');
			foreach ($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $subsidiaryId));
			}
			
			$pages = $clientNavigation->findAllBy('filter', 'filter');
			foreach($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'vse'));
			}
			$pages = $clientNavigation->findAllBy('filter2', 'filter2');
			foreach($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $subsidiaryId, 'filter' => 'podle-pracovist'));
			}
            
            // nacteni upper panelu
            $configUpper = new Zend_Config_Xml(APPLICATION_PATH . '/configs/upperPanelNavigation.xml', 'nav');		
            $navigationUpper = new Zend_Navigation($configUpper);
            Zend_Registry::set("UpperPanel", $navigationUpper);
            
            $pages = $navigationUpper->findAllBy("clientId", "clientId");
            $newParams = array("clientId" => $clientId, "subsidiaryId" => $subsidiaryId, "subId" => $subsidiaryId);
            
            foreach ($pages as $page) {
                $page->setParams(array_merge($page->getParams(), $newParams));
            }
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