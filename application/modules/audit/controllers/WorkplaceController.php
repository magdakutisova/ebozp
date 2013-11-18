<?php
class Audit_WorkplaceController extends Zend_Controller_Action {
	
	/**
	 * identifikacni cislo auditu
	 * 
	 * @var int
	 */
	protected $_auditId = 0;
	
	/**
	 * radek auditu
	 * @var Audit_Model_Row_Audit
	 */
	protected $_audit = null;
	
	public function init() {
		$this->_auditId = $this->getRequest()->getParam("auditId", 0);
		
		// kontrola jestli neni poslan primo objekt auditu
		if (is_object($this->_auditId)) {
			$this->_audit = $this->_auditId;
			$this->_auditId = $this->_auditId->id;
			return;
		}
		
		if ($this->_auditId) {
			$tableAudits = new Audit_Model_Audits();
			$this->_audit = $tableAudits->find($this->_auditId)->current();
		}
	}
	
	public function commentAction() {
		// nacteni auditu
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId not found");
		
		// kontrola opravneni
		$user = Zend_Auth::getInstance()->getIdentity();
		$role = $user->role;
		
		if ($role != My_Role::ROLE_ADMIN && 
				(($role == My_Role::ROLE_TECHNICIAN && $audit->auditor_id != $user->id_user))) throw new Zend_Acl_Exception("User is not allowed to do this action");
		
		// nacteni dat
		$form = new Audit_Form_WorkplaceComment();
		$form->populate($_REQUEST);
		
		// vytvoreni SQL kodu pro zapis
		$tableComments = new Audit_Model_AuditsWorkcomments();
		$nameComments = $tableComments->info("name");
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$comment = $adapter->quote($form->getValue("comment"));
		$workplaceId = $adapter->quote($form->getValue("workplace_id"));
		
		$sql = "insert into `$nameComments` (audit_id, workplace_id, `comment`) values ($audit->id, $workplaceId, $comment) on duplicate key update comment = values(comment)";
		$adapter->query($sql);
		
		$this->_helper->FlashMessenger("Komentář přidán");
		
		// presmerovani zpet na audit
		$url = $this->view->url(array("auditId" => $audit->id, "clientId" => $audit->client_id, "subsidiaryId" => $audit->subsidiary_id), "audit-edit") . "#workcomments";
		$this->_redirect($url);
	}
	
	public function createAction() {
		$form = new Application_Form_Workplace();
		
		$this->view->form = $form;
	}
	
	public function getAction() {
		if (!$this->_audit) throw new Zend_Exception("Audit must be set");
		$session = $this->_updateSession();
		$info = $this->_getWorkplace($session->workplaceId);
		
		if (!$info) {
			$this->view->notFound = true;
			return;
		}
		
		// nacteni paginace
		$pagination = $this->_loadPagination($session->workplaceId);
		
		$this->view->pagination = $pagination;
		$this->view->workplace = $info->workplace;
		$this->view->mistakes = $info->mistakes;
		$this->view->commentForm = $info->commentForm;
		$this->view->audit = $this->_audit;
	}
	
