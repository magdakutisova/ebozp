<?php
require_once __DIR__ . "/DirectoryController.php";

class Document_DocumentController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		$this->view->layout()->setLayout("client-layout");
		
		$retSubId = $this->_request->getParam("retsubId");
		
		if ($retSubId) $this->_request->setParam("subsidiaryId", $retSubId);
	}
	
	/**
	 * zaradi soubor do adresare
	 */
	public function attachAction() {
		// nactnei dat
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		$directory = Document_DirectoryController::loadDir($this->getRequest()->getParam("directoryId", 0));
		
		// kontrola existence asociace
		$tableAssocs = new Document_Model_DirectoriesFiles();
		
		if (!$tableAssocs->find($directory->id, $file->id)->count()) {
			$tableAssocs->insert(array(
					"directory_id" => $directory->id,
					"file_id" => $file->id
			));
		}
		
		$url = $this->view->url(array("clientId" => $this->getRequest()->getParam("clientId", 0), "fileId" => $file->id), "document-get");
		$this->_redirect($url);
	}
	
	/**
	 * odstrani souboru
	 */
	public function deleteAction() {
		
	}
	
	/**
	 * odstrani soubor z adresare
	 */
	public function detachAction() {
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		$directory = Document_DirectoryController::loadDir($this->getRequest()->getParam("directoryId", 0));
		
		// odstraneni asociace
		$tableAssocs = new Document_Model_DirectoriesFiles();
		$adapter = $tableAssocs->getAdapter();
		
		$tableAssocs->delete(array(
				"file_id = " . $file->id,
				"directory_id=" . $directory->id
		));
		
		// presmerovani
		$url = $this->view->url(array(
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"fileId" => $file->id
		), "document-get");
		
		$this->_redirect($url);
	}
	
	/**
	 * stahne soubor
	 */
	public function downloadAction() {
		// nacteni dat
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		$version = self::loadVersion($file, $this->getRequest()->getParam("versionId", 0));
		
		$this->view->file = $file;
		$this->view->version = $version;
	}
	
	/**
	 * zobrazi podrobnosti souboru
	 */
	public function getAction() {
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		
		// nacteni verzi
		$versions = $file->versions();
		
		// vyhodnoceni verze, ktera je aktualne pozadovana
		$versionId = $this->getRequest()->getParam("versionId", 0);
		$loaded = false;
		
		if ($versionId) {
			
			foreach ($versions as $v) {
				if ($v->id == $versionId) {
					$loaded = $v;
					break;
				}
			}
		}
		
		if (!$loaded) $loaded = $versions[$versions->count() - 1];
		
		$params = array(
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"fileId" => $file->id
		);
		
		// formular pro nahrani nove verze
		$uploadForm = new Document_Form_Upload();
		$url = $this->view->url($params, "document-version-upload");
		$uploadForm->setAction($url);
		
		// prejmenovani souboru
		$renameForm = new Document_Form_File();
		$renameForm->populate($file->toArray());
		$url = $this->view->url($params, "document-put");
		$renameForm->setAction($url);
		
		// nacteni adresaru, kde se prvek nachazi
		$directories = $file->directories();
		
		// nacteni korenovych adresaru
		$tableDirectories = new Document_Model_Directories();
		$roots = $tableDirectories->roots();
		
		// nacteni dokumentace
		$documentations = $file->getDocumentations();
		
		$this->view->file = $file;
		$this->view->versions = $versions;
		$this->view->loaded = $loaded;
		$this->view->uploadForm = $uploadForm;
		$this->view->renameForm = $renameForm;
		$this->view->directories = $directories;
		$this->view->roots = $roots;
		$this->view->documentations = $documentations;
	}
	
	/**
	 * zobrazi moje dokumenty (vsechny i kos)
	 */
	public function indexAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		$tableFiles = new Document_Model_Files();
		
		$files = $tableFiles->getByUserExtended($user->id_user);
		
		$this->view->files = $files;
	}
	
	/**
	 * nahraje novy soubor na server
	 */
	public function postAction() {
		// nacteni dat
		$directoryId = $this->getRequest()->getParam("directoryId", 0);
		$clientId = $this->getRequest()->getParam("clietId", 0);
		
		$directory = Document_DirectoryController::loadDir($directoryId);
		
		// kontrola formulare
		$form = new Document_Form_Upload();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			// presmerovani zpet na adresar
			$this->_forward("get", "directory");
			return;
		}
		
		// zapis souboru
		/* @var Zend_Form_Element_File */
		$file = $form->getElement("file");
		
		// vytvoreni souboru
		$tableFiles = new Document_Model_Files();
		$fileRow = $tableFiles->createFile($file->getValue(), $file->getMimeType(), Zend_Auth::getInstance()->getIdentity()->id_user);
		
		// vytvoreni prvni verze
		$fileRow->createVersionFromFile($file->getFileName(), $file->getMimeType());
		
		// zapis do ciloveho adresare
		$fileRow->attach($directory);
		
		// presmerovani zpet na adresar
		$url = $this->view->url($this->getRequest()->getParams(), "document-directory-get");
		$this->_redirect($url);
	}
	
	/**
	 * aktualizuje informace o souboru (jmeno a podobne)
	 */
	public function putAction() {
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		$form = new Document_Form_File();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			$this->_forward("get");
			return;
		}
		
		$file->setFromArray($form->getValues(true));
		$file->save();
		
		// presmerovani na get
		$url = $this->view->url(array(
				"clientId" => $this->getRequest()->getParam("clientId", 0),
				"fileId" => $file->id
		), "document-get");
		
		$this->_redirect($url);
	}
	
	/**
	 * zobrazi kos
	 */
	public function trashAction() {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$tableFiles = new Document_Model_Files();
		$files = $tableFiles->getTrash($user->id_user);
		
		$this->view->files = $files;
	}
	
	/**
	 * nahraje novou verzi souboru na server
	 */
	public function uploadAction() {
		// nacteni souboru z databaze
		$file = self::loadFile($this->getRequest()->getParam("fileId", 0));
		
		// kontrola formulare
		$form = new Document_Form_Upload();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			$this->_forward("get");
			return;
		}
		
		// presun souboru
		$element = $form->getElement("file");
		
		$file->createVersionFromFile($element->getFileName(), $element->getMimeType());
		
		// presmerovani na get
		$url = $this->view->url(array("clientId" => $this->getRequest()->getParam("clientId", 0), "fileId" => $file->id), "document-get");
		$this->_redirect($url);
	}
	
	/**
	 * nacte soubor z databaze
	 * 
	 * @param int $id identifikacni cislo souboru
	 * @return Document_Model_Row_File
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadFile($id) {
		$tableFiles = new Document_Model_Files();
		$file = $tableFiles->find($id)->current();
		
		if (!$file) throw new Zend_Db_Table_Exception("File ##id not found");
		
		return $file;
	}
	
	/**
	 * nacte verzi dle souboru, ke keteremu patri a jejiho id
	 * 
	 * @param Document_Model_Row_File $file soubor
	 * @param int $versionId id
	 * @return Document_Model_Row_Version
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadVersion(Document_Model_Row_File $file, $versionId) {
		$tableVersions = new Document_Model_Versions();
		
		// pokud je zadano versionId, vyhleda se tato verze, pokud ne, vyhleda se aktualni
		if ($versionId) {
			$version = $tableVersions->find($versionId)->current();
		} else {
			// vyhledani aktualni verze
			$select = $tableVersions->select(false);
			$select->where("file_id = ?", $file->id)->limit(1, 0)->order("id desc");
			
			$version = $tableVersions->fetchRow($select);
		}
		
		if (!$version) throw new Zend_Db_Table_Exception("Version #$versionId not found");
		if ($version->file_id != $file->id) throw new Zend_Db_Table_Exception("Version #$versionId is not part of file #$file->id");
		
		return $version;
	}
}