<?php
require_once __DIR__ . "/FormController.php";

class Audit_SectionController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	public function deleteAction() {
		// nacteni dat
		$category = self::loadSection($this->_request->getParam("categoryId"));
		$form = new Audit_Form_Delete();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_request->setParam("formId", $category->form_id);
			$this->_forward("edit");
			return;
		}
		
		// nacteni formulare
		$form = $category->findParentRow("Audit_Model_Forms", "form");
		$category->is_deleted = 1;
		$category->save();
		
		$this->_helper->FlashMessenger("Položka byla smazána");
		
		$this->view->form = $form;
	}
	
	public function editAction() {
		// nacteni dat
		$formId = $this->getRequest()->getParam("formId", 0);
		$categoryId = $this->getRequest()->getParam("categoryId", 0);
		
		$form = Audit_FormController::loadForm($formId);
		$category = self::loadSection($categoryId);
		
		// vytvoreni formulare upravy kategorie
		$categoryForm = new Audit_Form_Section();
		$categoryForm->populate($category->toArray())
				->isValidPartial($this->_request->getParams());
		$categoryForm->setAction("/audit/section/put?categoryId=" . $category->id . "&formId=" . $form->id);
		
		// vytvoreni formulare pro vlozeni nove otazky a nastaveni url
		$formNewQuestion = new Audit_Form_Question();
		$formNewQuestion->setAction("/audit/question/post/formId/" . $form->id . "/categoryId/$categoryId");
		$formNewQuestion->setElementsBelongTo("create");
		
		$formNewQuestion->isValidPartial($this->_request->getParams());
		
		// formular smazani
		$formDelete = new Audit_Form_Delete();
		$formDelete->setAction("/audit/section/delete/categoryId/" . $category->id);
		
		// nacteni otazek
		$questions = $category->findQuestions();
		
		// zapis dat do view
		$this->view->category = $category;
		$this->view->form = $form;
		$this->view->categoryForm = $categoryForm;
		$this->view->newQuestion = $formNewQuestion;
		$this->view->questions = $questions;
		$this->view->formDelete = $formDelete;
	}
	
	public function postAction() {
		// nacteni formulare
		$form = Audit_FormController::loadForm($this->_request->getParam("formId", 0));
		
		// kontrola dat
		$formPost = new Audit_Form_Section();
		
		if (!$formPost->isValid($this->_request->getParams())) {
			$this->_forward("edit", "form");
			return;
		}
		
		// vytvoreni zaznamu
		$tableCategories = new Audit_Model_FormsCategories();
		$category = $tableCategories->createCategory($formPost->getValue("name"), $form);
		
		// zapis do view
		$this->view->category = $category;
		$this->view->form = $form;
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
	}
	
	public function putAction() {
		// nacteni dat
		$category = self::loadSection($this->_request->getParam("categoryId", 0));
		
		// kontrola dat
		$form = new Audit_Form_Section();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_request->setParam("formId", $category->form_id);
			$this->_forward("edit");
			return;
		}
		
		// zapis dat
		$category->setFromArray($form->getValues(true));
		$category->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
		
		$this->view->category = $category;
	}
	
	public function sortAction() {
		// nactnei dat
		$category = self::loadSection($this->_request->getParam("categoryId", 0));
		
		// vytvoreni instance tabulky
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		
		// vytvoreni seznamu dat
		$order = (array) $this->_request->getParam("question", array());
		$order = array_merge(array("sort" => array()), $order);
		
		// priprava dat
		$where = array(
				"id = ?" => 0,
				"group_id = ?" => $category->id
		);
		
		$data["position"] = 0;
		
		foreach ($order["sort"] as $qId) {
			$data["position"]++;
			$where["id = ?"] = $qId;
			$tableQuestions->update($data, $where);
		}
		
		$this->view->category = $category;
	}
	
	/**
	 * nacte kategorii (sekci) z databaze
	 * 
	 * @param int $id id kategorie
	 * @return Audit_Model_Row_FormCategory
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadSection($id) {
		$tableCategories = new Audit_Model_FormsCategories();
		$category = $tableCategories->find($id)->current();
		
		if (!$category) throw new Zend_Db_Table_Exception("Category (section) #$id not found");
		
		return $category;
	}
}