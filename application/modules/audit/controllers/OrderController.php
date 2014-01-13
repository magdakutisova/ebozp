<?php
class Audit_OrderController extends Zend_Controller_Action {
	
	public function init() {
		// zapsani helperu
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function editHtmlAction() {
		$orderId = $this->_request->getParam("orderId", 0);
		$order = self::findOrder($orderId);
		
		// vytvoreni a nastaveni formulare
		$form = new Audit_Form_Order();
		$form->populate($order->toArray());
		
		$form->getElement("is_finished")->setValue($order->finished_at ? 1 : 0);
		
		$form->setAction(sprintf("/audit/order/put.html?orderId=%s", $orderId));
		
		$this->view->order = $order;
		$this->view->form = $form;
	}
	
	public function indexAction() {
		// nacteni filtracnich podminek
		$showFinished = $this->_request->getParam("showFinished", 0);
		$showActual = $this->_request->getParam("showActive", 1);
		
		$tableOrders = new Audit_Model_Orders();
		$orders = $tableOrders->findOrders($showFinished, $showActual);
		
		$this->view->orders = $orders;
	}
	
	public function putHtmlAction() {
		// nacteni objednavky
		$orderId = $this->_request->getParam("orderId", 0);
		$order = self::findOrder($orderId, false);
		
		// nacteni dat a validace formulare
		$form = new Audit_Form_Order();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit.html");
			return;
		}
		
		// zapsnai dat
		$order->comment = $form->getValue("comment");
		
		// vyhodnoceni odeslani
		if ($form->getValue("is_finished")) {
			$user = Zend_Auth::getInstance()->getIdentity();
			
			$order->finished_at = new Zend_Db_Expr("NOW()");
			$order->finished_by = $user->id_user;
		} else {
			$order->finished_at = null;
			$order->finished_by = null;
		}
		
		$order->save();
		
		$this->view->order = $order;
	}
	
	public static function findOrder($orderId, $extended = true) {
		$tableOrders = new Audit_Model_Orders();
		
		if ($extended) {
			$order = $tableOrders->findOrder($orderId);
		} else {
			$order = $tableOrders->find($orderId)->current();
		}
		
		if (!$order) throw new Zend_Db_Table_Exception(sprintf("Order #%s not found", $orderId));
		
		return $order;
	}
}