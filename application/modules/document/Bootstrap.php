<?php
class Document_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	protected function _initDirs() {
		if (!defined("DOCUMENT_PATH_DIR"))
			define("DOCUMENT_PATH_DIR", __DIR__ . "/files");
	}
	
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
		
		// vytvori adresar
		$router->addRoute(
				"document-directory-put",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/put",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "put"
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
		
		// zobrazi soubor
		$router->addRoute(
				"document-get",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/get",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "get"
						))
		);
		
		// zobrazi soubor
		$router->addRoute(
				"document-version-get",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/version/:versionId/get",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "get"
						))
		);
		
		$router->addRoute(
				"document-version-download",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/version/:versionId/download",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "download"
						))
		);
		
		$router->addRoute(
				"document-version-upload",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/version/upload",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "upload"
						))
		);
		
		$router->addRoute(
				"document-put",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/put",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "put"
						))
		);
		
		$router->addRoute(
				"document-attach",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/attach-directory/",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "attach"
						))
		);
		
		$router->addRoute(
				"document-dettach",
				new Zend_Controller_Router_Route("/klient/:clientId/file/:fileId/dettach-directory/:directoryId",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "detach"
						))
		);
		
		$router->addRoute(
				"document-directory-dettach",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/dettach-file/:fileId",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "detach"
						))
		);
		
		$router->addRoute(
				"document-mine",
				new Zend_Controller_Router_Route("/klient/:clientId/documents/mine",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "index"
						))
		);
		
		$router->addRoute(
				"document-trash",
				new Zend_Controller_Router_Route("/klient/:clientId/documents/mine/trash",
						array(
								"module" => "document",
								"controller" => "document",
								"action" => "trash"
						))
		);
		
		$router->addRoute(
				"document-multiupload",
				new Zend_Controller_Router_Route("/klient/:clientId/directory/:directoryId/multiupload",
						array(
								"module" => "document",
								"controller" => "directory",
								"action" => "multiupload"
						))
		);
		
		$router->addRoute(
				"document-documentation-index",
				new Zend_Controller_Router_Route("/klient/:clientId/documentation",
						array(
								"module" => "document",
								"controller" => "documentation",
								"action" => "index"
						))
		);
		
		$router->addRoute(
				"document-documentation-post",
				new Zend_Controller_Router_Route("/klient/:clientId/documentation/post",
						array(
								"module" => "document",
								"controller" => "documentation",
								"action" => "post"
						))
		);
		
		$router->addRoute(
				"document-documentation-put",
				new Zend_Controller_Router_Route("/klient/:clientId/documentation/:documentationId/put",
						array(
								"module" => "document",
								"controller" => "documentation",
								"action" => "put"
						))
		);
		
		$router->addRoute(
				"document-documentation-attach",
				new Zend_Controller_Router_Route("/klient/:clientId/documentation/:documentationId/attach",
						array(
								"module" => "document",
								"controller" => "documentation",
								"action" => "attach"
						))
		);
	}
}