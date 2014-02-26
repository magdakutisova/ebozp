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
        
        $identity = Zend_Auth::getInstance()->getIdentity();
        
        if ($identity && $identity->role != My_Role::ROLE_GUEST) {
            $navigation->removePage(0);     // HOVNO KOD - AZ BUDE CAS, TAK PRDELAT
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
            
            $tableSubs = new Application_Model_DbTable_Subsidiary();
			
            // pokud je id pobocky stale null, nacte se vychozi pobocka klienta
            if (is_null($subsidiaryId)) {
                $sub = $tableSubs->fetchRow(array(
                    "client_id = ?" => $clientId,
                    "hq"
                ));
                
                $subsidiaryId = $sub->id_subsidiary;
            } else {
                $sub = $tableSubs->find($subsidiaryId);
            }
            
            // pokud je pobocka pouze sidlo, pak se skryji nektere prcky navigace
            if ($sub instanceof Zend_Db_Table_Rowset_Abstract) {
                $subsidiary = $sub->current();
            } else {
                $subsidiary = $sub;
            }
            
            if ($subsidiary->hq_only) {
                $clientNavigation->removePage(2);
                $clientNavigation->removePage(3);
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
            
            $page = $clientNavigation->findBy("TYPE", "documentation")->setParams(array(
                "clientId" => $clientId,
                "subsidiaryId" => $subsidiaryId,
                "TYPE" => "documentation",
                "subId" => $subsidiaryId
            ));
            
            $page = $clientNavigation->findBy("TYPE", "record")->setParams(array(
                "clientId" => $clientId,
                "subsidiaryId" => $subsidiaryId,
                "TYPE" => "record",
                "subId" => $subsidiaryId
            ));
            
		}
        
        // nacteni upper panelu
        $configUpper = new Zend_Config_Xml(APPLICATION_PATH . '/configs/upperPanelNavigation.xml', 'nav');
        $navigationUpper = new Zend_Navigation($configUpper);
        Zend_Registry::set("UpperPanel", $navigationUpper);
        
        if ($clientId) {
            if ($sub instanceof Zend_Db_Table_Rowset_Abstract) {
                $sub = $sub->current();
            }

            $pages = $navigationUpper->findAllBy("clientId", "clientId");
            $newParams = array("clientId" => $clientId, "subsidiaryId" => $subsidiaryId, "subId" => $subsidiaryId);

            foreach ($pages as $page) {
                $page->setParams(array_merge($page->getParams(), $newParams));
            }
        }
        
        // nastaveni odkazu na elearning
        $elearningElement = $navigationUpper->findOneBy("uri", "elearning");
        $elearningConfig = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOption("elearning");
        $url = $elearningConfig["baseUri"];
        
        if ($identity && $identity->elearning_user_id) {
            try {
            $url .= "/user/dsign?key=";
            $adapter = Zend_Db::factory($elearningConfig["db"]["adapter"], $elearningConfig["db"]["params"]);
            
            $sql = sprintf("SELECT * FROM users WHERE id = %d", $identity->elearning_user_id);
            $user = $adapter->query($sql)->fetch(Zend_Db::FETCH_OBJ);
            
            // vytvoreni klice pro prihlasovaci link
            $firstPart = sha1($user->salt . $user->password);

            // druha cast klice - login prevedeny do hexadecimalni podoby
            $secondPart = "";

            for ($i = 0; $i < strlen($user->login); $i++) {
                $secondPart .= dechex(ord($user->login[$i]));
            }

            $key = $firstPart . $secondPart;
            
            $url .= $key;
            } catch (Zend_Exception $e) {
                // nic se zde nedela
            }
        }
        
        $elearningElement->setUri($url);
		
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