<?php
class Audit_FormController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	public function createAction() {
		// vytvoreni formulare
		$form = new Audit_Form_Form();
		
		// nacteni dat, pokud jsou nejaka odeslana
		$data = $this->getRequest()->getParam("form", array());
		$form->isValid($data);
		
		$this->view->form = $form;
	}
	
	/*
	 * smaze formular
	 */
	public function deleteAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		// kontrola dat
		try {
			// nacteni formulare
			$tableForms = new Audit_Model_Forms();
			$form = $tableForms->findById($data["id"]);
			
			if (!$form) throw new Zend_Exception("Form #" . $data["id"] . " not found");
		} catch (Zend_Exception $e) {
			$this->_forward("index");
			return;
		}
		
		// nacteni puvodniho dotazniku
		$questionary = $form->getQuestionary();
		
		// smazani dat
		$form->delete();
		$questionary->delete();
		
		// presmerovani na index
		$this->_redirect("/audit/form/index");
	}
	
	/*
	 * zobrazi formular pro editaci
	 */
	public function editAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->findById($data["id"]);
		
		if (!$form) {
			// formular nebyl nalezen
			$this->_forward("index");
			return;
		}
		
		// nalezeni dotazniku a prevod do tridy
		$questionaryRow = $form->getQuestionary();
		$questionary = $questionaryRow->toClass();
		
		// vytvoreni HTML formulare
		$editForm = new Audit_Form_Form();
		$editForm->populate($questionaryRow->toArray());
		
		$this->view->form = $form;
		$this->view->questionary = $questionary;
		$this->view->editForm = $editForm;
	}
	
	/*
	 * zobrazi formular pro vyplneni
	 */
	public function getAction() {
		
	}
	
	/*
	 * uvodni strana
	 * zobrazi seznam formularu
	 * zobrazi formular pro vytvoreni noveho formulare (ehm)
	 */
	public function indexAction() {
		
	}
	
	/*
	 * zobrazi seznm formularu
	 */
	public function listAction() {
		// nacteni formularu
		$tableForms = new Audit_Model_Forms();
		$forms = $tableForms->fetchAll(null, "name");
		
		$this->view->forms = $forms;
	}
	
	/*
	 * vytvori novy formular
	 */
	public function postAction() {
		// nactnei a kontrola dat
		$data = $this->getRequest()->getParam("form", array());
		$form = new Audit_Form_Form();
		
		if (!$form->isValid($data)) {
			$this->_forward("create", null, null, array("form" => $data));
			return;
		}
		
		// vytvoreni zakladniho dotazniku
		$tableQuestionaries = new Questionary_Model_Questionaries();
		$questionary = $tableQuestionaries->createQuestionary($form->getValue("name"));
		
		// vytvoreni reprezentace formulare
		$tableForms = new Audit_Model_Forms();
		$form = $tableForms->createForm($form->getValue("name"), $questionary);
		
		// presmerovani na editaci
		$this->_redirect("/audit/form/edit?form[id]=" . $questionary->id);
	}
	
	/*
	 * ulozi zmeny ve formulari
	 */
	public function putJsonAction() {
		$this->view->layout()->disableLayout();
		$this->view->response = false;
		
		// nacteni dat
		$data = $this->getRequest()->getParam("form", array());
		$data = array_merge(array("id" => 0), $data);
		
		// nacteni lokalnich dat
		$tableForms = new Audit_Model_Forms();
		$formRow = $tableForms->findById($data["id"]);
		
		if (!$formRow) return;
		
		// nacteni dotazniku
		$questionaryRow = $formRow->getQuestionary();
		
		// kontrola dat odeslanych z klienta
		$form = new Audit_Form_Form();
		
		if (!$form->isValid($data)){
			return;
		}
		
		// nastaveni jmena
		$formRow->name = $form->getElement("name")->getValue();
		$formRow->save();
		
		// naprasovani dat z dotazniku
		$qData = Zend_Json::decode($data["def"]);
		
		// nastaveni dotazniku
		$questionary = new Questionary_Questionary();
		$questionary->setFromArray($qData);
		
		// zapis do dotazniku
		$questionaryRow->saveClass($questionary);
		
		// zapis do formulare
		$formRow->writeQuestionary($questionary);
		
		// nastaveni view
		$this->view->response = true;
	}
}