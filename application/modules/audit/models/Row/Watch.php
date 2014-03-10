<?php
class Audit_Model_Row_Watch extends Zend_Db_Table_Row_Abstract {
	
	public function getClient() {
		return $this->findParentRow("Application_Model_DbTable_Client", "client");
	}
	
	public function getSubsidiary() {
		return $this->findParentRow("Application_Model_DbTable_Subsidiary", "subsidiary");
	}
	
	public function getContactPerson() {
		return $this->findParentRow("Application_Model_DbTable_ContactPerson", "contact");
	}
	
	public function getUser() {
		return $this->findParentRow("Application_Model_DbTable_User", "user");
	}
    
	public function findDiscussed() {
		$tableDiscussed = new Audit_Model_WatchesDiscussed();
		
		return $tableDiscussed->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
	
	public function findChanges() {
		$tableChanges = new Audit_Model_WatchesChanges();
	
		return $tableChanges->fetchAll(array("watch_id = ?" => $this->_data["id"]), "id");
	}
	
	public function findOrder() {
		$tableOrders = new Audit_Model_Orders();
	
		return $tableOrders->getOrCreateRow($this->id, $this->client_id, $this->subsidiary_id);
	}
	
	public function findMistakes($disableRemoved = false) {
		// sestaveni vyhledavaciho dotazu
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAssocs = new Audit_Model_WatchesMistakes();
        $tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		
		$nameMistakes = $tableMistakes->info("name");
		$nameAssocs = $tableAssocs->info("name");
        $nameSubsidiaries = $tableSubsidiaries->info("name");
		
		$select = new Zend_Db_Select(Zend_Db_Table_Abstract::getDefaultAdapter());
		$select->from($nameAssocs, array("set_removed" => "set_removed"))->where("$nameAssocs.watch_id = ?", $this->id);
		$select->joinInner($nameMistakes, "id = mistake_id");
		$select->joinInner($nameSubsidiaries, "$nameMistakes.subsidiary_id = id_subsidiary", array("subsidiary_name", "subsidiary_town", "subsidiary_street"));
        
		// provazani s pracovisti
		$tableWorkplaces = new Application_Model_DbTable_Workplace();
		$nameWorkplaces = $tableWorkplaces->info("name");
		
		$select->joinLeft($nameWorkplaces, "id_workplace = workplace_id", array("workplace_name" => "name"));
                
        // vyhodnoceni, zda se maji vyradit z vyberu odebrane neshody
        if ($disableRemoved) {
            $select->where("!set_removed");
        }
		
		$data = $select->query()->fetchAll();
		$retVal = new Audit_Model_Rowset_AuditsRecordsMistakes(array(
				"data" => $data, 
				"rowClass" => $tableMistakes->getRowClass(),
				"readOnly" => true));
		
		return $retVal;
	}
}