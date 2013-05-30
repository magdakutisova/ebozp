<?php
class Document_View_Helper_DocMenu extends Zend_View_Helper_Abstract {
	
	public function docMenu() {
		$clientId = Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId", 0);
		
		$params = array("clientId" => $clientId);
		
		$items = array(
				$this->generateLink("Adresáře", "document-directory-index", $params),
				$this->generateLink("Moje dokumenty", "document-mine", $params),
				$this->generateLink("Koš", "document-trash", $params)
		);
		
		return implode(" | ", $items);
	}
	
	public function generateLink($label, $route, $params) {
		$url = $this->view->url($params, $route);
		
		return "<a href='$url'>$label</a>";
	}
}