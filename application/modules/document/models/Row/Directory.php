<?php
class Document_Model_Row_Directory extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci cestu k adresari (sve predky)
	 * 
	 * @return Document_Model_Rowset_Directories
	 */
	public function path() {
		$where = array(
				"left_id < " . $this->_data["left_id"],
				"right_id > " . $this->_data["right_id"]
		);
		
		return $this->getTable()->fetchAll($where, "left_id");
	}
	
	/**
	 * vraci adresare, ktere jsou primymi potomky serazene dle jmena
	 * 
	 * @return Document_Model_Rowset_Directories
	 */
	public function childDirs() {
		// vygenerovani dotazu
		$where = array("parent_id = " . $this->_data["id"]);
		
		return $this->getTable()->fetchAll($where, "name");
	}
	
	public function childDocs() {
		// sestaveni SQL dotazu
		$tableAssocs = new Document_Model_DirectoriesFiles();
		$tableFiles = new Document_Model_Files();
		$adapter = $tableAssocs->getAdapter();
		
		$nameAssocs = $adapter->quoteIdentifier($tableAssocs->info("name"));
		$nameFiles = $adapter->quoteIdentifier($tableFiles->info("name"));
		
		$sql = "select * from $nameFiles inner join $nameAssocs on file_id = id where directory_id = " . $adapter->quote($this->_data["id"]);
		$result = $adapter->query($sql)->fetchAll();
		
		return new Document_Model_Rowset_Files(array("data" => $result, "table" => $tableFiles, "rowClass" => $tableFiles->getRowClass()));
	}
	
	/**
	 * vraci soubor podle jmena
	 * pokud soubor v adresari neni, vraci NULL
	 * 
	 * @param string $name
	 * @return Document_Model_Row_File
	 */
	public function childFileByName($name) {
		// sestaveni dotazu
		$tableFiles = new Document_Model_Files();
		
		$retVal = $this->findManyToManyRowset($tableFiles, new Document_Model_DirectoriesFiles(), "directory", "file", $tableFiles->select(false)->where("name like ?", $name));
		
		return $retVal->current();
	}
	
	/**
	 * vytvori novy podadresar
	 * 
	 * @param string $name jmeno adresare
	 * @return Document_Model_Row_Directory
	 */
	public function createChildDir($name) {
		// vytvoreni potomka
		$table = $this->getTable();
		$retVal = $table->createRow(array("parent_id" => $this->_data["id"], "name" => $name));
		
		// nastaveni left_id a right_id
		$retVal->left_id = $this->_data["left_id"] + 1;
		$retVal->right_id = $this->_data["left_id"] + 2;
		$retVal->root_id = $this->_data["root_id"];
		$retVal->client_id = $this->_data["client_id"];
		
		// provedeni update
		$this->_setOffset(2, $this->_data["root_id"], $this->_data["left_id"]);
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vraci podstrom adresare
	 * podstrom obsahuje i svuj koren (tedy tento prvek)
	 */
	public function subtree() {
		// nacteni dat serazenych dle id predka a dle jmena 
		$where = array(
				"left_id > ?" => $this->_data["left_id"],
				"right_id < ?" => $this->_data["right_id"],
				"client_id = ?" => $this->_data["client_id"]
		);
		
		$items = $this->getTable()->fetchAll($where, array("parent_id", "name"));
		
		// vygenerovani vysledku
		$retVal = new stdClass();
		$retVal->children = array();
		$retVal->dir = $this;
		
		// zapis dat
		$itemIndex = array();			// asociativni pole, kde klicem je id adresare a obsahem jeho zaznam z hierarchie
		$itemIndex[$this->_data["id"]] = $retVal;
		
		foreach ($items as $item) {
			// inicializace objektu a zapis do indexu
			$itemObj = new stdClass();
			$itemObj->children = array();
			$itemObj->dir = $item;
			$itemIndex[$item->id] = $itemObj;
			
			// zapis do predka
			$itemIndex[$item->parent_id]->children[] = $itemObj;
		}
		
		return $retVal;
	}
	
	public function delete() {
		$diff = $this->_data["left_id"] - $this->_data["right_id"] - 1;
		$rootId = $this->_data["root_id"];
		$leftId = $this->_data["left_id"];
		$retVal = parent::delete();
		
		$this->_setOffset($diff, $rootId, $leftId);
		
		return $retVal;
	}
	
	private function _setOffset($offset, $rootId, $leftId) {
		$left = "left_id ";
		$right = "right_id ";
		
		if ($offset > 0) {
			$left .= "+ $offset";
			$right .= "+ $offset";
		} else {
			$left .= "- " . abs($offset);
			$right .= "- " . abs($offset);
		}
		
		$table = $this->getTable();
		
		$table->update(array("right_id" => new Zend_Db_Expr($right)), array("right_id > " . $leftId, "root_id = " . $rootId));
		$table->update(array("left_id" => new Zend_Db_Expr($left)), array("left_id > " . $leftId, "root_id = " . $rootId));
	}
}