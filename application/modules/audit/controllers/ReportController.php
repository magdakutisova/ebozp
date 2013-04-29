<?php
class Audit_ReportController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		$this->view->headLink()->appendStylesheet("/css/jquery.jqplot.css");
	}
	
	/**
	 * prvnotni vytvoreni z auditu
	 */
	public function createAction() {
		$audit = $this->loadAudit();
		
		// nacteni nacionalii
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		$coordinator = $audit->getCoordinator();
		$auditor = $audit->getAuditor();
		
		// nacteni formularu
		$forms = $audit->getForms();
		$formsGroups = self::getFormsGroups($forms);
		
		// nacteni pracovist
		$workplaces = self::getWorkplaces($audit);
		
		// nacteni neshod
		$mistakes = self::getMistakes($audit);
		
		$this->view->audit = $audit;
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->subsidiary = $subsidiary;
		
		$this->view->forms = $forms->toArray();
		$this->view->formsGroups = $formsGroups;
		
		$this->view->workplaces = $workplaces;
		
		$this->view->mistakes = $mistakes;
	}
	
	public function editAction() {
		$audit = $this->loadAudit();
		$report = $this->loadReport($audit);
		
		// nacteni nacionalii
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		$coordinator = $audit->getCoordinator();
		$auditor = $audit->getAuditor();
		
		// nacteni itemu
		$itemList = $report->getItems();
		$items = array();
		
		foreach ($itemList as $item) $items[] = $item->content;
		
		// nacteni formularu
		$forms = $audit->getForms();
		$formsGroups = self::getFormsGroups($forms);
		
		// nacteni pracovist
		$workplaces = self::getWorkplaces($audit);
		
		// nacteni neshod
		$mistakes = self::getMistakes($audit);
		
		$this->view->audit = $audit;
		$this->view->client = $client;
		$this->view->coordinator = $coordinator;
		$this->view->auditor = $auditor;
		$this->view->subsidiary = $subsidiary;
		$this->view->report = $report;
		$this->view->items = $items;
		
		$this->view->forms = $forms->toArray();
		$this->view->formsGroups = $formsGroups;
		
		$this->view->workplaces = $workplaces;
		
		$this->view->mistakes = $mistakes;
	}
	
	public function saveAction() {
		// nacteni dat
		$data = (array) $this->getRequest()->getParam("report", array());
		$data = array_merge(array(
				"name" => "",
				"org" => "",
				"org_hq" => "",
				"ico" => "",
				"sub_hq" => "",
				"done_at" => "",
				"done_in" => "",
				"auditor_name" => "",
				"coordinator_name" => "",
				"client_responsibles" => "",
				"target_caption" => "",
				"target" => "",
				"progres_caption" => "",
				"item" => array(),
				"progres_note" => "",
				"summary" => ""
				), $data);
		
		// nacteni informace z databaze
		$audit = $this->loadAudit();
		$report = $this->loadReport($audit);
		
		// nastaveni dat a ulozeni
		$report->setFromArray($data);
		$report->save();
		
		if ($audit->report_id == null) {
			$audit->report_id = $report->id;
			$audit->save();
		}
		
		// zapis polozek cilu
		$tableItems = new Audit_Model_AuditsReportsProgresitems();
		$tableItems->delete("report_id = " . $report->id);
		
		foreach ($data["item"] as $item) {
			$tableItems->insert(array(
					"report_id" => $report->id,
					"content" => $item
			));
		}
		
		// presmerovani na editaci zpravy
		$url = $this->view->url(array("auditId" => $audit->id, "clientId" => $audit->client_id), "audit-report-edit");
		$this->_redirect($url);
	}
	
	public function loadAudit() {
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId has not been found");
		
		return $audit;
	}
	
	public static function getFormsGroups($forms) {
		$tableRecordsGroups = new Audit_Model_AuditsRecordsGroups();
		$tableRecords = new Audit_Model_AuditsRecords();
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$retVal = array();
		
		foreach ($forms as $form) {
			// nacteni skupin a zaznamu
			$groups = $tableRecordsGroups->fetchAll("audit_form_id = " . $form->id, "name");
			$records = $tableRecords->fetchAll("audit_form_id = " . $form->id, "group_id");
			
			// indexace zaznamu
			$lastGroupId = 0;
			$groupInfo = array();
			
			foreach ($records as $record) {
				if ($record->group_id != $lastGroupId) {
					$lastGroupId = $record->group_id;
					$groupInfo[$lastGroupId] = array(
							"max" => 0,
							"gained" => 0
					);
				}
				
				$groupInfo[$lastGroupId]["max"] += $record->weight;
				$groupInfo[$lastGroupId]["gained"] += ($record->score == Audit_Model_AuditsRecords::SCORE_N) ? $record->weight : 0;
			}
			
			$retVal[$form->id] = array(
					"groups" => $groups->toArray(),
					"groupsInfo" => $groupInfo
			);
		}
		
		return $retVal;
	}
	
	public static function getWorkplaces($audit) {
		$retVal = array();
		
		// nacteni pracovist
		$tableWorks = new Application_Model_DbTable_Workplace();
		$workplaces = $tableWorks->fetchAll("subsidiary_id = " . $audit->subsidiary_id, "name");
		
		// nacteni komentaru a indexace dle pracoviste
		$tableComments = new Audit_Model_AuditsWorkcomments();
		$comments = $tableComments->fetchAll("audit_id = " . $audit->id);
		$commentIndex = array();
		
		foreach ($comments as $comment) {
			$commentIndex[$comment->workplace_id] = $comment->toArray();
		}
		
		// nacteni neshod dle pracoviste
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$mistakes = $tableMistakes->fetchAll(array(
				"audit_id = " . $audit->id,
				"workplace_id is not null"
		), "workplace_id");
		
		// indexace neshod dle pracoviste
		$mistakeIndex = array();
		$lastId = 0;
		
		foreach ($mistakes as $mistake) {
			if ($lastId != $mistake->workplace_id) {
				$lastId = $mistake->workplace_id;
				$mistakeIndex[$lastId] = array();
			}
			
			$mistakeIndex[$lastId][] = $mistake->toArray();
		}
		
		$retVal["workplaces"] = $workplaces->toArray();
		$retVal["comments"] = $commentIndex;
		$retVal["mistakes"] = $mistakeIndex;
		
		return $retVal;
	}
	
	public static function getMistakes($audit) {
		// nacteni neshod, ktere spadaji do skupin
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableRecords = new Audit_Model_AuditsRecords();
		
		$nameRecords = $tableRecords->info("name");
		$nameMistakes = $tableMistakes->info("name");
		
		$sql = "select group_id, $nameMistakes.id, $nameMistakes.weight, mistake, suggestion, comment from $nameMistakes, $nameRecords where $nameMistakes.record_id = $nameRecords.id and $nameRecords.audit_id = $audit->id and score = " . Audit_Model_AuditsRecords::SCORE_N;
		$mistakes = $tableMistakes->getAdapter()->query($sql)->fetchAll();
		
		$mistakeIndex = array();
		$lastId = 0;
		
		foreach ($mistakes as $mistake) {
			if ($mistake["group_id"] != $lastId) {
				$lastId = $mistake["group_id"];
				$mistakeIndex[$lastId] = array();
			}
			
			$mistakeIndex[$lastId][] = $mistake;
		}
		
		// nacteni ostatnich neshod
		$other = $tableMistakes->fetchAll(array(
				"audit_id = " . $audit->id,
				"workplace_id is null",
				"record_id is null"
		));
		
		$retVal = array("forms" => $mistakeIndex, "others" => $other->toArray());
		
		return $retVal;
	}
	
	public function loadReport(Audit_Model_Row_Audit $audit) {
		$tableReports = new Audit_Model_AuditsReports();
		$retVal = null;
		
		// vyhodnoceni existence zpravy
		if ($audit->report_id) {
			$retVal = $tableReports->find($audit->report_id)->current();
		} 

		if (is_null($retVal)) {
			$retVal = $tableReports->createRow();
		}
		
		return $retVal;
	}
}