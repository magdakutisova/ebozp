<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDoctype() {
		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->doctype( 'XHTML1_STRICT' );
	}
	
	protected function _initAutoload() {
        Zend_Loader::loadClass("Zend_Loader_Autoloader");
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        
        return $loader;
    }
    
//    protected function _initViewHelpers(){
//    	$this->bootstrap('view');
//    	$view = $this->getResource('view');
//    	$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
//    	
//    	$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
//		$viewRenderer->setView($view);
//		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
//    }
   
    protected function _initSession(){
    	Zend_Session::start(true);
    	new Zend_Session_Namespace();
    }
    
	protected function _initNavigation(){
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
		
		$navigation = new Zend_Navigation($config);
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
			'coordinator',
			new Zend_Controller_Router_Route('koordinator',
											 array('controller' => 'coordinator',
											 	   'action' => 'index'))
		);
		
		$router->addRoute(
			'clientList',
			new Zend_Controller_Router_Route('klienti',
											 array('controller' => 'client',
											 	   'action' => 'list'))
		);
		
		$router->addRoute(
			'clientSearch',
			new Zend_Controller_Router_Route('hledej-klienta',
											 array('controller' => 'client',
											 	   'action' => 'search'))
		);
		
		$router->addRoute(
			'clientNew',
			new Zend_Controller_Router_Route('novy-klient',
											 array('controller' => 'client',
											 	   'action' => 'new'))
		);
		
		$router->addRoute(
			'clientAdmin',
			new Zend_Controller_Router_Route('klienti/:clientId',
											 array('controller' => 'client',
											 	   'action' => 'admin'))
		);
		
		$router->addRoute(
			'clientEdit',
			new Zend_Controller_Router_Route('klienti/:clientId/edit',
											array('controller' => 'client',
													'action' => 'edit'))
		);
		
		$router->addRoute(
			'subsidiaryNew',
			new Zend_Controller_Router_Route('klienti/:clientId/nova-pobocka',
											array('controller' => 'subsidiary',
													'action' => 'new'))
		);
		
		$router->addRoute(
			'subsidiaryList',
			new Zend_Controller_Router_Route('klienti/:clientId/pobocky',
											array('controller' => 'subsidiary',
													'action' => 'list'))
		);
		
		$router->addRoute(
			'subsidiaryEdit',
			new Zend_Controller_Router_Route('klienti/:clientId/pobocky/:subsidiary',
											array('controller' => 'subsidiary',
													'action' => 'edit'))
		);
	}

}

