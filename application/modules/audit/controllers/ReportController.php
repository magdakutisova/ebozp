<?php
class Audit_ReportController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->addHelperPath(APPLICATION_PATH . "/views/helpers");
		$this->view->headLink()->appendStylesheet("/css/jquery.jqplot.css");
		
		$this->view->layout()->setLayout("client-layout");
	}
	
	/**
	 * prvnotni vytvoreni z auditu
	 */
	public function createAction() {
		$audit = $this->loadAudit();
		$this->_request->setParam("subsidiaryId", $audit->subsidiary_id);
		
		// nacteni nacionalii
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		$auditor = $audit->getAuditor();
		
		// nacteni formularu
		$forms = $audit->getForms();
		$formsGroups = self::getFormsGroups($forms);
		$forms = $forms->toArray();
		
		
		// nacteni pracovist
		$workplaces = self::getWorkplaces($audit);
		
		// nacteni neshod
		$mistakes = self::getMistakes($audit);
		
		// nacteni zastupce klienta
		if ($audit->contactperson_id) {
			$tableContacts = new Application_Model_DbTable_ContactPerson();
			$this->view->contact = $tableContacts->find($audit->contactperson_id)->current();
		}
		
		// pokud neni pobocka centrala, nacte se centrala
		if ($subsidiary->hq) {
			$hq = $subsidiary;
		} else {
			$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
			
			$hq = $tableSubsidiaries->fetchRow(array(
					"client_id = ?" => $subsidiary->client_id,
					"hq"
					));
		}
		
		// nacteni lhut
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$deadlines = $tableDeadlines->findExtendedByAudit($audit, true);
        
        if ($audit->display_deadlines_close) {
            $tableDeadlines = new Deadline_Model_Deadlines();
            $select = $tableDeadlines->_prepareSelect();
            $select->where("d.subsidiary_id = ?", $audit->subsidiary_id)->where("next_date > NOW()")->having("invalid_close");
            
            $data = $select->query()->fetchAll();
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => $data, "table" => $tableDeadlines, "stored" => true));
        } else {
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => array()));
        }
		
		$this->view->deadlines = $deadlines;
        $this->view->deadlinesClose = $deadlinesClose;
        
		$this->view->audit = $audit;
		$this->view->client = $client;
		$this->view->auditor = $auditor;
		$this->view->subsidiary = $subsidiary;
		$this->view->hq = $hq;
		
		$this->view->forms = $forms;
		$this->view->formsGroups = $formsGroups;
		
		$this->view->workplaces = $workplaces;
		
		$this->view->mistakes = $mistakes;
	}
	
	public function getAction() {
		$this->editAction();
	}
	
	public function reportPdfAction() {
		$audit = $this->loadAudit();
		$report = $this->loadReport($audit);
		
		$charts = (array) $this->getRequest()->getParam("chart", array());
		
		// nacteni lhut
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$deadlines = $tableDeadlines->findExtendedByAudit($audit, true);
        
        if ($audit->display_deadlines_close) {
            $tableDeadlines = new Deadline_Model_Deadlines();
            $select = $tableDeadlines->_prepareSelect();
            $select->where("d.subsidiary_id = ?", $audit->subsidiary_id)->where("next_date > NOW()")->having("invalid_close");
            
            $data = $select->query()->fetchAll();
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => $data, "table" => $tableDeadlines, "stored" => true));
        } else {
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => array()));
        }
		
		$this->view->deadlines = $deadlines;
        $this->view->deadlinesClose = $deadlinesClose;
		
		$this->view->report = $report;
		$this->view->audit = $audit;
		$this->view->items = $audit->getProgres();
		$this->view->charts = $charts;
		
		$this->view->disableHeaders = $this->_request->getParam("disableHeaders", 0);
		
		$forms = $audit->getForms();
		$formsGroups = self::getFormsGroups($forms);
		$forms = $forms->toArray();
		
		
		$this->view->forms = $forms;
		$this->view->formsGroups = $formsGroups;
		$this->view->workplaces = self::getWorkplaces($audit);
		$this->view->mistakes = self::getMistakes($audit);
		
		$this->view->image = __DIR__ . "/../resources/helmet.png";
		$this->view->logo = __DIR__ . "/../resources/logo.png";
	}
	
	public function editAction() {
		$audit = $this->loadAudit();
		$report = $this->loadReport($audit);
		$this->_request->setParam("subsidiaryId", $audit->subsidiary_id);
		
		// nacteni nacionalii
		$client = $audit->getClient();
		$subsidiary = $audit->getSubsidiary();
		$auditor = $audit->getAuditor();
		
		// nacteni itemu
		$items = $audit->getProgres();
		
		// nacteni formularu
		$forms = $audit->getForms();
		$formsGroups = self::getFormsGroups($forms);
		$forms = $forms->toArray();
		
		
		// nacteni pracovist
		$workplaces = self::getWorkplaces($audit);
		
		// nacteni neshod
		$mistakes = self::getMistakes($audit);
		
		// nacteni lhut
		$tableDeadlines = new Audit_Model_AuditsDeadlines();
		$deadlines = $tableDeadlines->findExtendedByAudit($audit, true);
        
        if ($audit->display_deadlines_close) {
            $tableDeadlines = new Deadline_Model_Deadlines();
            $select = $tableDeadlines->_prepareSelect();
            $select->where("d.subsidiary_id = ?", $audit->subsidiary_id)->where("next_date > NOW()")->having("invalid_close");
            
            $data = $select->query()->fetchAll();
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => $data, "table" => $tableDeadlines, "stored" => true));
        } else {
            $deadlinesClose = new Zend_Db_Table_Rowset(array("data" => array()));
        }
		
		$this->view->deadlines = $deadlines;
		
		$this->view->audit = $audit;
		$this->view->client = $client;
		$this->view->auditor = $auditor;
		$this->view->subsidiary = $subsidiary;
		$this->view->report = $report;
		$this->view->items = $items;
		
		$this->view->forms = $forms;
		$this->view->formsGroups = $formsGroups;
		
		$this->view->workplaces = $workplaces;
		
		$this->view->mistakes = $mistakes;
        $this->view->deadlinesClose = $deadlinesClose;
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
				"contact_name" => "",
				"progres_note_caption" => "",
				"progres_caption" => "",
				"item" => array(),
				"progress_note" => "",
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
		}
        
        $audit->progress_note = $data["progress_note"];
        $audit->summary = $data["summary"];
		$audit->save();
		
		// zapis polozek cilu
		$tableItems = new Audit_Model_AuditsProgresitems();
		$tableItems->delete("audit_id = " . $audit->id);
		
		foreach ($data["item"] as $item) {
			$tableItems->insert(array(
					"audit_id" => $audit->id,
					"content" => $item
			));
		}
		
		$this->_helper->FlashMessenger("Zpráva byla uložena");
		
		// presmerovani na editaci zpravy
		$url = $this->view->url(array("auditId" => $audit->id, "subsidiaryId" => $audit->subsidiary_id, "clientId" => $audit->client_id), "audit-report-edit");
		$this->_redirect($url);
	}
	
	public function sendAction() {
		// kontrolni nacteni
		$audit = self::loadAudit($this->_request->getParam("auditId"));
        $subsidiary = $audit->getSubsidiary();
	
		if (!$audit->is_closed) throw new Zend_Exception("Audit #$audit->id must be closed for send protocol");
	
		// vygenerovani protokolu
		$pdfProt = $this->view->action("report.pdf", "report", "audit", array_merge($this->_request->getParams(), array("disableHeaders" => 1)));
	
		// vyhodnoceni kontaktni osoby
		if ($audit->contactperson_id) {
			$tableContacts = new Application_Model_DbTable_ContactPerson();
			$contact = $tableContacts->find($audit->contactperson_id)->current();
				
			$email = $contact->email;
			$name = $contact->name;
		} else {
			$email = $audit->contact_email;
			$name = $audit->contact_name;
		}
	
		$msg = self::generateMail("Dobrý den,

v příloze zasíláme závěrečnou zprávu o provedení roční prověrky bezpečnosti práce a požární ochrany.

S pozdravem

GUARD7, v.o.s.", $pdfProt, "guardian@guard7.cz", $email, $subsidiary, $audit);

		mail('', "=?UTF-8?B?" . base64_encode("Závěrečná zpráva o provedení roční prověrky BOZP a PO") . "?=", $msg["message"], $msg["headers"]);
	
		$this->view->audit = $audit;
		
		$this->_helper->FlashMessenger("Protokol byl odeslán");
	}
	
	public function loadAudit() {
		$auditId = $this->getRequest()->getParam("auditId", 0);
		$tableAudits = new Audit_Model_Audits();
		
		$audit = $tableAudits->getById($auditId);
		if (!$audit) throw new Zend_Exception("Audit #$auditId has not been found");
		
		return $audit;
	}
	
	public static function getFormsGroups($forms) {
		$tableCategories = new Audit_Model_FormsCategories();
		$tableRecords = new Audit_Model_AuditsRecords();
		$adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$retVal = array();
		
		foreach ($forms as $form) {
			// nacteni skupin a zaznamu
			$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
			$nameQuestions = $tableQuestions->info("name");
			$nameRecords = $tableRecords->info("name");
			
			$select = new Zend_Db_Select($tableCategories->getAdapter());
			$select->from($nameQuestions, array("group_id"))
					->joinInner($nameRecords, "question_id = $nameQuestions.id", array())
					->where("audit_form_id = ?", $form->id);
			
			$groups = $tableCategories->fetchAll(array("id in (?)" => new Zend_Db_Expr($select->assemble())), "position");
			$records = $form->getRecords(null, "group_id");
			
			// indexace zaznamu
			$lastGroupId = 0;
			$groupInfo = array();
			$allNt = false;
			
			foreach ($records as $record) {
				if ($record->group_id != $lastGroupId) {
					if ($allNt) {
						unset($groupInfo[$lastGroupId]);
					}
					
					$lastGroupId = $record->group_id;
					$groupInfo[$lastGroupId] = array(
							"max" => 0,
							"gained" => 0
					);
					
					$allNt = true;
				}
				
				if ($record->score == Audit_Model_AuditsRecords::SCORE_NT) continue;
				$allNt = false;
				
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
        $tableAssocs = new Audit_Model_AuditsMistakes();
        $nameAssocs = $tableAssocs->info("name");
        
		$mistakes = $tableMistakes->fetchAll(array(
				"id in (select mistake_id from $nameAssocs where audit_id = ?)" => $audit->id,
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
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		
		$nameRecords = $tableRecords->info("name");
		$nameMistakes = $tableMistakes->info("name");
		$nameQuestions = $tableQuestions->info("name");
		
		// sestaveni dotazu
		$select = new Zend_Db_Select($tableMistakes->getAdapter());
		$select->from($nameMistakes, array(
						"$nameMistakes.id", 
						"$nameMistakes.weight", 
						"$nameMistakes.mistake", 
						"$nameMistakes.suggestion", 
						"comment")
				)->joinInner($nameRecords, "$nameMistakes.record_id = $nameRecords.id", array())
				->joinInner($nameQuestions, "$nameRecords.question_id = $nameQuestions.id", array("group_id"))
				->where("$nameMistakes.audit_id = ?", $audit->id)
				->where("$nameRecords.score = ?", Audit_Model_AuditsRecords::SCORE_N);
		
		$sql = "select group_id, $nameMistakes.id, $nameMistakes.weight, mistake, suggestion, comment from $nameMistakes, $nameRecords where $nameMistakes.record_id = $nameRecords.id and $nameRecords.audit_id = $audit->id and score = " . Audit_Model_AuditsRecords::SCORE_N;
		$mistakes = $select->query()->fetchAll();
		
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
		$tableAssocs = new Audit_Model_AuditsMistakes();
		$nameAssocs = $tableAssocs->info("name");
		
		$subSelect = new Zend_Db_Select($tableMistakes->getAdapter());
        
        // vyhodnoceni, ktere neshody zobrazit
        $statusList = array("status = 1");
        
        if ($audit->display_mistakes) {
            $statusList[] = "status = 0";
        }
        
        if ($audit->display_mistakes_removed) {
            $statusList[] = "status = 2";
        }
        
		$subSelect->from(array("a" => $nameAssocs), array("status"))->where("a.audit_id = ?", $audit->id)->where("(" . implode(" or ", $statusList) . ")");
		$subSelect->where("a.record_id IS NULL");
        
        $subSelect->joinInner(array("m" => $nameMistakes), "m.id = a.mistake_id");
        
		$retVal = array("forms" => $mistakeIndex, "others" => $subSelect->query()->fetchAll());
		
		return $retVal;
	}
	
	/**
	 * nacteni zpravu nebo vytvori novou
	 * 
	 * @param Audit_Model_Row_Audit $audit
	 * @return Audit_Model_Row_AuditReport
	 */
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
	
	private static function generateMail($messageContent, $pdf, $from, $to, $subsidiary, $audit) {
		// vygenerovani hranice
		$boundary = uniqid('np');
		$boundary2 = $boundary . "2";
        
        // vygenerovani jmena souboru
        $fileName = sprintf("%s, %s, %s - %s", $subsidiary->subsidiary_name, $subsidiary->subsidiary_town, $subsidiary->subsidiary_street, $audit->done_at);
        $nameHeader = "=protokol.pdf";
        $nameHeader = "*=UTF-8''" . str_replace("+", "%20", urlencode($fileName));
        
		// hlavicky
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "From: $from \r\n";
		$headers .= "To: $to\r\n";
        $headers .= "Cc: podklady@guard7.cz\r\n";
		$headers .= "Content-Type: multipart/mixed;boundary=" . $boundary . "\r\n";
	
		$message .= "\r\n\r\n--" . $boundary . "\r\n";
		$message .= "Content-Type: text/plain; charset=utf-8\r\n";
		$message .= "Content-Disposition: inline\r\n";
		$message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
		$message .= $messageContent;
	
		$message .= "\r\n\r\n--$boundary\r\n";
		$message .= "Content-Transfer-Encoding: base64\r\n";
		$message .= "Content-Disposition: attachment; filename$nameHeader\r\n";
		$message .= "Content-type: application/pdf; name$nameHeader\r\n\r\n";
	
		$message .= base64_encode($pdf);
		$message .= "\r\n\r\n--" . $boundary . "--";
	
		return array("message" => $message, "headers" => $headers);
	}
}