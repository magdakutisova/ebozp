<?php
require_once __DIR__ . "/SectionController.php";
require_once __DIR__ . "/FormController.php";

class Audit_QuestionController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	public function deleteAction() {
		// kontrola dat
		$question = self::loadQuestion($this->_request->getParam("questionId"));
		$form = new Audit_Form_Delete();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("get");
			return;
		}
		
		// priprava dat pred smazanim a smazani
		$category = $question->getCategory();
		$question->delete();
		
		$this->view->category = $category;
	}
	
	public function getAction() {
		// nacteni dat
		$questionId = $this->_request->getParam("questionId", 0);
		$question = self::loadQuestion($questionId);
		
		// vytvoreni formulare
		$form = new Audit_Form_Question();
		$form->populate(array("q" => $question->toArray()));
		$form->isValidPartial($this->_request->getParams());
		
		// nastaveni akce
		$url = "/audit/question/put/questionId/" . $question->id;
		$form->setAction($url);
		
		// formular smazani
		$formDelete = new Audit_Form_Delete();
		$formDelete->setAction("/audit/question/delete/questionId/" . $questionId);
		
		// zapis dat
		$this->view->form = $form;
		$this->view->category = $question->getCategory();
		$this->view->formDelete = $formDelete;
	}
	
	public function getHtmlAction() {
		$this->getAction();
	}
	
	public function postAction() {
		// nactnei dat
		$form = Audit_FormController::loadForm($this->_request->getParam("formId"));
		$category = Audit_SectionController::loadSection($this->_request->getParam("categoryId"));
		
		// kontrola formulare
		$qForm = new Audit_Form_Question();
		$qForm->setElementsBelongTo("create");
		
		if (!$qForm->isValid($this->_request->getParams())) {
			$this->_forward("edit", "section");
			return;
		}
		
		// vytvoreni nove otazky
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		$question = $tableQuestions->createQuestion($category, $qForm->getValues(true));
		
		// presmerovani zpet na editaci skupiny
		$url = "/audit/section/edit?formId=$form->id&categoryId=$category->id";
		$this->_redirect($url);
	}
	
	public function putAction() {
		// nacteni a validace dat
		$question = self::loadQuestion($this->_request->getParam("questionId"));
		
		$form = new Audit_Form_Question();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("get");
			return;
		}
		
		// oznaceni otazky jako smazane
		$question->is_deleted = 1;
		
		// kopirovani otazky
		$newQuestion = $question->getTable()->createRow($from->getValues(true));
		$newQuestion->position = $question->position;
		
		// zapis dat
		$newQuestion->save();
		
		$section = $question->getCategory();
		
		$this->view->question = $newQuestion;
		$this->view->section = $section;
	}
	
	/**
	 * nacte otazku
	 * 
	 * @param int $id identifikacni cislo otazky
	 * @throws Zend_Db_Table_Exception
	 * @return Audit_Model_Row_FormCategoryQuestion
	 */
	public static function loadQuestion($id) {
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		$question = $tableQuestions->find($id)->current();
		
		if (!$question) throw new Zend_Db_Table_Exception("Question #$id not found");
		
		return $question;
	}
}