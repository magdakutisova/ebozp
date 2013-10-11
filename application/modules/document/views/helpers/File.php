<?php
class Document_View_Helper_File extends Zend_View_Helper_Abstract {
	
	public function file($file = null, $route = "show", $subsidiaryId = null) {
		if (is_null($file)) return $this;
		
		$url = $this->url($file, $route, $subsidiaryId);
		
		if ($route == "show") {
			$name = $this->fileName($file);
			$target = "_self";
		} else {
			$name = "Stáhnout";
			$target = "_blank";
		}
		
		$retVal = sprintf("<a href='%s' target='%s'>%s</a>", $url, $target, $name);
		
		return $retVal;
	}
	
	public function url($file, $route = "show", $subsidiaryId = null) {
		// vyhodnoceni, zda se bude stahovat
		if ($route == "download") {
			$route = "document-version-download";
		} else {
			$route = "document-get";
		}
		
		$url = $this->view->url(array("fileId" => $file->id, "clientId" => Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId", 0)), $route);
		
		if (!is_null($subsidiaryId)) {
			$url .= sprintf("?retsubId=%s", $subsidiaryId);
		}
		
		return $url;
	}
	
	public function fileName($file) {
		return $file->name;
	}
}