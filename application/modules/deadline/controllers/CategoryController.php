<?php
class Deadline_CategoryController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function deleteAction() {
		$categoryId = $this->_request->getParam("categoryId", 0);
		$category = self::loadCategory($categoryId);
		
		$parent = $category->findParent();
		$category->delete();
		
		$this->view->parent = $parent;
	}
	
	public function editAction() {
		// nacteni dat
		$categoryId = $this->_request->getParam("categoryId");
		$category = self::loadCategory($categoryId);
		
		// priprava a kontrola dat
		$editForm = new Deadline_Form_Category();
		$editForm->setElementsBelongTo("put");
		$editForm->populate($category->toArray());
		$editForm->setAction(sprintf("/deadline/category/put?categoryId=%s", $category->id));
		$editForm->isValidPartial($this->_request->getParams());
		
		// nacteni potomku
		$children = $category->children();
		
		// formular nove kategorie
		$newForm = new Deadline_Form_Category();
		$newForm->setAction(sprintf("/deadline/category/post?parentId=%s", $category->id));
		$newForm->isValidPartial($this->_request->getParams());
		
		// formular ke smazani
		$deleteForm = new Deadline_Form_Delete();
		$deleteForm->setAction(sprintf("/deadline/category/delete?categoryId=%s", $category->id))->setAttrib("onsubmit", "return confirm('Skutečně samazat kategorii?')");
		$deleteForm->getElement("submit")->setLabel("Smazat kategorii");
		
		$this->view->category = $category;
		$this->view->editForm = $editForm;
		$this->view->children = $children;
		$this->view->newForm = $newForm;
		$this->view->deleteForm = $deleteForm;
	}
	
	public function indexAction() {
		// nacteni korenu
		$tableCategories = new Deadline_Model_Categories();
		$categories = $tableCategories->bases();
		
		// formular pro novou kategorii
		$form = new Deadline_Form_Category();
		$form->setAction("/deadline/category/post");
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->categories = $categories;
		$this->view->form = $form;
	}
	
	public function postAction() {
		// nacteni dat
		$form = new Deadline_Form_Category();
		$parentId = $this->_request->getParam("parentId", null);
		
		if ($parentId) {
			$parent = self::loadCategory($parentId);
		} else {
			$parent = null;
		}
		
		if (!$form->isValid($this->_request->getParams())) {
			if ($parentId) {
				$action = "edit";
			} else {
				$action = "index";
			}
			
			$this->_forward($action);
			return;
		}
		
		$tableCategories = new Deadline_Model_Categories();
		$category = $tableCategories->createCategory($form->getValue("name"), $parent, $form->getValue("period"));
		
		$this->view->category = $category;
	}
	
	public function putAction() {
		// nacteni dat
		$categoryId = $this->_request->getParam("categoryId");
		$category = self::loadCategory($categoryId);
		
		// kontrola dat
		$form = new Deadline_Form_Category();
		$form->setElementsBelongTo("put");
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		// nastaveni dat
		$category->setFromArray($form->getValues(true));
		$category->save();
		
		$this->view->category = $category;
	}
	
	/**
	 * nacte a vraci kategorii dle id
	 * pokud kategorie neni nalezena, vyhazuje vyjimku
	 * 
	 * @param int $categoryId id kategorie
	 * @return Deadline_Model_Row_Category
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadCategory($categoryId) {
		$tableCategories = new Deadline_Model_Categories();
		$category = $tableCategories->findById($categoryId);
		
		if (!$category) throw new Zend_Db_Table_Exception(sprintf("Category #%s not found", $categoryId));
		
		return $category;
	}
}