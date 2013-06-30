<?php
class Document_PresetController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function deleteAction() {
		// nacteni id a smazani dat
		$presetId = $this->_request->getParam("presetId", 0);
		$tablePresets = new Document_Model_DocumentationsPresets();
		$tablePresets->delete(array("id = ?" => $presetId));
	}
	
	public function indexAction() {
		// nacteni dat
		$tablePresets = new Document_Model_DocumentationsPresets();
		$commons = $tablePresets->getCommons();
		$noCommons = $tablePresets->getNoCommons();
		
		$form = new Document_Form_Preset();
		$form->setAction("/document/preset/post");
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->commons = $commons;
		$this->view->noCommons = $noCommons;
		$this->view->form = $form;
	}
	
	public function postAction() {
		// nacteni dat
		$form = new Document_Form_Preset();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}
		
		// vytvoreni noveho zaznamu
		$tablePresets = new Document_Model_DocumentationsPresets();
		$preset = $tablePresets->createRow(array(
				"name" => $form->getValue("name"),
				"is_general" => $form->getValue("is_general")
		));
		
		$preset->save();
		
		$this->view->preset = $preset;
	}
}