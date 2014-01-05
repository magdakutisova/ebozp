<?php
require_once __DIR__ . "/DocumentationController.php";

class Document_NameController extends Zend_Controller_Action {
	
    const REQ_PARAM = "TYPE";
    const REQ_DOC = "documentation";
    const REQ_REC = "record";
    
    /**
     *
     * @var Document_Model_Documentations
     */
    protected $_tableItems;
    
    /**
     * typ dotazu
     *
     * @var str
     */
    protected $_type;
    
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
        
        $reqType = $this->_request->getParam(self::REQ_PARAM, self::REQ_DOC);
        
        switch ($reqType) {
            case self::REQ_DOC:
                $this->_tableItems = new Document_Model_Names();
                break;
            
            case self::REQ_REC:
                $this->_tableItems = new Document_Model_RecordsNames();
                break;
        }
        
        $this->view->REQ_TYPE = $reqType;
        $this->_type = $reqType;
	}
	
	public function deleteAction() {
		$name = self::loadById($this->_request->getParam("nameId"), $this->_tableItems);
		$name->delete();
		
		$this->_helper->FlashMessenger("Jméno bylo smazáno");
	}
	
	public function editAction() {
		$name = self::loadById($this->_request->getParam("nameId"), $this->_tableItems);
		
		// vytvoreni formulare
		$form = new Document_Form_Name();
		$form->populate($name->toArray());
		$form->isValidPartial($this->_request->getParams());
		
		$form->setAction(sprintf("/document/name/put?TYPE=%s&nameId=%s", $this->_type, $name->id));
		
		$this->view->form = $form;
		$this->view->name = $name;
	}
	
	public function indexAction() {
		// nacteni a vypsani vsech jmen
		$names = $this->_tableItems->fetchAll(null, "name");
		
		$form = new Document_Form_Name();
		$form->setAction("/document/name/post?TYPE=" . $this->_type)->isValidPartial($this->_request->getParams());
		
		$this->view->names = $names;
		$this->view->form = $form;
	}
	
	public function postAction() {
		$form = new Document_Form_Name();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}
		
		$row = $this->_tableItems->createRow($form->getValues(true));
		$row->save();
		
		$this->view->name = $row;
		
		
		$this->_helper->FlashMessenger("Jméno bylo uloženo");
	}
	
	public function putAction() {
		// nacteni a kontrola dat
		$form = new Document_Form_Name();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		$row = self::loadById($this->_request->getParam("nameId"), $this->_tableItems);
		$row->setFromArray($form->getValues(true));
		$row->save();
		
		$this->view->name = $row;
		$this->view->form = $form;
		
		
		$this->_helper->FlashMessenger("Změny byly uloženy");
	}
	
	/**
	 * nacte zaznam dle identifikacniho cisla
	 * 
	 * @param int $id identifikacni cislo jmena
     * @param Zend_Db_Table_Abstract $table tabulka dat
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadById($id, $table) {
		$retVal = $table->find($id)->current();
		
		if (!$retVal) throw new Zend_Db_Table_Exception(sprintf("Name #%s not found", $id));
		
		return $retVal;
	}
}