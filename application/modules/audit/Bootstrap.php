<?php
class Audit_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	public function _initRoutes() {
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		// routa na vyplneni dotazniku auditu ze strany technika
		$router->addRoute(
				"audit-fill",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/fill",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "fill"
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
		
		// routa na zobrazeni auditu klientovi pro potvrezeni
		$router->addRoute(
				"audit-review",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/review",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "review"
						)
				)
		);
		
		// routa na prehled auditu technika
		$router->addRoute(
				"audit-list-technic",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/list/technic",
						array("module" => "audit",
								"controller" => "audit",
								"action" => "techlist"))
		);
		
		// routa na prehled auditu technika
		$router->addRoute(
				"audit-list-client",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/list/client",
						array("module" => "audit",
								"controller" => "audit",
								"action" => "clientlist"))
		);
		
		// routa na prehled auditu koordinatora
		$router->addRoute(
				"audit-list-coord",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/list/coordinator",
						array("module" => "audit",
								"controller" => "audit",
								"action" => "coordlist"))
		);
		
		// vytvoreni neshod koordinatorem
		$router->addRoute(
				"audit-mistake-create",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/record/:recordId/create-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "create"))
		);
		
		// nova neshoda
		$router->addRoute(
				"audit-mistake-post",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/record/:recordId/post-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "post"))
		);
		
		// nova neshoda
		$router->addRoute(
				"audit-mistake-attach",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/record/:recordId/attach-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "attach"))
		);
		
		// zobrazit jednu neshodu
		$router->addRoute(
				"audit-mistake-get",
				new Zend_Controller_Router_Route("/klient/:clientId/mistake/:mistakeId",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "get"))
		);
	}
}