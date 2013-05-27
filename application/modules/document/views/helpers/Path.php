<?php
class Document_View_Helper_Path extends Zend_View_Helper_Abstract {
	
	public function path(Document_Model_Rowset_Directories $path = null, Document_Model_Row_Directory $directory = null) {
		// pokud je cesta NULL, vraci se instance
		if ($path == null) return $this;
		
		// vygenerovani cesty
		$list = array();
		
		foreach ($path as $item) {
			$list[] = $this->dirLink($item, $this->dirName($item));
		}
		
		$list[] = $this->dirName($directory);
		
		return "/ " . implode(" / ", $list);
	}
	
	public function dirLink(Document_Model_Row_Directory $dir, $caption) {
		// vygenerovani URL
		$clientId = Zend_Controller_Front::getInstance()->getRequest()->getParam("clientId");
		
		$url = $this->view->url(array("clientId" => $clientId, "directoryId" => $dir->id), "document-directory-get");
		
		return "<a href='$url'>$caption</a>";
	}
	
	public function dirName(Document_Model_Row_Directory $dir) {
		return $dir->name;
	}
}