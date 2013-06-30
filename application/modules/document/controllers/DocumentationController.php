<?php
require_once __DIR__ . "/DocumentController.php";
require_once __DIR__ . "/DirectoryController.php";

class Document_DocumentationController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		$this->view->layout()->setLayout("client-layout");
	}
	
	public function attachAction() {
		// nacteni dat
		$documentationId = $this->_request->getParam("documentationId", 0);
		$fileId = $this->_request->getParam("fileId", 0);
		
		$documentation = self::loadDocumentation($documentationId);
		
		// nastaveni dat
		$documentation->file_id = $fileId ? $fileId : null;
		$documentation->save();
		
		$this->view->documentation = $documentation;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}
	
	public function deleteAction() {
		// nactei dat
		$clientId = $this->_request->getParam("clientId", 0);
		$docId = $this->_request->getParam("documentationId", 0);
		
		$tableDocumentations = new Document_Model_Documentations();
		$tableDocumentations->delete(array("id = ?" => $docId));
		
		$this->view->clientId = $clientId;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}
	
	public function editAction() {
		// nacteni dat
		$docId = $this->_request->getParam("documentationId", 0);
		$clientId = $this->_request->getParam("clientId", 0);
		$doc = self::loadDocumentation($docId);
		
		// vytvoreni formulare
		$form = new Document_Form_Documentation();
		self::insertSubs($form, $clientId);
		$form->populate($doc->toArray());
		$url = $this->view->url(array("documentationId" => $docId, "clientId" => $clientId), "document-documentation-put");
		$form->setAction($url);
		$form->isValidPartial($this->_request->getParams());
		$form->getElement("subsidiary_id")->setAttrib("disabled", "disabled");
		
		// nacteni adresarove struktury pro vyber souboru
		$tableDirectories = new Document_Model_Directories();
		$root = $tableDirectories->root($clientId);
		
		$this->view->documentation = $doc;
		$this->view->form = $form;
		$this->view->root = $root;
	}
	
	public function editHtmlAction() {
		$this->editAction();
	}
	
	public function getAction() {
		
	}
	
	public function indexAction() {
		// nacteni informaci o suplicich
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subId", null);
		
		// nacteni klienta a pobocky
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$client = $tableClients->find($clientId)->current();
		$subsidiary = null;
		
		if ($subsidiaryId > 0) {
			$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
		}
		
		$tableDocumentations = new Document_Model_Documentations();
		
		$documentations = $tableDocumentations->getDocumentation($clientId, $subsidiaryId);
		
		// formular pridani noveho supliku
		$addForm = new Document_Form_Documentation();
		self::insertSubs($addForm, $clientId);
		
		// nastaveni akce
		$url = $this->view->url(array("clientId" => $client->id_client), "document-documentation-post");
		$addForm->setAction($url);
		$addForm->isValidPartial($this->_request->getParams());
		
		$this->view->documentations = $documentations;
		$this->view->subsidiary = $subsidiary;
		$this->view->client = $client;
		
		$this->view->addForm = $addForm;
		$this->view->subsidiaryId = $subsidiaryId;
	}
	
	public function postAction() {
		// kontrola dat
		$form = new Document_Form_Documentation();
		self::insertSubs($form, $this->_request->getParam("clientId"));
		
		if (!$form->isValidPartial($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}
		
		// vyhodnoceni pobocky
		$subsidiaryId = $form->getValue("subsidiary_id");
		
		if (!$subsidiaryId) $subsidiaryId = null;
		
		// zapis dat
		$tableDocumentation = new Document_Model_Documentations();
		$tableDocumentation->createSlot(
				$form->getValue("name"), 
				$this->_request->getParam("clientId"), 
				$subsidiaryId);
		
		// presmerovani na seznam
		$url = $this->view->url(array("clientId" => $this->_request->getParam("clientId")), "document-documentation-index");
		$this->_redirect($url);
	}
	
	public function putAction() {
		// nacteni dat
		$clientId = $this->_request->getParam("clientId");
		$documentationId = $this->_request->getParam("documentationId");
		
		$doc = self::loadDocumentation($documentationId);
		
		$form = new Document_Form_Documentation();
		self::insertSubs($form, $clientId);
		
		if (!$form->isValidPartial($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}
		
		$data = $form->getValues(true);
		unset($data["subsidiary_id"]);
		
		$doc->setFromArray($data);
		$doc->save();
		
		$this->view->doc = $doc;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}
	
	public function resetAction() {
		// nacteni klienta
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subId", -2);
		
		if ($subsidiaryId < -1) throw new Zend_Exception("Unkonown subsidiary set");
		
		// vyhodnoceni id pobocky
		$tablePresets = new Document_Model_DocumentationsPresets();
		
		switch ($subsidiaryId) {
			case -1:
				$tablePresets->resetClient($clientId);
				break;
				
			case 0:
				$tablePresets->resetGeneral($clientId);
				break;
				
			default:
				$tablePresets->resetSubsidiary($clientId, $subsidiaryId);
				break;
		}
		
		// presmerovani zpet na index
		$this->view->clientId = $clientId;
	}
	
	/**
	 * nastavi pobocky do formulare
	 * 
	 * @param Document_Form_Documentation $form formular
	 * @param unknown_type $clientId klient
	 * @return Document_Form_Documentation
	 */
	public static function insertSubs(Document_Form_Documentation $form, $clientId) {
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		// nacteni informaci o pobockach
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId), "subsidiary_name");
		$subIndex = array();
		
		foreach ($subsidiaries as $item) $subIndex[$item->id_subsidiary] = $item->subsidiary_name;
		
		$form->setSubsidiaries($subIndex);
		
		return $form;
	}
	
	/**
	 * nacte a vraci slot dokumentace
	 * 
	 * @param int $id id slotu
	 * @return Document_Model_Row_Documentation
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadDocumentation($id) {
		$tableDocs = new Document_Model_Documentations();
		$doc = $tableDocs->find($id)->current();
		
		if (!$doc) throw new Zend_Db_Table_Exception("Documentation slot #$id not found");
		
		return $doc;
	}
	
	private static function getFilterSubId($url) {
		$items = explode("?", $url);
		
		if (count($items) < 2) return -1;
		
		$vars = explode("&", $items[1]);
		
		foreach ($vars as $var) {
			list($name, $val) = explode("=", $var);
			
			if ($name == "subId") return $val;
		}
		
		return -1;
	}
}