	public function listJsonAction() {
		$subsidiaryId = $this->_request->getParam("subsidiaryId", 0);
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		
		$this->view->workplaces = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));
	}
	
	public function postAction() {
		try {
			$this->view->action("new", "workplace", "default", $this->getRequest()->getParams());
		} catch (Exception $e) {
			/**
			 * @todo ODSTRANIT TENHLE HOVNOKOD
			 */
		}
		
		$this->_helper->FlashMessenger("Pracoviště vytvořeno");
		
		// presmerovani zpet na editaci auditu
		$url = $this->view->url($this->getRequest()->getParams(), "audit-edit") . "#newmistake";
		$this->_redirect($url);
	}
	
	public function setplaceAction() {
		$this->_updateSession(false);
		
		$url = $this->view->url($this->getRequest()->getParams(), "audit-edit") . "#workcomments";
		$this->_redirect($url);
	}
	
	/**
	 * aktualizuje session dle odeslanych hodnot a vraci instanci session
	 * 
	 * @param bool $initializeIfNot pokud je TRUE, pak provede inicializaci sessoin, pokud neni nastavena
	 * @return Zend_Session_Namespace
	 */
	protected function _updateSession($initializeIfNot = true) {
		$session = new Zend_Session_Namespace("audit");
		$workplaceId = $this->getRequest()->getParam("workplaceId", 0);
		
		if ($workplaceId) $session->workplaceId = $workplaceId;
		
		// kontrola inicializace
		if (!$session->workplaceId && $this->_audit && $initializeIfNot) {
			// nacte se prvni pracoviste auditu dle abecedy a nastavi se jeho id
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workplace = $tableWorkplaces->fetchRow(array("subsidiary_id =" . $this->_audit->subsidiary_id), "name");
			
			if ($workplace) $session->workplaceId = $workplace->id_workplace;
		}
		
		return $session;
	}
	
	/**
	 * nacteni informace o aktualnim pracovisti
	 * vracena stdClass obsahuje
	 * - workplace -> instance radku pracoviste
	 * - mistakes -> seznam neshod pridelenych k pracovisti v tomto auditu
	 * - comment -> komentar k auditu a pracovisti
	 * 
	 * @param int $workplaceId idnetifikacni cislo pracoviste
	 * @return stdClass
	 */
	protected function _getWorkplace($workplaceId) {
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$tableComments = new Audit_Model_AuditsWorkcomments();
		
		$nameWorkplaces = $tableWorkplaces->info("name");
		$nameComments = $tableComments->info("name");
		
		// nacteni informaci o pracovisti a komentari
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		$workplaceIdQ = $adapter->quote($workplaceId);
		$sql = "select `$nameWorkplaces`.*, `$nameComments`.`comment`, `$nameComments`.workplace_id, (comment is null) as `create` from `$nameWorkplaces` left join `$nameComments` on workplace_id = id_workplace and audit_id = " . $adapter->quote($this->_auditId) . " where id_workplace = $workplaceIdQ";
		
		$workplaceInfo = $adapter->query($sql)->fetch();
		
		// pokud je workplaceInfo == False, pak zadne pracoviste nebylo nalezeno a nema cenu pokracovat
		if (!$workplaceInfo) return false;
		
		if (is_null($workplaceInfo["workplace_id"])) $workplaceInfo["workplace_id"] = $workplaceInfo["id_workplace"];
		
		// vytvoreni dat pro navratovou hodnotu
		$commentForm = new Audit_Form_WorkplaceComment();
		$commentForm->populate($workplaceInfo);
		
		// nastaveni akce
		$commentForm->setAction(
				$this->view->url(array("clientId" => $this->_audit->client_id, "auditId" => $this->_audit->id), "audit-post-wcomment")
		);
		
		$workplace = new Zend_Db_Table_Row(array("data" => $workplaceInfo, "table" => $tableWorkplaces));
		
		// nacteni neshod
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_AuditsMistakes();
		
		$nameMistakes = $tableMistakes->info("name");
		$nameAssocs = $tableAssocs->info("name");
		
		// sestaveni SQL
		$sql = "select `$nameMistakes`.*, status from `$nameMistakes`, `$nameAssocs` where mistake_id = id and workplace_id = $workplaceIdQ and `$nameAssocs`.audit_id = " . $this->_audit->id;
		$mistakesData = $adapter->query($sql)->fetchAll();
		$mistakes = new Audit_Model_Rowset_AuditsRecordsMistakes(array("data" => $mistakesData, "table" => $tableMistakes, "rowClass" => $tableMistakes->getRowClass()));
		
		return (object) array("workplace" => $workplace, "commentForm" => $commentForm, "mistakes" => $mistakes);
	}
	
	/**
	 * vraci podklady pro konstrukci paginace
	 * vracena stdClass obsahuje prvky
	 * - prev -> id predchoziho pracoviste
	 * - next -> id nasledujiciho pracoviste
	 * - select -> seznam klic (id) -> hodnota (jmeno) pro generovani selectu
	 * - index -> poradi aktualniho pracoviste v seznamu
	 * pokud je dane pracoviste prvni nebo posledni, pak je prislusna hodnota next nebo prev 0
	 * 
	 * @param int $workplaceId id aktualne nacteneho pracoviste
	 */
	protected function _loadPagination($workplaceId) {
		// nacteni seznamu pracovist
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$sql = "select id_workplace, name from `" . $tableWorkplaces->info("name") . "` where subsidiary_id = " . $this->_audit->subsidiary_id . " order by name";
		$result = Zend_Db_Table_Abstract::getDefaultAdapter()->query($sql)->fetchAll();
		
		$select = array();
		$prev = 0;
		$next = 0;
		
		foreach ($result as $i => $record) {
			$select[$record["id_workplace"]] = $record["name"];
			
			if ($record["id_workplace"] == $workplaceId) {
				// zpracovavane pracoviste je aktivni -> nastavi se rychla navigace
				$prev = ($i > 0) ? $result[$i - 1]["id_workplace"] : 0;
				$next = (($i + 1) < count($result)) ? $result[$i + 1]["id_workplace"] : 0;
			}
		}
		
		// sestaveni a navraceni vysledku
		return (object) array("prev" => $prev, "next" => $next, "select" => $select);
	}
}