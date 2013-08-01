<?php
class Audit_Model_WatchesMistakes extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_watches_mistakes";
	
	protected $_primary = array("watch_id", "mistake_id");
	
	protected $_sequence = false;
	
	protected $_referenceMap = array(
			"watch" => array(
					"columns" => "watch_id",
					"refTableClass" => "Audit_Model_Watches",
					"refColumns" => "id"
					),
			
			"mistake" => array(
					"columns" => "mistake_id",
					"refTableClass" => "Audit_Model_AuditsRecordsMistakes",
					"refColumns" => "id"
					)
	);
	
	/**
	 * zapise asociace mezi neodstranenymi neshodami a dohlidkou
	 * @param unknown_type $watch
	 */
	public function insertByWatch($watch) {
		// prirpava tabulky a selectu
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$select = new Zend_Db_Select($this->getAdapter());
		$select->from($tableMistakes->info("name"), array("id", new Zend_Db_Expr($watch->id)));
		
		// vyhledavaci podminky pro filtraci pobocky a neodstranenych neshod
		$select->where("subsidiary_id = ?", $watch->subsidiary_id);
		$select->where("!is_removed");
		
		// zapis dat
		$sql = "insert into " . $this->_name . " (mistake_id, watch_id) " . $select->assemble();
		$this->getAdapter()->query($sql);
		
		return $this;
	}
}