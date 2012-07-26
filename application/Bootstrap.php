<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDoctype() {
		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->doctype( 'XHTML1_STRICT' );
	}
	
	protected function _initAutoload() {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('My_');
    }
    
    protected function _initSession(){
    	Zend_Session::start(true);
    	new Zend_Session_Namespace();
    }
    
    protected function _initSearch(){ 
    	Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    }
    
	protected function _initNavigation(){
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
		$clientConfig = new Zend_Config_Xml(APPLICATION_PATH . '/configs/clientNavigation.xml', 'nav');
		$navigation = new Zend_Navigation($config);
		$clientNavigation = new Zend_Navigation($clientConfig);
		Zend_Registry::set('ClientNavigation', $clientNavigation);
		$view->navigation($navigation);
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
	
	protected function _initRouter(array $options = array()){
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		$router->addRoute(
			'userLogin',
			new Zend_Controller_Router_Route('/',
											 array('controller' => 'user',
											 	   'action' => 'login'))
		);
		
		$router->addRoute(
			'home',
			new Zend_Controller_Router_Route('domu',
											 array('controller' => 'index',
											 	   'action' => 'home'))
		);
		
		$router->addRoute(
			'userRegister',
			new Zend_Controller_Router_Route('registrace',
											array('controller' => 'user',
												'action' => 'register'))
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
		
		
		
	}

}

