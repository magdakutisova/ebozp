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

		$router->addRoute(
				"planning-index",
				new Zend_Controller_Router_Route("/planning/clients",
						array(
								"module" => "planning",
								"controller" => "index",
								"action" => "index"
						))
		);
		
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
        
		
		// index - presmerovava na domosvsky adresar
		$router->addRoute(
				"planning-subsidiary",
				new Zend_Controller_Router_Route("/planning/client/:clientId/subsidiary/:subsidiaryId/detail",
						array(
								"module" => "planning",
								"controller" => "subsidiary",
								"action" => "index"
						))
		);
        
		// index - presmerovava na domosvsky adresar
		$router->addRoute(
				"planning-task-post",
				new Zend_Controller_Router_Route("/planning/client/:clientId/subsidiary/:subsidiaryId/create-task",
						array(
								"module" => "planning",
								"controller" => "task",
								"action" => "post"
						))
		);
        
		// index - presmerovava na domosvsky adresar
		$router->addRoute(
				"planning-task-put",
				new Zend_Controller_Router_Route("/planning/client/:clientId/subsidiary/:subsidiaryId/task/:itemId/edit",
						array(
								"module" => "planning",
								"controller" => "task",
								"action" => "put"
						))
		);
        
    }
}

?>
