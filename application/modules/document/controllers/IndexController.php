<?php
class Document_IndexController extends Zend_Controller_Action {
	
	public function indexAction() {
		$this->_forward("index", "directory");
	}
}