<?php
class Audit_Model_AuditsDeadlines extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_deadlines";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
					),
			
			"deadline" => array(
					"columns" => "deadline_id",
					"refTableClass" => "Deadline_Model_Deadlines",
					"refColumns" => "id"
					)
			);
	
	protected $_rowClass = "Audit_Model_Row_WatchDeadline";
	
	protected $_rowsetClass = "Audit_Model_Rowset_WatchesDeadlines";
	
	/**
	 * vytvori asociacni zaznamy vztahujici se k dohlidce
	 * 
	 * @param Audit_Model_Row_Watch $watch radek dohlidky
	 * @return int
	 */
	public function createByAudit(Audit_Model_Row_Audit $audit) {
		// vytvoreni tabulky lhut a nacteni jejiho jmena
		$tableDeadlines = new Deadline_Model_Deadlines();
		$nameDeadlines = $tableDeadlines->info("name");
		
		// sesta eni vyhledavaciho dotazu
		$select = $tableDeadlines->select(true);
		$select->reset(Zend_Db_Select::COLUMNS);
		
		$select->columns(array(new Zend_Db_Expr($audit->id), "id", "next_date", "is_over" => new Zend_Db_Expr("next_date < NOW()")));
		
		$select->where("subsidiary_id = ?", $audit->subsidiary_id)
					->where("(next_date < NOW() OR next_date IS NULL)");
		
		// sestaveni insertniho dotazu
		$adapter = $this->getAdapter();
		$sql = sprintf("insert into %s (audit_id, deadline_id, valid_to, is_over) %s", 
				$adapter->quoteIdentifier($this->_name),
				$select);
		
		// odeslani dat
		return $adapter->query($sql)->rowCount();
	}
	
	public function findByAuditDeadline($auditId, $deadlineId) {
		return $this->fetchRow(array(
				"audit_id = ?" => $auditId,
				"deadline_id = ?" => $deadlineId
				));
	}
	
	/**
	 * najde informace o lhutach zahrnutych v dohlidce
	 * informace jsou rozsireny o data z asociacni tabulky
	 * 
	 * @param Audit_Model_Row_Watch $watch
	 * @return Zend_Db_Table_Rowset_Abstract
	 */
	public function findExtendedByAudit(Audit_Model_Row_Audit $audit, $undoneOnly = false) {
		// sestaveni dotazu
		$tableDeadlines = new Deadline_Model_Deadlines();
		$select = $tableDeadlines->_prepareSelect();
        
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
        $nameSubsidiaries = $tableSubsidiaries->info("name");
		
		// vlozeni omezeni na asociovane lhuty
		$subSelect = new Zend_Db_Select($this->getAdapter());
		$subSelect->from($this->_name, array("deadline_id"))->where("audit_id = ?", $audit->id);
		
		if ($undoneOnly) {
			$subSelect->where("!is_done");
		}
		
		$select->where("deadline_deadlines.id in ?", $subSelect);
		
		$select->joinInner(array("dt" => $this->_name), "dt.deadline_id = deadline_deadlines.id and dt.audit_id = " . $audit->id, array("is_done"));
		$select->joinInner($nameSubsidiaries, "id_subsidiary = subsidiary_id", array("subsidiary_street", "subsidiary_name", "subsidiary_town"));
        
		$data = $select->query()->fetchAll();
		
		return new Zend_Db_Table_Rowset(array("data" => $data, "readOnly" => true));
	}
}