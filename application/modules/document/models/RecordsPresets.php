<?php
class Document_Model_RecordsPresets extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_records_presets";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	public function getCommons() {
		return $this->fetchAll("is_general", "name");
	}
	
	public function getNoCommons() {
		return $this->fetchAll("!is_general", "name");
	}
	
	public function resetClient($clientId) {
		// reset obecne dokumentace
		$this->resetGeneral($clientId);
		
		// smazani vsech pobocek
		$tableDocs = new Document_Model_Documentations();
		$tableDocs->delete(array("subsidiary_id is not null", "client_id = ?" => $clientId));
		
		// sestaveni dotazu pro soucin
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$adapter = $this->getAdapter();
		
		$nameDocs = $adapter->quoteIdentifier($tableDocs->info("name"));
		$namePresets = $adapter->quoteIdentifier($this->_name);
		$nameSubsidiaries = $adapter->quoteIdentifier($tableSubsidiaries->info("name"));
		
		// sestaveni dotazu
		$clientIdQ = $adapter->quote($clientId);
		$insertSql = "select $clientIdQ, id_subsidiary, name from $namePresets, $nameSubsidiaries where !is_general and client_id = $clientIdQ";
		$sql = "insert into $nameDocs (client_id, subsidiary_id, name) $insertSql";
		
		$adapter->query($sql);
	}
	
	public function resetGeneral($clientId) {
		$tableRecs = new Document_Model_Records();
		
		// smazani stare dokumentace
		$tableRecs->delete(array("subsidiary_id is null", "client_id = ?" => $clientId));
		
		// zapis nove dokumentace
		$adapter = $this->getAdapter();
		$nameRecs = $adapter->quoteIdentifier($tableRecs->info("name"));
		$namePresets = $adapter->quoteIdentifier($this->_name);
		
		// sestaveni dotazu
		$clientIdQ = $adapter->quote($clientId);
		
		$sql = "insert into $nameRecs (client_id, name) select $clientIdQ, name from $namePresets where is_general";
		$adapter->query($sql);
	}
	
	public function resetSubsidiary($clientId, $subsidiaryId) {
		$tableRecs = new Document_Model_Records();
		
		// smazani stare dokumentace
		$tableRecs->delete(array("subsidiary_id = ?" => $subsidiaryId));
		
		// zapis nove dokumentace
		$adapter = $this->getAdapter();
		$nameRecs = $adapter->quoteIdentifier($tableRecs->info("name"));
		$namePresets = $adapter->quoteIdentifier($this->_name);
		
		// sestaveni dotazu
		$clientIdQ = $adapter->quote($clientId);
		$subsidiaryIdQ = $adapter->quote($subsidiaryId);
		
		$sql = "insert into $nameRecs (client_id, subsidiary_id, name) select $clientIdQ, $subsidiaryIdQ, name from $namePresets where !is_general";
		$adapter->query($sql);
	}
}