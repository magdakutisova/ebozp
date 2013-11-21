<?php
class Audit_FarplanController extends Zend_Controller_Action {
	
	public function init() {
		// zapsani helperu
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	/**
	 * vytvori farplan z formulare a presune se na jeho editaci
	 */
	public function cloneAction() {
		// nacteni informaci
		$auditId = $this->_request->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($auditId);
		
		$this->_request->setParam("clientId", $audit->client_id);
		
		// kontrola formulare a nastaveni dat
		$form = new Audit_Form_FormInstanceCreate();
		$formInstances = $audit->getFarplans();
		$form->loadUnused($formInstances);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit", "audit", "audit");
			return;
		}
		
		$formId = $form->getValue("id");
		
		// nacteni dat z databaze
		$tableForms = new Audit_Model_Forms();
		$tableAudits = new Audit_Model_Audits();
		
		$form = $tableForms->findById($formId);
		
		// vytvoreni farplanu
		$tableFars = new Audit_Model_Farplans();
		$farplan = $tableFars->cloneForm($form, $audit);
		
		$this->view->farplan = $farplan;
		$this->view->form = $form;
		$this->view->audit = $audit;
	}
	
	public function deleteAction() {
		// nacteni farplanu
		$farplanId = $this->_request->getParam("farplanId");
		$farplan = self::findFarplan($farplanId);
		
		$audit = $farplan->findAudit();
		$farplan->delete();
		
		$this->view->audit = $audit;
	}
	
	public function editAction() {
		// nacteni farplanu
		$farplanId = $this->_request->getParam("farplanId", 0);
		$farplan = self::findFarplan($farplanId);
		
		// nacteni auditu
		$audit = $farplan->findAudit();
		
		// nacteni informaci o farplanu
		$farplanData = $farplan->findData();
		
		$this->view->audit = $audit;
		$this->view->farplan = $farplan;
		$this->view->farplanData = $farplanData;
	}
	
	public function getAction() {
		// nacteni farplanu
		$farplanId = $this->_request->getParam("farplanId", 0);
		$farplan = self::findFarplan($farplanId);
		
		// nacteni auditu
		$audit = $farplan->findAudit();
		
		// nacteni informaci o farplanu
		$farplanData = $farplan->findData(true);
		
		$this->view->audit = $audit;
		$this->view->farplan = $farplan;
		$this->view->farplanData = $farplanData;
	}
	
	public function saveAction() {
		// nacteni farplanu
		$farplanId = $this->_request->getParam("farplanId");
		$farplan = self::findFarplan($farplanId);
		
		// anulace kategoriee
		$tableCategories = new Audit_Model_FarplansCategories();
		$tableCategories->update(array("is_selected" => 0), array("farplan_id = ?" => $farplan->id));
		
		// vytvoreni noveho seznamu kategorii
		$catList = (array) $this->_request->getParam("category", array());
		$catIds = array(0);
		
		foreach ($catList as $id => $use) {
			if ($use) {
				$catIds[] = $id;
			}
		} 
		
		// update dat
		$tableCategories->update(array("is_selected" => 1), array("farplan_id = ?" => $farplan->id, "id in (?)" => $catIds));
		
		$this->view->farplanId = $farplanId;
		$this->view->auditId = $this->_request->getParam("auditId");
		$this->view->clientId = $this->_request->getParam("clientId");
	}
	
	/**
	 * nacte farplan z databaze
	 * 
	 * @param int $id identifikacni cislo farplanu
	 * @return Audit_Model_Row_Farplan
	 * @throws Zend_Db_Table_Exception
	 */
	public static function findFarplan($id) {
		// pokus o nacteni dat
		$tableFarplans = new Audit_Model_Farplans();
		$farplan = $tableFarplans->findById($id);
		
		if (!$farplan) throw new Zend_Db_Table_Exception(sprintf("Farplan #%s not found", $id));
		
		return $farplan;
	}
}