<?php
class Document_NameController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function deleteAction() {
		$name = self::loadById($this->_request->getParam("nameId"));
		$name->delete();
	}
	
	public function editAction() {
		$name = self::loadById($this->_request->getParam("nameId"));
		
		// vytvoreni formulare
		$form = new Document_Form_Name();
		$form->populate($name->toArray());
		$form->isValidPartial($this->_request->getParams());
		
		$form->setAction(sprintf("/document/name/put?nameId=%s", $name->id));
		
		$this->view->form = $form;
		$this->view->name = $name;
	}
	
	public function indexAction() {
		// nacteni a vypsani vsech jmen
		$tableNames = new Document_Model_Names();
		$names = $tableNames->fetchAll(null, "name");
		
		$form = new Document_Form_Name();
		$form->setAction("/document/name/post")->isValidPartial($this->_request->getParams());
		
		$this->view->names = $names;
		$this->view->form = $form;
	}
	
	public function postAction() {
		$form = new Document_Form_Name();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}
		
		$tableNames = new Document_Model_Names();
		$row = $tableNames->createRow($form->getValues(true));
		$row->save();
		
		$this->view->name = $row;
	}
	
	public function putAction() {
		// nacteni a kontrola dat
		$form = new Document_Form_Name();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		$row = self::loadById($this->_request->getParam("nameId"));
		$row->setFromArray($form->getValues(true));
		$row->save();
		
		$this->view->name = $row;
		$this->view->form = $form;
	}
	
	/**
	 * nacte zaznam dle identifikacniho cisla
	 * 
	 * @param unknown_type $id
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadById($id) {
		$tableNames = new Document_Model_Names();
		$retVal = $tableNames->find($id)->current();
		
		if (!$retVal) throw new Zend_Db_Table_Exception(sprintf("Name #%s not found", $id));
		
		return $retVal;
	}
}