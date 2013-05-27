<?php
require_once __DIR__ . "/DirectoryController.php";

class Document_DocumentController extends Zend_Controller_Action {
	
	/**
	 * zaradi soubor do adresare
	 */
	public function attachAction() {
		
	}
	
	/**
	 * odstrani souboru
	 */
	public function deleteAction() {
		
	}
	
	/**
	 * odstrani soubor z adresare
	 */
	public function detachAction() {
		
	}
	
	/**
	 * zobrazi podrobnosti souboru
	 */
	public function getAction() {
		
	}
	
	/**
	 * zobrazi kos
	 */
	public function indexAction() {
		
	}
	
	/**
	 * nahraje novy soubor na server
	 */
	public function postAction() {
		// nacteni dat
		$directoryId = $this->getRequest()->getParam("directoryId", 0);
		$clientId = $this->getRequest()->getParam("clietId", 0);
		
		$directory = Document_DirectoryController::loadDir($directoryId);
		
		// kontrola formulare
		$form = new Document_Form_Upload();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			// presmerovani zpet na adresar
			$this->_forward("get", "directory");
			return;
		}
	}
	
	/**
	 * aktualizuje informace o souboru (jmeno a podobne)
	 */
	public function putAction() {
		
	}
	
	/**
	 * nahraje novou verzi souboru na server
	 */
	public function updateAction() {
		
	}
}