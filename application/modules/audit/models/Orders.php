<?php
class Audit_Model_Orders extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_orders";
	
	protected $_primary = array("id");
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"watch" => array(
					"columns" => "watch_id",
					"refTableClass" => "Audit_Model_Watches",
					"refColumns" => "id"
					)
	);
	
	public function findOrder($id) {
		$select = $this->prepareSelect();
		
		$select->where("o.id = ?", $id);
		
		$data = $select->query()->fetch();
		$cls = $this->_rowClass;
		
		return new $cls(array("data" => $data, "table" => $this));
	}
	
	public function findOrders($selectDone = false, $selectUndone = true) {
		// vytvoreni dotazu
		$select = $this->prepareSelect();
		
		// pridani podminek
		if (!$selectDone) {
			$select->where("o.finished_at IS NULL");
		}
		
		if (!$selectUndone) {
			$select->where("o.finished_at IS NOT NULL");
		}
		
		$select->order(new Zend_Db_Expr("o.finished_at IS NOT NULL"))->order("o.id DESC");
		
		$result = $select->query()->fetchAll();
		
		return new Zend_Db_Table_Rowset(array(
				"data" => $result,
				"rowClass" => $this->_rowClass
				));
	}
    
    /**
     * pokusi se najit radek s obednavkou pro dohlidku
     * pokud radek neexistuje, vytvori se novy
     * 
     * @param int $watchId identifikacni cislo dohlidky
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOrCreateRow($watchId, $clientId = null, $subsidiaryId = null) {
        $row = $this->fetchRow(array("watch_id = ?" => $watchId));
        
        if (!$row) {
            $row = $this->createRow();
            $row->watch_id = $watchId;
            $row->client_id = $clientId;
            $row->subsidiary_id = $subsidiaryId;
            $row->save();
        }
        
        return $row;
    }
	
	public function prepareSelect() {
		// sestaveni dotazu
		$tableSubsidiary = new Application_Model_DbTable_Subsidiary();
		$tableWatches = new Audit_Model_Watches();
		
		$nameWatches = $tableWatches->info("name");
		$nameSubsidiary = $tableSubsidiary->info("name");
		
		$tableUser = new Application_Model_DbTable_User();
		$nameUser = $tableUser->info("name");
		
		$select = new Zend_Db_Select($this->getAdapter());
		
		$select->from(array("o" => $this->_name));
		
		// napojeni dohlidky
		$select->joinLeft(array("w" => $nameWatches), "watch_id = w.id", array("watched_at"));
		
		// napojeni pobocky
		$select->joinInner(array("subs" => $nameSubsidiary), "subs.id_subsidiary = o.subsidiary_id", array(
				"subsidiary_name" => new Zend_Db_Expr("CONCAT(subsidiary_name, ' - ', subsidiary_town, ', ', subsidiary_street)")
				));
		
		// pirpojeni kontaktni osoby
		$tableContacts = new Application_Model_DbTable_ContactPerson();
		$nameContacts = $tableContacts->info("name");
        
        // pripojeni osoby z obednavky mimo dohlidku
        $select->joinLeft(array("u2" => $nameUser), "o.user_id = u2.id_user", array());
		
		$select->joinLeft(array("cp" => $nameContacts), "cp.id_contact_person = w.contactperson_id", array(
				"contact_person_name" => new Zend_Db_Expr("CONCAT(IFNULL(w.contact_name, ''), IFNULL(cp.name, ''), IFNULL(u2.name, ''))"),
				"contact_person_phone" => new Zend_Db_Expr("CONCAT(IFNULL(w.contact_phone, ''), IFNULL(cp.phone, ''))"),
				"contact_person_email" => new Zend_Db_Expr("CONCAT(IFNULL(w.contact_email, ''), IFNULL(cp.email, ''))")
		));

		// napojeni uzivatele, ktery polozku vyridil
		$select->joinLeft(array("u1" => $nameUser), "o.finished_by = u1.id_user", array("login" => "username", "username" => "name"));
        
        $select->where("w.is_closed OR watch_id IS NULL");
		
		return $select;
	}
}
