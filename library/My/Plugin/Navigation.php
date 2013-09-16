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
			$subsidiariesDb = new Application_Model_DbTable_Subsidiary();
			$subsidiaries = $subsidiariesDb->getSubsidiariesComplete($clientId);
			$acl = new My_Controller_Helper_Acl();
			$subIds = array();
			foreach($subsidiaries as $subsidiary){
				// pokud je pobocka pouze sidlo, preskoci se
				if ($subsidiary->getHqOnly()) continue;
				
				if($acl->isAllowed($user, $subsidiary)){
					$active = '';
					if(!$subsidiary->getActive()){
						$active = ' (neaktivní)';
					}				 
					$hqOnly = '';
					if($subsidiary->getHqOnly()){
						$hqOnly = ' (pouze sídlo)';
					}
					$subs->addPage(array(
						'label' => sprintf("%s, %s, %s", $subsidiary->getSubsidiaryName(), $subsidiary->getSubsidiaryTown(), $subsidiary->getSubsidiaryStreet()),
						'route' => 'subsidiaryIndex',
						'resource' => 'subsidiary',
						'privilege' => 'index',
						'params' => array(
							'clientId' => $clientId,
							'subsidiary' => $subsidiary->getIdSubsidiary(),
						)
					));
					$subIds[] = $subsidiary->getIdSubsidiary();
				}
			}
			
			$defSubId = $subIds[0];
			if($subsidiariesDb->getSubsidiary($defSubId)->getHqOnly() && count($subIds) > 1){
				$defSubId = $subIds[1];
			}
			
			$pages = $clientNavigation->findAllBy('subsidiaryId', 'subsidiaryId');
			foreach ($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $defSubId));
			}
			
			$pages = $clientNavigation->findAllBy('filter', 'filter');
			foreach($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $defSubId, 'filter' => 'vse'));
			}
			$pages = $clientNavigation->findAllBy('filter2', 'filter2');
			foreach($pages as $page){
				$page->setParams(array('clientId' => $clientId, 'subsidiaryId' => $defSubId, 'filter' => 'podle-pracovist'));
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