<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bootstrap
 *
 * @author petr
 */
class Planning_Bootstrap extends Zend_Application_Module_Bootstrap {
    
    protected function _initRoutes() {
        
        $this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		// index - presmerovava na domosvsky adresar
		$router->addRoute(
				"planning-client",
				new Zend_Controller_Router_Route("/planning/client/:clientId/status",
						array(
								"module" => "planning",
								"controller" => "index",
								"action" => "client"
						))
		);
        
    }
}

?>
