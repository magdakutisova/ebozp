<?php
class Audit_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	public function _initRoutes() {
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
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
		
		// odeslani auditu technikem
		$router->addRoute(
				"audit-technic-submit",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/technic/submit",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "techsubmit"
						))
		);
		
		// odeslani auditu koordinatorem
		$router->addRoute(
				"audit-coordinator-submit",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/coordinator/submit",
						array(
								"module" => "audit",
								"controller" => "audit",
								"action" => "coordsubmit"		
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
		
		// nova neshoda bez zavislosti na zaznamu
		$router->addRoute(
				"audit-mistake-createalone",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/create-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "createalone"))
		);
		
		// nova neshoda bez zavislosti na zaznamu - vytvoreni
		$router->addRoute(
				"audit-mistake-postalone",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/post-mistake",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "postalone"))
		);
		
		// zobrazit vypis neshod, ktere nejsou vazany k zadne otazce auditu s motnosti pridani
		$router->addRoute(
				"audit-mistakes-auditlist",
				new Zend_Controller_Router_Route("/klient/:clientId/audit/:auditId/ungrouped-mistakes",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "auditlist"))
		);
		
		// zobrazit jednu neshodu
		$router->addRoute(
				"audit-mistake-get",
				new Zend_Controller_Router_Route("/klient/:clientId/mistake/:mistakeId",
						array("module" => "audit",
								"controller" => "mistake",
								"action" => "get"))
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
		
		// potvrzeni neshody
		$router->addRoute(
				"audit-mistake-submit-json",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/mistake/:mistakeId/submit.json",
						array(
								"module" => "audit",
								"controller" => "mistake",
								"action" => "submit.json"
						))
		);
		
		// odpotvrzeni neshody
		$router->addRoute(
				"audit-mistake-unsubmit-json",
				new Zend_Controller_Router_Route("/klient/:clientId/pobocka/:subsidiaryId/audit/:auditId/mistake/:mistakeId/unsubmit.json",
						array(
								"module" => "audit",
								"controller" => "mistake",
								"action" => "unsubmit.json"
						))
		);
	}
}