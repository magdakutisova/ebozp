<?php
class Document_View_Helper_File extends Zend_View_Helper_Abstract {
	
	public function file($file = null) {
		if (is_null($file)) return $this;
		
		if (!isset($file->route)) {
			$file->route = "show";
		}
		
		$url = $this->url($file);
		
		if ($file->route == "show") {
			$name = $this->fileName($file);
			$target = "_self";
		} else {
			$name = "Stáhnout";
			$target = "_blank";
		}
		
		$retVal = sprintf("<a href='%s' target='%s'>%s</a>", $url, $target, $name);
		
		return $retVal;
	}
	
	public function url($file) {
		// vyhodnoceni, zda se bude stahovat
		if (isset($file->route) && $file->route == "download") {
			$route = "document-version-download";
		} else {
			$route = "document-get";
		}
		
		return $this->view->url(array("fileId" => $file->id, "clientId" => Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId", 0)), $route);
	}
	
	public function fileName($file) {
		return $file->name;
	}
}