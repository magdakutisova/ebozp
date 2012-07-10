<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initDoctype() {
		$this->bootstrap( 'view' );
		$view = $this->getResource( 'view' );
		$view->doctype( 'XHTML1_STRICT' );
	}
	
	protected function _initRouter(array $options = array()) {
		
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		$router->addRoute(
			'coordinator',
			new Zend_Controller_Router_Route('coordinator',
											 array('controller' => 'coordinator',
											 	   'action' => 'index'))
		);
	}

}

