<?php
require_once __DIR__ . "/DocumentController.php";
require_once __DIR__ . "/DirectoryController.php";

class Document_DocumentationController extends Zend_Controller_Action {
    
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
		$this->view->layout()->setLayout("client-layout");
        
        $reqType = $this->_request->getParam(self::REQ_PARAM, self::REQ_DOC);
        
        switch ($reqType) {
            case self::REQ_DOC:
                $this->_tableItems = new Document_Model_Documentations();
                break;
            
            case self::REQ_REC:
                $this->_tableItems = new Document_Model_Records();
                break;
        }
        
        $this->view->REQ_TYPE = $reqType;
        $this->_type = $reqType;
	}

	public function attachAction() {
		// nacteni dat
		$documentationId = $this->_request->getParam("documentationId", 0);
		$fileId = $this->_request->getParam("fileId", 0);

		$documentation = self::loadDocumentation($documentationId, $this->_tableItems);

		// vyhodnoceni typu odeslani a nastaveni dat
		if ($this->_request->getParam("submit-client", false)) {
			// soubor je verejny a urceny pro klienta (napr. PDF)
			$documentation->file_id = $fileId ? $fileId : null;
		} else {
			// soubor je interni a urceny jen pro nas (napr. DOC)
			$documentation->internal_file_id = $fileId ? $fileId : null;
		}

		$documentation->save();
		
		$this->_helper->FlashMessenger("Dokument byl připojen");

		$this->view->documentation = $documentation;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}

	public function clientsXmlAction() {
		$this->view->layout()->disableLayout(true);

		// tabulka klientu a pobocek
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubs = new Application_Model_DbTable_Subsidiary();

		// nacteni dat
		$clients = $tableClients->fetchAll("1", "company_name");
		$subsidiaries = $tableSubs->fetchAll("1", "subsidiary_name");

		// zpracovani klientu
		$clientList = array();
		$subList = array();

		foreach ($clients as $c) {
			$clientList[] = $c->toArray();
			$subList[$c->id_client] = array();
		}

		// zpracovani poboce
		foreach ($subsidiaries as $s) {
			$subList[$s->client_id][] = $s->toArray();
		}

		$this->view->clients = $clientList;
		$this->view->subsidiaries = $subList;
	}

	public function deleteAction() {
		// nactei dat
		$clientId = $this->_request->getParam("clientId", 0);
		$docId = $this->_request->getParam("documentationId", 0);

		$this->_tableItems->delete(array("id = ?" => $docId));
		
		$this->_helper->FlashMessenger("Dokumentace byla smazána");
		
		$this->view->clientId = $clientId;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}

	public function editAction() {
		// nacteni dat
		$docId = $this->_request->getParam("documentationId", 0);
		$clientId = $this->_request->getParam("clientId", 0);
		$doc = self::loadDocumentation($docId, $this->_tableItems);

		// vytvoreni formulare
		$form = new Document_Form_Documentation();
		self::insertSubs($form, $clientId);
		$form->populate($doc->toArray());
		$url = $this->view->url(array("documentationId" => $docId, "clientId" => $clientId, self::REQ_PARAM => $this->_type), "document-documentation-put");
		$form->setAction($url);
		$form->isValidPartial($this->_request->getParams());
		$form->getElement("subsidiary_id")->setAttrib("disabled", "disabled");

		// nacteni adresarove struktury pro vyber souboru
		$tableDirectories = new Document_Model_Directories();
		$root = $tableDirectories->root($clientId);
		
		// zapis jmen
		self::prepareNames($form, $doc, $this->_type);

        // nacteni interniho a verejneho souboru (pokud nejsou dostupne - NULL)
        $internal = $doc->getInternal();
        $public = $doc->getPublic();

		$this->view->documentation = $doc;
		$this->view->form = $form;
        $this->view->root = $root;
        $this->view->internal = $internal;
        $this->view->public = $public;
	}

	public function editHtmlAction() {
		$this->editAction();
	}

	public function getAction() {

	}

	public function importAction() {
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();

		// nacitani dat
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);

		// kontrola platnosti id
		$client = $tableClients->find($clientId)->current();

		if (!$client) throw new Zend_Db_Table_Exception("Client #$clientId not found");

		// nacteni pobocky
		$subsidiary = null;
		$where = array("client_id = ?" => $client->id_client);

		if ($subsidiaryId) {
			$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
			if (!$subsidiary) throw new Zend_Exception("Subsidiary #$subsidiaryId not found");
				
			$subsidiaryId = $subsidiary->id_subsidiary;
				
			$where["subsidiary_id = ?"] = $subsidiaryId;
		} else {
			$subsidiaryId = "NULL";
			$where[] = "subsidiary_id is null";
		}

		// smazani puvodni dokumentace
		$tableDocumentations = $this->_tableItems;
		$tableDocumentations->delete($where);

		// nacteni dat
		$dotNet = $this->_request->getParam("dotnet", 0);

		if ($dotNet) {
			$fp = $tmpFile = tmpfile();
			fwrite($fp, $this->_request->getParam("data"));
				
			fseek($fp, 0, SEEK_SET);
		} else {
			/**
			 * @todo dodelat vstup z formulare
			 */
		}

		$toInsert = array();

		// preskoceni prvniho radku
		fgetcsv($fp);

		// zapis dat do pole pro insert
		$adapter = $tableClients->getAdapter();

		while(!feof($fp)) {
			$row = fgetcsv($fp);
				
			$line = "(" . $client->id_client . "," . $subsidiaryId . "," . $adapter->quote($row[2]) . "," . $adapter->quote($row[3]) . "," . $adapter->quote($row[4]) . ")";
			$toInsert[] = $line;
		}

		// pokud je neco k zapsani, zapise se
		if ($toInsert) {
			$nameDocumentation = $tableDocumentations->info("name");
				
			try {
				$sql = "insert into $nameDocumentation (client_id, subsidiary_id, name, modified_at, comment) values " . implode("," , $toInsert);
				$adapter->query($sql);
			} catch (Zend_Exception $e) {
				throw $e;
			}
		}
		
		$this->_helper->FlashMessenger("Dokumentace byla importována");

		$this->view->dotNet = $dotNet;
		$this->view->client = $client;
	}

	public function indexAction() {
		// nacteni informaci o suplicich
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subId", null);
		$this->_request->setParam("subsidiaryId", $subsidiaryId);

		// nacteni klienta a pobocky
		$tableClients = new Application_Model_DbTable_Client();
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();

		$client = $tableClients->find($clientId)->current();
		$subsidiary = null;

		if ($subsidiaryId > 0) {
			$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();
		} else {
			$this->_request->setParam("subsidiaryId", null);
		}

		$tableDocumentations = $this->_tableItems;
		
		// pokud je dovoleno polozky dokumentace editovat, centralni dokumentace se nevypise
		$role = Zend_Auth::getInstance()->getIdentity()->role;
		$acl = new My_Controller_Helper_Acl();
		$withCentral = !$acl->isAllowed($role, "document:documentation", "put");
		
		$documentations = $tableDocumentations->getDocumentation($clientId, $subsidiaryId, $withCentral);

		// formular pridani noveho supliku
		$addForm = new Document_Form_Documentation();
		self::insertSubs($addForm, $clientId);

		// nastaveni akce
		$url = $this->view->url(array("clientId" => $client->id_client, "TYPE" => $this->_type), "document-documentation-post");
		$addForm->setAction($url);
		$addForm->getElement("subsidiary_id")->setValue($subsidiaryId);
		$addForm->isValidPartial($this->_request->getParams());
		
		self::prepareNames($addForm, null, $this->_type);
		
		// vyhodnoceni jmena
		$currName = $addForm->getValue("name");

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
		self::prepareNames($form, $_REQUEST["documentation"]["name"], $this->_type);
		
		if (!$form->isValidPartial($this->_request->getParams())) {
			$this->_forward("index");
			return;
		}

		// vyhodnoceni pobocky
		$subsidiaryId = $form->getValue("subsidiary_id");

		if (!$subsidiaryId) $subsidiaryId = null;

		// zapis dat
		$tableDocumentation = $this->_tableItems;
		$slot = $tableDocumentation->createSlot(
				$form->getValue("name"),
				$this->_request->getParam("clientId"),
				$subsidiaryId,
				$form->getValue("comment"),
				$form->getValue("comment_internal"));

		// vyhodnoceni, jestli doslo k odeslani alespon jednoho souboru
		if ($form->getElement("internal_file")->getValue() || $form->getElement("external_file")) {
			// nejake soubory byly odeslany - zapis do uloziste
			self::_saveFiles($form, $slot);
			$slot->save();
		}

		$this->_helper->FlashMessenger("Nová dokumentace byla vytvořena");
		
		// presmerovani na seznam
		$url = $this->view->url(array("clientId" => $this->_request->getParam("clientId")), "document-documentation-index");
		$url = sprintf("%s?subId=%s", $url, $subsidiaryId);
		
		$this->_redirect($url);
	}

	public function putAction() {
		// nacteni dat
		$clientId = $this->_request->getParam("clientId");
		$documentationId = $this->_request->getParam("documentationId");

		$doc = self::loadDocumentation($documentationId, $this->_tableItems);

		$form = new Document_Form_Documentation();
		self::insertSubs($form, $clientId);
		self::prepareNames($form, $_REQUEST["documentation"]["name"], $this->_type);

		if (!$form->isValidPartial($this->_request->getParams())) {
			$this->_forward("edit");
			return;
		}

		$data = $form->getValues(true);
		unset($data["subsidiary_id"]);

		$doc->setFromArray($data);

		// vyhodnoceni, jestli doslo k odeslani alespon jednoho souboru
		if ($form->getElement("internal_file")->getValue() || $form->getElement("external_file")) {
			// nejake soubory byly odeslany - zapis do uloziste
			self::_saveFiles($form, $doc);
		}

		$doc->save();
		
		$this->_helper->FlashMessenger("Změny byly uloženy");

		$this->view->doc = $doc;
		$this->view->subId = self::getFilterSubId($_SERVER["HTTP_REFERER"]);
	}

	public function resetAction() {
		// nacteni klienta
		$clientId = $this->_request->getParam("clientId", 0);
		$subsidiaryId = $this->_request->getParam("subId", -2);

		if ($subsidiaryId < -1) throw new Zend_Exception("Unkonown subsidiary set");

		// vyhodnoceni id pobocky
        if ($this->_type == self::REQ_DOC) {
            $tablePresets = new Document_Model_DocumentationsPresets();
        } else {
            $tablePresets = new Document_Model_RecordsPresets();
        }
        
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
		$subsidiaries = $tableSubsidiaries->fetchAll(array("client_id = ?" => $clientId, "active", "!deleted", "!hq_only"), array("hq desc", "subsidiary_town", "subsidiary_street"));
		$subIndex = array();

		foreach ($subsidiaries as $item) $subIndex[$item->id_subsidiary] = $item->subsidiary_name . " (" . $item->subsidiary_town . " - " . $item->subsidiary_street . ")";

		$form->setSubsidiaries($subIndex);

		return $form;
	}

	/**
	 * nacte a vraci slot dokumentace
	 *
	 * @param int $id id slotu
     * @param Document_Model_Documentations $table tabulka s daty
	 * @return Document_Model_Row_Documentation
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadDocumentation($id, $table) {
		$doc = $table->find($id)->current();

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

	/**
	 * zapise nove soubory s dokumentaci
	 *
	 * @param Document_Form_Documentation $form vyplneny formular
	 * @param Document_Model_Row_Documentation $row radek dokumentace
	 */
	private static function _saveFiles($form, $row) {
		// nacteni domovskeho adresare klienta
		$tableDirs = new Document_Model_Directories();
		$root = $tableDirs->root($row->client_id);

		// pokud koren neexistuje, vytvori se
		if (!$root) {
			$root = $tableDirs->createRoot($row->client_id, $row->getClient()->company_name);
		}

		// pokud je nastavena pobocka, nacteni adresare pobocky
		if ($row->subsidiary_id) {
			// nacteni pobocky, pokud existuje
			$dir = $tableDirs->fetchRow(array(
					"parent_id = ?" => $root->id,
					"subsidiary_id = ?" => $row->subsidiary_id
			));
				
			// pokud adresar pobocky neexistuje, pak se vytvori novy
			if (!$dir) {
				$subsidiary = $row->getSubsidiary();
				$dirName = sprintf("%s, %s", $subsidiary->subsidiary_town, $subsidiary->subsidiary_street);
				$dir = $root->createChildDir($dirName);
				$dir->subsidiary_id = $row->subsidiary_id;
				$dir->save();
			}
		} else {
			$dir = $root;
		}
        
        $uploaded = 0;

		// zapis novych dokumentacnich souboru
		if ($form->getElement("internal_file")->getValue()) {
			// nacteni souboru
            $fileRow = $row->findParentRow("Document_Model_Files", "internal");

            // vytvoreni souboru
			$file = self::_saveFile($form->getElement("internal_file"), $dir, $fileRow);
            
			$row->internal_file_id = $file->id;
				
			// pripojeni souboru do adresare
            if (!$fileRow)
                $file->attach($dir);
            
            $uploaded++;
		}

		if ($form->getElement("external_file")->getValue()) {
            // nacteni souboru
            $fileRow = $row->findParentRow("Document_Model_Files", "file");
            
			// vytvoreni souboru
			$file = self::_saveFile($form->getElement("external_file"), $dir, $fileRow);
			$row->file_id = $file->id;
				
			// pripojeni souboru do adresare
            if (!$fileRow)
                $file->attach($dir);
            
            $uploaded++;
		}
        
        if ($uploaded == 2) {
            $row->is_marked = 0;
        }
	}
	
	public static function prepareNames($form, $row = null, $type = self::REQ_DOC) {
		$nameIndex = array();
		
		if ($row) {
			if (is_object($row)) {
				$nameIndex[$row->name] = $row->name;
			} else {
				$nameIndex[$row] = $row;
			}
		}
		
		// nacteni prednastavenych jmen dokumentace
        if ($type == self::REQ_DOC) {
            $tableNames = new Document_Model_Names();
        } else {
            $tableNames = new Document_Model_RecordsNames();
        }
        
		$names = $tableNames->fetchAll(null, "name");
		
		foreach ($names as $name) {
			$nameIndex[$name->name] = $name->name;
		}
		
		$nameIndex[""] = "--JINÉ--";
		
		$form->getElement("name")->setMultiOptions($nameIndex);
	}

	/**
	 * zapise soubor do systemu a adresare a vraci instanci radku
	 *
	 * @param Zend_Form_Element_File $fileElement
	 * @param Document_Model_Row_Directory $target
     * @param Document_Model_Row_File $fileRow
	 * @return Document_Model_Row_File
	 */
	private static function _saveFile(Zend_Form_Element_File $fileElement, Document_Model_Row_Directory $target, Document_Model_Row_File $fileRow = null) {
		// vytvoreni noveho souboru
		$user = Zend_Auth::getInstance()->getIdentity();
        
        if (is_null($fileRow)) {
            $tableFiles = new Document_Model_Files();
            $fileRow = $tableFiles->createFile($fileElement->getFileName(null, false), $fileElement->getMimeType(), $user->id_user);
        }
        
		$fileRow->createVersionFromFile($fileElement->getFileName(), $fileElement->getMimeType());

		return $fileRow;
	}
}
