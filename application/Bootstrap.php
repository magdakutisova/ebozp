<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDoctype() {
		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->doctype( 'XHTML1_STRICT' );
		$view->setEncoding('UTF-8');
	}
	
	protected function _initAutoload() {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('My_');
        Zend_Controller_Action_HelperBroker::addPrefix('My_Controller_Helper');
    }
    
    protected function _initSession(){
    	//Zend_Session::destroy();
    	Zend_Session::start(true);
    	new Zend_Session_Namespace();
    }
    
    protected function _initLog(){
    	if ($this->hasPluginResource('log')){
    		$r = $this->getPluginResource('log');
    		$log = $r->getLog();
    		Zend_Registry::set('log', $log);
    	}
    }
    
    protected function _initSearch(){ 
    	Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    }
    
	protected function _initNavigation(){
		Zend_Controller_Front::getInstance()->registerPlugin(new My_Plugin_Navigation());		
	}
	
	protected function _initTranslator(){
		$translator = new Zend_Translate(
			array(
				'adapter' => 'array',
				'content' => APPLICATION_PATH . '/../resources/languages',
				'locale' => 'cs',
			)
		);
		Zend_Validate_Abstract::setDefaultTranslator($translator);
	}
	
	protected function _initLocale(){
		$locale = new Zend_Locale('cs_CZ');
		Zend_Registry::set('Zend_Locale', $locale);
	}
	
	protected function _initAcl(){
		$acl = new My_Controller_Helper_Acl();
		$fc = Zend_Controller_Front::getInstance();
		$fc->registerPlugin(new My_Plugin_Acl($acl));
	}
	
	protected function _initRouter(array $options = array()){
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		$router->addRoute(
			'userLogin',
			new Zend_Controller_Router_Route('prihlaseni',
											 array('controller' => 'user',
											 	   'action' => 'login'))
		);
		
		$router->addRoute(
			'home',
			new Zend_Controller_Router_Route('/',
											 array('controller' => 'index',
											 	   'action' => 'index'))
		);
		
		$router->addRoute(
			'userRegister',
			new Zend_Controller_Router_Route('administrace-uzivatelu',
											array('controller' => 'user',
												'action' => 'register'))
		);
		
		$router->addRoute(
			'userRights',
			new Zend_Controller_Router_Route('administrace-uzivatelu/pridat-prava',
											array('controller' => 'user',
												'action' => 'rights'))
		);
		
		$router->addRoute(
			'userRevoke',
			new Zend_Controller_Router_Route('administrace-uzivatelu/odebrat-prava',
											array('controller' => 'user',
												'action' => 'revoke'))
		);
		
		$router->addRoute(
			'userDelete',
			new Zend_Controller_Router_Route('administrace-uzivatelu/smazat',
											array('controller' => 'user',
												'action' => 'delete'))
		);
		
		$router->addRoute(
			'userPassword',
			new Zend_Controller_Router_Route('zmena-hesla',
											array('controller' => 'user',
												'action' => 'password'))
		);
		
		$router->addRoute(
			'userLogout',
			new Zend_Controller_Router_Route('odhlaseni',
											array('controller' => 'user',
												'action' => 'logout'))
		);
		
		$router->addRoute(
			'clientList',
			new Zend_Controller_Router_Route('klienti',
											 array('controller' => 'client',
											 	   'action' => 'list'))
		);
		
		$router->addRoute(
			'clientFilter',
			new Zend_Controller_Router_Route('klienti/:mode',
											 array('controller' => 'client',
											 	   'action' => 'list'))
		);
		
		$router->addRoute(
			'clientNew',
			new Zend_Controller_Router_Route('novy-klient',
											 array('controller' => 'client',
											 	   'action' => 'new'))
		);
		
		$router->addRoute(
			'clientAdmin',
			new Zend_Controller_Router_Route('klient/:clientId/admin',
											 array('controller' => 'client',
											 	   'action' => 'admin'))
		);
		
		$router->addRoute(
			'clientIndex',
			new Zend_Controller_Router_Route('klient/:clientId',
											 array('controller' => 'client',
											 	   'action' => 'index'))
		);
		
		$router->addRoute(
			'clientEdit',
			new Zend_Controller_Router_Route('klient/:clientId/edit',
											array('controller' => 'client',
													'action' => 'edit'))
		);
		
		$router->addRoute(
			'clientDelete',
			new Zend_Controller_Router_Route('klient/:clientId/smazat',
											array('controller' => 'client',
													'action' => 'delete'))
		);
		
		$router->addRoute(
			'subsidiaryNew',
			new Zend_Controller_Router_Route('klient/:clientId/nova-pobocka',
											array('controller' => 'subsidiary',
													'action' => 'new'))
		);
		
		$router->addRoute(
			'subsidiaryIndex',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiary',
											array('controller' => 'subsidiary',
													'action' => 'index'))
		);
		
		$router->addRoute(
			'subsidiaryList',
			new Zend_Controller_Router_Route('klient/:clientId/seznam-pobocek',
											array('controller' => 'subsidiary',
													'action' => 'list'))
		);
		
		$router->addRoute(
			'subsidiaryEdit',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiary/editovat',
											array('controller' => 'subsidiary',
													'action' => 'edit'))
		);
		
		$router->addRoute(
			'subsidiaryDelete',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiary/smazat',
											array('controller' => 'subsidiary',
													'action' => 'delete'))
		);
		
		$router->addRoute(
			'workplaceNew',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/nove-pracoviste',
											array('controller' => 'workplace',
													'action' => 'new'))
		);
		
		$router->addRoute(
			'workplaceList',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/databaze-pracovist/:filter',
											array('controller' => 'workplace',
													'action' => 'list'))
		);
		
		$router->addRoute(
			'workplaceEdit',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/upravit-pracoviste/:workplaceId',
											array('controller' => 'workplace',
													'action' => 'edit'))
		);
		
		$router->addRoute(
			'workplaceDelete',
			new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/smazat-pracoviste/:workplaceId',
											array('controller' => 'workplace',
													'action' => 'delete'))
		);
		
		$router->addRoute(
				'positionNew',
				new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/nova-pracovni-pozice',
						array('controller' => 'position',
								'action' => 'new'))
				);
		
		$router->addRoute(
				'positionList',
				new Zend_Controller_Router_Route('klient/:clientId/pobocka/:subsidiaryId/databaze-pracovnich-pozic/:filter',
						array('controller' => 'position',
								'action' => 'list'))
				);
		
		$router->addRoute(
			'searchIndex',
			new Zend_Controller_Router_Route('indexace',
											 array('controller' => 'search',
											 	   'action' => 'index'))
		);
		
		$router->addRoute(
			'search',
			new Zend_Controller_Router_Route('vyhledavani',
											array('controller' => 'search',
												'action' => 'search'))
		);
		
		$router->addRoute(
			'printIndex',
			new Zend_Controller_Router_Route('klient/:clientId/zaznamy-k-tisku',
											array('controller' => 'print',
												'action' => 'index'))
		);
		
		$router->addRoute(
			'printDiary',
			new Zend_Controller_Router_Route('klient/:clientId/historie-bd',
											array('controller' => 'print',
												'action' => 'diary'))
		);
		
	}

}

