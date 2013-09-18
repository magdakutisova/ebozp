<?php
class Deadline_Bootstrap extends Zend_Application_Module_Bootstrap {
	
	protected function _initRoutes() {
		
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		
		// rozcestnik lhut
		$router->addRoute(
				"deadline-index",
				new Zend_Controller_Router_Route("/klient/:clientId/subsidiary/:subsidiaryId/deadlines",
						array(
								"module" => "deadline",
								"controller" => "index",
								"action" => "index"
						))
		);
		
		// seznam lhut zamestnancu
		$router->addRoute(
				"deadline-create",
				new Zend_Controller_Router_Route("/klient/:clientId/deadline/create",
						array(
								"module" => "deadline",
								"controller" => "deadline",
								"action" => "create"
						))
		);
		
		// smazani lhuty
		$router->addRoute(
				"deadline-delete",
				new Zend_Controller_Router_Route("/klient/:clientId/deadline/:deadlineId/delete",
						array(
								"module" => "deadline",
								"controller" => "deadline",
								"action" => "delete"
						))
		);
		
		// seznam lhut zamestnancu
		$router->addRoute(
				"deadline-employees",
				new Zend_Controller_Router_Route("/klient/:clientId/subsidiary/:subsidiaryId/deadlines/employees",
						array(
								"module" => "deadline",
								"controller" => "index",
								"action" => "employee"
						))
		);
		
		// seznam lhut chemikalii
		$router->addRoute(
				"deadline-chemicals",
				new Zend_Controller_Router_Route("/klient/:clientId/subsidiary/:subsidiaryId/deadlines/chemicals",
						array(
								"module" => "deadline",
								"controller" => "index",
								"action" => "chemical"
						))
		);
		
		// seznam lhut chemikalii
		$router->addRoute(
				"deadline-others",
				new Zend_Controller_Router_Route("/klient/:clientId/subsidiary/:subsidiaryId/deadlines/others",
						array(
								"module" => "deadline",
								"controller" => "index",
								"action" => "other"
						))
		);
		
		// seznam lhut zarizeni
		$router->addRoute(
				"deadline-devices",
				new Zend_Controller_Router_Route("/klient/:clientId/subsidiary/:subsidiaryId/deadlines/devices",
						array(
								"module" => "deadline",
								"controller" => "index",
								"action" => "device"
						))
		);
	}
}