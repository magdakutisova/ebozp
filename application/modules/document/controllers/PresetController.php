<?php
require_once __DIR__ . "/DocumentationController.php";

class Document_PresetController extends Zend_Controller_Action {
	
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
                $this->_tableItems = new Document_Model_DocumentationsPresets();
                break;
            
            case self::REQ_REC:
                $this->_tableItems = new Document_Model_RecordsPresets();
                break;
        }
        
        $this->view->REQ_TYPE = $reqType;
        $this->_type = $reqType;
	}
	
	public function deleteAction() {
		// nacteni id a smazani dat
		$presetId = $this->_request->getParam("presetId", 0);
		$this->_tableItems->delete(array("id = ?" => $presetId));
	}
	
	public function indexAction() {
		// nacteni dat
		$commons = $this->_tableItems->getCommons();
		$noCommons = $this->_tableItems->getNoCommons();
		
		$form = new Document_Form_Preset();
		$form->setAction("/document/preset/post?TYPE=" . $this->_type);
        Document_DocumentationController::prepareNames($form, null, $this->_type);
		$form->isValidPartial($this->_request->getParams());
		
		$this->view->commons = $commons;
		$this->view->noCommons = $noCommons;
		$this->view->form = $form;
	}
	
	public function postAction() {
		// nacteni dat
		$form = new Document_Form_Preset();
        Document_DocumentationController::prepareNames($form, null, $this->_type);
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}
		
		// vytvoreni noveho zaznamu
		$preset = $this->_tableItems->createRow(array(
				"name" => $form->getValue("name"),
				"is_general" => $form->getValue("is_general")
		));
		
		$preset->save();
		
		$this->view->preset = $preset;
	}
}