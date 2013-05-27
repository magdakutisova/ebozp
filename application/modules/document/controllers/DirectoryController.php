<?php
class Document_DirectoryController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
	}
	
	public function deleteAction() {
		$directory = self::loadDir($this->getRequest()->getParam("directoryId", 0));
		
		// korenovy adresar nesmi byt smazan
		if ($directory->parent_id == null) throw new Zend_Exception("Directory #$directory->id is root");
		$parentId = $directory->parent_id;
		
		// smazani adresare a presmerovani na rodice
		$directory->delete();
		$url = $this->view->url(array("clientId" => $this->getRequest()->getParam("clientId", 0), "directoryId" => $parentId), "document-directory-get");
		$this->_redirect($url);
	}
	
	public function getAction() {
		// nacteni dat
		$directory = self::loadDir($this->getRequest()->getParam("directoryId", 0));
		
		// nacteni cesty
		$path = $directory->path();
		
		// nacteni podrizenych adresaru
		$childDirs = $directory->childDirs();
		$childDocuments = $directory->childDocs();
		
		// forumalr noveho adresare
		$clientId = $this->getRequest()->getParam("clientId", 0);
		$formPostDir = new Document_Form_Directory();
		$formPostDir->setSubmit("Vytvořit");
		$formPostDir->setParent($directory);
		$formPostDir->setNameLabel("Nový adresář : ");
		$formPostDir->setAction($this->view->url(array(
				"clientId" => $clientId,
				"directoryId" => $directory->id), "document-directory-post"));
		$formPostDir->isValidPartial($this->getRequest()->getParams());
		
		// formular pro upload
		$formPostFile = new Document_Form_Upload();
		$formPostFile->setDirectory($directory);
		$url = $this->view->url(array("clientId" => $clientId, "directoryId" => $directory->id), "document-post");
		$formPostFile->setAction($url);
		
		$this->view->directory = $directory;
		$this->view->path = $path;
		$this->view->childDirs = $childDirs;
		$this->view->childDocuments = $childDocuments;
		$this->view->formPostDir = $formPostDir;
		$this->view->formPostFile = $formPostFile;
		$this->view->clientId = $this->getRequest()->getParam("clientId");
	}
	
	public function indexAction() {
		// zatim pouze nacteni korene - pozdeji domovsky adresar uzivatele
		$tableDirectories = new Document_Model_Directories();
		$directory = $tableDirectories->root($this->getRequest()->getParam("clientId", 0));
		
		// pokud root nebyl nalezen, vytvori se
		if (!$directory) {
			// nacteni klienta
			$tableClients = new Application_Model_DbTable_Client();
			$clientId = $this->getRequest()->getParam("clientId", 0);
			$client = $tableClients->find($clientId)->current();
			
			if (!$client) throw new Zend_Db_Table_Exception("Client #$cliendId not found");
			
			$directory = $tableDirectories->createRoot($client->id_client, $client->company_name);
		}
		
		$this->getRequest()->setParam("directoryId", $directory->id);
		$this->_forward("get");
	}
	
	public function postAction() {
		$form = new Document_Form_Directory();
		
		if (!$form->isValid($this->getRequest()->getParams())) {
			$this->_forward("index");
			return;
		}
		
		// nacteni rodicovskeho adresare
		$parent = self::loadDir($this->getRequest()->getParam("directoryId", 0));
		
		// vytvoreni potomka
		$child = $parent->createChildDir($form->getValue("name"));
		
		// vygenerovani url
		$url = $this->view->url(array("clientId" => $this->getRequest()->getParam("clientId"), "directoryId" => $child->id), "document-directory-get");
		$this->_redirect($url);
	}
	
	public function putAction() {
		
	}
	
	/**
	 * nacte adresar dle id
	 * @param int $id identifikacni cislo adresare
	 * @return Document_Model_Row_Directory
	 * @throws Zend_Db_Table_Exception
	 */
	public static function loadDir($id) {
		$tableDirectories = new Document_Model_Directories();
		$directory = $tableDirectories->find($id)->current();
		
		if (!$directory) throw new Zend_Db_Table_Exception("Directory #$id not found");
		
		return $directory;
	}
}