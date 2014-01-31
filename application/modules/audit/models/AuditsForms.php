<?php
class Audit_Model_AuditsForms extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_forms";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"form" => array(
					"columns" => "form_id",
					"refTableClass" => "Audit_Model_Forms",
					"refColumns" => "id"
			),
			
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditForm";
	
	protected $_rowsetClass = "Audit_Model_Rowset_AuditsForms";
	
	/**
	 * vytvori instanci noveho formulare
	 * 
	 * @param Audit_Model_Row_Audit $audit
	 * @param Audit_Model_Row_Form $form
     * @param bool $useTransaction prepinac pro vypnuti pouziti tansakce
	 * @return Audit_Model_Row_AuditForm
	 */
	public function createForm(Audit_Model_Row_Audit $audit, Audit_Model_Row_Form $form, $useTransaction = true) {
		// nacteni jmen
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableAuditsMistakes = new Audit_Model_AuditsMistakes();
		$tableItems = new Audit_Model_FormsCategoriesQuestions();
		$tableCategories = new Audit_Model_FormsCategories();
		
		$nameRecords = $tableRecords->info("name");
		$nameMistakes = $tableMistakes->info("name");
		$nameAuditMistakes = $tableAuditsMistakes->info("name");
		$nameItems = $tableItems->info("name");
		$nameCategories = $tableCategories->info("name");
		
		// zacatek transakce a zjisteni id
		$adapter = $this->getAdapter();
		
		if ($useTransaction) $adapter->beginTransaction();
		$adapter->query("set foreign_key_checks = 0;");
		
		$retVal = $this->createRow(array(
				"form_id" => $form->id,
				"audit_id" => $audit->id,
				"name" => $form->name
		));
		
		$retVal->save();
		
		// nacteni skupin otazek
		$groupList = $form->findCategories();
		$adapter->query("lock tables `$this->_name` write, `$nameMistakes` write, `$nameRecords` write, `$nameAuditMistakes` write, `$nameItems` write, `$nameCategories` write");
		
		try {
			// nacteni poslednich id zaznamu
			$recordId = $adapter->query("select Auto_increment from information_schema.tables where table_name='$nameRecords' and table_schema = DATABASE()")->fetchColumn();
			$mistakeId = $adapter->query("select Auto_increment from information_schema.tables where table_name='$nameMistakes' and table_schema = DATABASE()")->fetchColumn();
			
			// nacteni prvku formulare
			$sql = "select `$nameItems`.* from `$nameItems` inner join `$nameCategories` on $nameCategories.id = $nameItems.group_id where !$nameItems.is_deleted and form_id = " . $retVal->form_id . " order by group_id, position";
			$items = $adapter->query($sql);
			$itemIndex = array();
			
			$thisList = array();
			$lastId = 0;
			
			// indexace dle kategorie
			while ($item = $items->fetch()) {
				if ($item["group_id"] != $lastId) {
					$itemIndex[$lastId] = $thisList;
					$thisList = array();
					$lastId = $item["group_id"];
				}
				
				$thisList[] = $item;
			}
			
			// zapis poslednich dat
			$itemIndex[$lastId] = $thisList;
			
			// priprava dat
			$mistakes = array();
			$records = array();
			$assocs = array();
			
			$null = new Zend_Db_Expr("NULL");
			
			foreach ($groupList as $group) {
				// zapis prvku
				$items = $itemIndex[$group->id];
				$maxI = count($items);
				
				for ($i = 0; $i < $maxI; $i++)
				{
					// zjisteni dat o otazce
					$item = $items[$i];
					
					// vygenerovani zaznamu
					$record = array(
							$recordId,
							$audit->id,
							$retVal->id,
							$mistakeId,
							Audit_Model_AuditsRecords::SCORE_NT,
							$adapter->quote($item["id"])
					);
					
					$records[] = "(" . implode(",", $record) . ")";
					
					// vygenerovani neshody
					$mistake = array(
							$mistakeId,													// id zaznamu
							$audit->id,													// id auditu
							$audit->client_id,											// id klienta
							$audit->subsidiary_id,										// id pobocky
							$recordId,													// od zaznamu auditu
							$adapter->quote($item["id"]),								// id itemu dotazniku
							$adapter->quote($item["weight"]),							// zavaznost
							$adapter->quote($item["question"]),							// otazka
							$adapter->quote($item["category"]),							//kategorie
							$adapter->quote($item["subcategory"]),						//podkategorie
							$adapter->quote($item["concretisation"]),					//specifikace
							$adapter->quote($item["mistake"]),							//neshoda
							$adapter->quote($item["suggestion"]),						//navrh
							$adapter->quote($item["mistake_comment"]),					// komentar
							"''"														// zodpovedna osoba
					);
					
					$mistakes[] = "(" . implode(",", $mistake) . ")";
					
					$recordId++;
					$mistakeId++;
				}
			}
			
			// zapis zaznamu
			if ($records) {
				$sqlBase = "insert into $nameRecords (id, audit_id, audit_form_id, mistake_id, score, question_id) values ";
				
				$chunks = array_chunk($records, 1000);
				
				foreach ($chunks as $chunk) {
					$sql = $sqlBase . implode(",", $chunk);
					$adapter->query($sql);
				}
			}
			
			// zapis neshod
			if ($mistakes) {
				$sqlBase = "insert into `$nameMistakes` (id, audit_id, client_id, subsidiary_id, record_id, item_id, weight, question,category,subcategory,concretisation,mistake, suggestion, comment, responsibile_name) values ";
				
				$chunks = array_chunk($mistakes, 1000);
				
				foreach ($chunks as $chunk) {
					$sql = $sqlBase . implode(",", $chunk);
					$adapter->query($sql);
				}
			}
			
			if ($useTransaction) $adapter->commit();
		} catch (Zend_Exception $e) {
			if ($useTransaction) $adapter->rollBack();
			throw $e;
		}
		
		$adapter->query("set foreign_key_checks = 1;");
		$adapter->query("unlock tables");
		
		return $retVal;
	}
	
	/**
	 * vraci formulare dle auditu
	 * 
	 * @param Audit_Model_Row_Audit $audit
	 * @return Audit_Model_Rowset_AuditsForms
	 */
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		return $this->fetchAll("audit_id = " . $audit->id, "name");
	}
	
	/**
	 * vraci radek instance dle auditu a formulare
	 * 
	 * @param Audit_Model_Row_Audit $audit radek auditu
	 * @param Audit_Model_Row_Form $form radek formulare
	 * @return Audit_Model_Row_AuditForm
	 */
	public function getByAuditAndForm(Audit_Model_Row_Audit $audit, Audit_Model_Row_Form $form) {
		$where = array(
				"audit_id = " . $audit->id,
				"form_id = " . $form->id
		);
		
		return $this->fetchRow($where);
	}
	
	/**
	 * vraci formular podle id
	 * 
	 * @param int $id id formulare
	 * @return Audit_Model_Row_AuditForm
	 */
	public function getById($id) {
		return $this->find($id)->current();
	}
	
	/**
	 * rozlozi popisek na zavaznost a popisek
	 * vraci stdClass
	 * pokud je rozklad neuspesny, je nastavena zavaznost na hodnotu $defaultW
	 *
	 * @param string $label popisek k rozkladu
	 * @param int $defaultW vychoti hodnota zavaznosti
	 * @return stdClass
	 */
	protected function _explodeWeight($label, $defaultW = 1) {
		$retVal = new stdClass();
	
		// rozlozeni
		list($weight, $pureLabel) = explode(" ", $label, 2);
	
		$weight = trim($weight, "()");
	
		$retVal->question = $pureLabel;
		$retVal->weight = $weight;
	
		// kontrola jeslti je
	
		return $retVal;
	}
}