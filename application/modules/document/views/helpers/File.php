<?php
class Document_View_Helper_File extends Zend_View_Helper_Abstract {
	
	public function file($file = null) {
		if (is_null($file)) return $this;
		
		$url = $this->url($file);
		$name = $this->fileName($file);
		
		$retVal = "<a href='$url'>$name</a>";
		
		return $retVal;
	}
	
	public function url($file) {
		return $this->view->url(array("fileId" => $file->id, "clientId" => Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId", 0)), "document-get");
	}
	
	public function fileName($file) {
		return $file->name;
	}
}