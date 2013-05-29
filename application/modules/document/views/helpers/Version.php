<?php
class Document_View_Helper_Version extends Zend_View_Helper_Abstract {
	
	public function version(Document_Model_Row_Version $version = null) {
		if (is_null($version)) return $this;
		
		$content = $this->date($version->created_at);
		$url = $this->view->url(array(
				"clientId" => Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId"),
				"fileId" => $version->file_id,
				"versionId" => $version->id
		), "document-version-get");
		
		return "<a href='$url'>$content</a>";
	}
	
	public function date($date) {
		$elements = explode(" ", $date);
		$p1 = explode("-", $elements[0]);
				
		$retVal = $p1[2] . ". " . $p1[1] . ". " . $p1[0] . " ";
		$retVal .= $elements[1];
		
		return $retVal;
	}
	
	public function download($version) {
		$url = $this->view->url(array(
				"clientId" => Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId", 0),
				"fileId" => $version->file_id,
				"versionId" => $version->id
		), "document-version-download");
		
		return "<a href='$url' class='button' target='_blank'>Stáhnout</a>";
	}
}