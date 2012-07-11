<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDoctype() {
		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->doctype( 'XHTML1_STRICT' );
	}
	
	protected function _initNavigation(){
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
		
		$navigation = new Zend_Navigation($config);
		$view->navigation($navigation);
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
	}

}

