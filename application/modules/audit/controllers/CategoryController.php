<?php
class Audit_CategoryController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers/", "Zend_View_Helper");
	}
	
	/*
	 * vypise formular pro vytvoreni
	 * nove (pod)kategorie
	 */
	public function createAction() {
		// vytvoreni instance formulare a nastaveni action
		$form = new Audit_Form_Category();
		$form->setAction("/audit/category/post");
		
		// kontrola predka
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		$form->isValidPartial($data);
		
		// kontrola rodice
		if ($data["id"]) {
			$tableCategories = new Audit_Model_Categories();
			$category = $tableCategories->find($data["id"])->current();
			
			if (!$category) throw new Zend_Exception("Category #" . $data["id"] . " has not been found");
			
			$form->getElement("parent_id")->setValue($data["id"]);
		} else {
			// pokud neni nastaven predek, odebere se prvek parent_id
			$form->removeElement("parent_id");
		}
		
		// odebrani prvku id
		$form->removeElement("id");
		
		$this->view->form = $form;
	}
	
	/*
	 * smaze kategorii
	 */
	public function deleteAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		// kontrola dat
		$tableCategories = new Audit_Model_Categories();
		
		try {
			// nacteni formulare
			$category = $tableCategories->getById($data["id"]);
			
			if (!$category) throw new Zend_Exception("Category id " . $data["id"] . " not found");
		} catch (Zend_Exception $e) {
			// nacteni presmerovani
			$redirect = $this->getRequest()->getParam("redirect", "/audit/index/index");
			$this->_redirect($redirect);
			return;
		}
		
		// zjisteni id predka
		$parentId = $category->parent_id;
		
		$category->delete();
		
		// vyhodnoceni presmerovani
		if ($parentId) {
			// predek existuje, presmeruje se na nej
			$redirect = "/audit/category/get?category[id]=" . $parentId;
		} else {
			// presmeruje se na index
			$redirect = "/audit/category/index";
		}
		
		$this->_redirect($redirect);
	}
	
	/*
	 * vypise informace o kategorii
	 */
	public function getAction() {
		// nacteni a kontrola dat
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		// nacteni kateogire
		$tableCategories = new Audit_Model_Categories();
		$category = $tableCategories->getById($data["id"]);
		
		if (!$category) {
			// hlaska o nenalezeni
			throw new Zend_Exception("Category #" . $data["id"] . " not found");
		}
		
		// ziskani kategorie jako pole
		$categoryArray = $category->toArray();
		
		// formular editace
		$form = new Audit_Form_Category();
		$form->populate($categoryArray);
		$form->setAction("/audit/category/put");
		
		// formular smazani
		$deleteForm = new Audit_Form_CategoryDelete();
		$deleteForm->populate($categoryArray);
		
		$this->view->form = $form;
		$this->view->category = $category;
		$this->view->deleteForm = $deleteForm;
	}
	
	/*
	 * vypise seznam korenovych kategorii a formular pro pridani nove
	 */
	public function indexAction() {
		
	}
	
	/*
	 * vypise seznam kategorii
	 */
	public function listAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		// kontrola dat
		try {
			$tableCategories = new Audit_Model_Categories();
			
			if ($data["id"]) {
				// nactou se potomci
				$category = $tableCategories->getById($data["id"]);
				$categories = $category->getChildren("name");
			} else {
				// nactou se rooti
				$categories = $tableCategories->getRoots("name");
				$category = null;
			}
		} catch (Zend_Exception $e) {
			
		}
		
		$this->view->categories = $categories;
		$this->view->category = $category;
	}
	
	/*
	 * vytvori novou kategorii
	 */
	public function postAction() {
		// nactnei dat
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		$form = new Audit_Form_Category();
		
		// kontrola dat formulare
		if (!$form->isValid($data)) {
			$this->_forward("create", null, null, array("category" => $data));
			return;
		}
		
		// zapsani dat
		$tableCategories = new Audit_Model_Categories();
		$parent = null;
		
		if ($data["parent_id"]) {
			// predek existuje
			$parent = $tableCategories->find($data["parent_id"])->current();
		}
		
		$category = $tableCategories->createCategory($data["name"], $parent);
		
		// presmerovani na get
		$this->_redirect("/audit/category/get?category[id]=" . $category->id);
	}
	
	/*
	 * upravi nastaveni kategorie
	 * (momentalne pouze jmeno)
	 */
	public function putAction() {
		// nacteni dat
		$data = $this->getRequest()->getParam("category", array());
		$data = array_merge(array("id" => 0), $data);
		
		// nacteni kategorie
		$tableCategories = new Audit_Model_Categories();
		$category = $tableCategories->getById($data["id"]);
		
		if (!$category) {
			$this->_forward("index");
			return;
		}
		
		// nacteni formulare a validace dat
		$form = new Audit_Form_Category();
		
		if (!$form->isValid($data)) {
			$this->_forward("get");
			return;
		}
		
		// filtrace dat
		$filtered = $form->populate($data)->getValues(true);
		
		// nastaveni hodnot
		$category->name = $filtered["name"];
		$category->save();
		
		// zobrazeni get
		$this->_forward("get");
	}
}