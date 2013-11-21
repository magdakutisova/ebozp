<?php
class TaskController extends Zend_Controller_Action {
	
	public function commentAction() {
		// nacteni ukolu
		$task = self::loadTask($this->_request->getParam("taskId"));
		
		// kontrola formulare
		$form = new Application_Form_TaskComment();
		
		if (!$form->isValid($this->_request->getParams())) {
			$this->_forward("get.html");
			return;
		}
		
		// zapis komentare
		$comment = $task->createComment($form->getValue("comment"), Zend_Auth::getInstance()->getIdentity()->id_user);
		$this->view->comment = $comment;
		
		$this->_helper->FlashMessenger("Komentář přidán");
	}
	
	public function completeAction() {
		// nacteni dat
		$items = $this->_request->getParam("task", array());
		
		$taskIds = array(0);
		
		foreach ($items as $id => $checked) {
			if ($checked) {
				$taskIds[] = $id;
			}
		}
		
		// update dat
		$tableTasks = new Application_Model_DbTable_Task();
		$tableTasks->update(array(
				"completed_at" => new Zend_Db_Expr("NOW()"),
				"completed_by" => Zend_Auth::getInstance()->getIdentity()->id_user
				), array("id in (?)" => $taskIds));
		
		$this->_helper->FlashMessenger("Úkol splněn");
	}
	
	public function deleteAction() {
		
	}
	
	public function getAction() {
		// nacteni dat
		$task = self::loadTask($this->_request->getParam("taskId"));
		
		// nactei komentaru
		$comments = $task->findComments();
		
		// formular noveho komentare
		$formComment = new Application_Form_TaskComment();
		$formComment->setAction(sprintf("/task/comment?taskId=%s", $task->id));
		
		$this->view->task = $task;
		$this->view->comments = $comments;
		$this->view->formComment = $formComment;
	}
	
	public function getHtmlAction() {
		$this->getAction();
	}
	
	public function indexAction() {
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$subsidiary = $tableSubsidiaries->find($this->_request->getParam("subsidiaryId"))->current();
		
		$tableTasks = new Application_Model_DbTable_Task();
		$tasks = $tableTasks->findTasks($subsidiary->id_subsidiary, $subsidiary->client_id, $this->_request->getParam("filter", 1));
		
		$this->view->tasks = $tasks;
		$this->view->subsidiaryId = $subsidiary->id_subsidiary;
		$this->view->subsidiary = $subsidiary;
	}
	
	public function listAction() {
		// nacteni id pobocky
		$subsidiaryId = $this->_request->getParam("subsidiaryId");
		
		// nacteni ukolu
		$tableTasks = new Application_Model_DbTable_Task();
		$tasks = $tableTasks->findTasks($subsidiaryId, null, $this->_request->getParam("filter", 0));
		
		$this->view->tasks = $tasks;
		$this->view->subsidiaryId = $subsidiaryId;
	}
	
	public function listHtmlAction() {
		$this->listAction();
	}
	
	public function postAction() {
		$form = new Application_Form_Task();
		$form->setAction(sprintf("?subsidiaryId=%s", $this->_request->getParam("subsidiaryId")));
		
		if (strtoupper($this->_request->getMethod()) == "POST") {
			if ($form->isValid($this->_request->getParams())) {
				// vytvoreni tabulky a zapis dat
				$tableTasks = new Application_Model_DbTable_Task();
				$task = $tableTasks->createRow($form->getValues(true));
				
				$task->created_by = Zend_Auth::getInstance()->getIdentity()->id_user;
				$task->created_at = new Zend_Db_Expr("CURRENT_DATE");
				
				// nacteni pobocky
				$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
				$subsidiary = $tableSubsidiary->find($this->_request->getParam("subsidiaryId"))->current();
				
				if ($form->getValue("global")) {
					// ukol je globalni - vlozeni pro kazdou pobocku
					$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
					$nameSubsidiary = $tableSubsidiary->info("name");
					$nameTask = $tableTasks->info("name");
					$adapter = $tableSubsidiary->getAdapter();
					
					$select = new Zend_Db_Select($adapter);
					$select->from($nameSubsidiary, array(
							"id_subsidiary",
							new Zend_Db_Expr($task->created_by),
							new Zend_Db_Expr("CURRENT_DATE"),
							new Zend_Db_Expr($adapter->quote($task->task)),
							new Zend_Db_Expr($adapter->quote($task->description))))->where("client_id = ?", $subsidiary->client_id);
					
					$sqlP = "insert into %s (subsidiary_id, created_by, created_at, task, description) %s";
					$sql = sprintf($sqlP, $nameTask, $select);
					
					$adapter->query($sql);
					
					// nacteni zaznamu aktualni pobocky
					$task = $tableTasks->fetchRow(array("subsidiary_id = ?" => $subsidiary->id_subsidiary), "id desc");
				} else {
					$task->subsidiary_id = $subsidiary->id_subsidiary;
					$task->save();
				}
				
				$this->_helper->FlashMessenger("Úkol vytvořen");
				
				$this->view->task = $task;
			}
		}
		
		$this->view->form = $form;
	}
	
	public function postHtmlAction() {
		$this->postAction();
	}
	
	public function putAction() {
		$form = new Application_Form_Task();
		$task = self::loadTask($this->_request->getParam("taskId"));
		
		$form->setAction(sprintf("?taskId=%s", $task->id));
		$form->populate($task->toArray());
		$form->getElement("global")->setAttrib("disabled", "disabled");
		
		if (strtoupper($this->_request->getMethod()) == "POST") {
			if ($form->isValid($this->_request->getParams())) {
				$task->setFromArray($form->getValues(true));
				
				$task->save();
				$this->_helper->FlashMessenger("Změny byly uloženy");
			}
		}
		
		$this->view->form = $form;
	}
	
	public function putHtmlAction() {
		$this->putAction();
	}
	
	public static function loadTask($taskId) {
		$tableTasks = new Application_Model_DbTable_Task();
		$task = $tableTasks->find($taskId)->current();
		
		if (!$task) throw new Zend_Db_Table_Exception(sprintf("Task #$taskId not found", $taskId));
		
		return $task;
	}
}