<?php
class Document_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	protected function _initRoutes() {
		
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		// index - presmerovava na domosvsky adresar
		$router->addRoute(
				"document-index",
				new Zend_Controller_Router_Route("/klient/:clientId/documents",
						array(
								"module" => "document",
								"controller" => "index",
								"action" => "index"
						))
		);
		
		// domovsky adresar - presmerovava na get adresare
		$router->addRoute(
				"document-directory-index",
				new Zend_Controller_Router_Route("/klient/:clientId/directories",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "index"
						))
		);
		
		// zobrazi adresar
		$router->addRoute(
				"document-directory-get",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/get",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "get"
						))
		);
		
		// vytvori adresar
		$router->addRoute(
				"document-directory-post",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/create-child",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "post"
						))
		);
		
		// smaze adresar
		$router->addRoute(
				"document-directory-delete",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/delete",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "delete"
						))
		);
		
		// vytvori soubor a zapise ho do adresare
		$router->addRoute(
				"document-post",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/post-document",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "post"
						))
		);
	}
}