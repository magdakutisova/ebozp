<?php
class Audit_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	public function _initRoutes() {
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		// seznam auditu
		$router->addRoute(
				"audit-list",
				new Zend_Controller_Router_Route("/klient/:clientId/adits",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "list"
						))
		);
		
		// route pro post auditu
		$router->addRoute(
				"audit-post",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/post",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "post"
						))
		);
		
		// klonovani autidu
		$router->addRoute(
				"audit-clone",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/clone",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "clone"
						))
		);
		
		// route pro update auditu
		$router->addRoute(
				"audit-put",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/put",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "put"
						))
		);
		
		// odesle audit ke zpracovani vyssi instanci
		$router->addRoute(
				"audit-submit",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/submit",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "submit"
						))
		);
		
		// routa pro fill dotazniku
		$router->addRoute(
				"audit-form-fill",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/form/:formId/edit/:page",
					array(
							"module" => "audit",
							"controller" => "form",
							"action" => "fill",
							"page" => 1
					))
		);
		
		// routa pro ulozeni dotazniku
		$router->addRoute(
				"audit-form-save",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/form/:formId/save/:page",
						array(
								"module" => "audit",
								"controller" => "form",
								"action" => "save"
						))
		);
		
		// vytvoreni instance formulare
		$router->addRoute(
				"audit-form-instance",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/append-form",
						array(
								"module" => "audit",
								"controller" => "form",
								"action" => "instance"
						))
		);
		
		// zobrazeni formulare
		$router->addRoute(
				"audit-form-get",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/form/:formId/get",
						array(
								"module" => "audit",
								"controller" => "form",
								"action" => "get"
						))
		);
		
		// routa na vyplneni dotazniku auditu ze strany technika
		$router->addRoute(
				"audit-edit",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/edit",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "edit"
						)
				)
		);
		
		// routa na vytvoreni dotazniku
		$router->addRoute(
				"audit-create",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/create",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "create"
						)
				)
		);
		
		// routa na zobrazeni auditu (read-only)
		$router->addRoute(
				"audit-get",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/get",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "get"
						)
				)
		);
		
		// nova neshoda bez zavislosti na zaznamu
		$router->addRoute(
				"audit-mistake-createalone1",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/select-subsidiary",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "createalone1"))
		);
		
		// nova neshoda bez zavislosti na zaznamu
		$router->addRoute(
				"audit-mistake-createalone2",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/create-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "createalone2"))
		);
		
		// nova neshoda bez zavislosti na zaznamu - vytvoreni
		$router->addRoute(
				"audit-mistake-postalone",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/post-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "postalone"))
		);
		
		// zobrazeni vypisu neshod
		$router->addRoute(
				"audit-mistakes-index",
				new Zend_Controller_Router_Route("/klient/:clientId/mistakes",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "index"))
		);
		
		// zobrazit jednu neshodu
		$router->addRoute(
				"audit-mistake-get",
				new Zend_Controller_Router_Route("/klient/:clientId/mistake/:mistakeId",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "get"))
		);
		
		$router->addRoute(
				"audit-mistake-get-html",
				new Zend_Controller_Router_Route("/klient/:clientId/mistake/:mistakeId/html",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "get.html"))
		);
		
		// edituje neshodu
		$router->addRoute(
				"audit-mistake-edit",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "edit"))
		);
		
		// edituje neshodu bez layoutu
		$router->addRoute(
				"audit-mistake-edit-html",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId/html",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "edit.html"))
		);
		
		// ulozi zmenenou neshodu
		$router->addRoute(
				"audit-mistake-put",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId/put",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "put"))
		);
		
		// ulozi zmenenou neshodu jako html
		$router->addRoute(
				"audit-mistake-put-html",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId/put/html",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "put.html"))
		);
		
		// smaze neshodu
		$router->addRoute(
				"audit-mistake-delete",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId/delete",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "delete"))
		);
		
		// smaze neshodu v plovoucim okne
		$router->addRoute(
				"audit-mistake-delete-html",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/mistake/:mistakeId/delete/html",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "delete.html"))
		);
		
		// nastaveni stavu skupiny neshod
		$router->addRoute(
				"audit-mistake-setstates",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/mistakes/setstatus",
						array(
								"module" => "audit",
								"controller" => "mistake",
								"action" => "setstatus.json"
						))
		);
	}
}
