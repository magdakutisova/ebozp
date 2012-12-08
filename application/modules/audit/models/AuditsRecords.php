<?php
class Audit_Model_AuditsRecords extends Zend_Db_Table_Abstract {
	
	const NT = "NT";
	const A = "A";
	const N = "N";
	const C = "C";
	
	const SCORE_NT = -1;
	const SCORE_A = 1;
	const SCORE_N = 3;
	const SCORE_C = 2;
	
	protected $_name = "audit_audits_records";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"audit" => array(
					"columns" => "audit_id",
					"refTableClass" => "Audit_Model_Audits",
					"refColumns" => "id"
			),
			
			"mistake" => array(
					"columns" => "mistake_id",
					"refTableCLass" => "Audit_Model_AuditsRecordsMistakes",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Audit_Model_Row_AuditRecord";
	
	protected $_rosetClass = "Audit_Model_Rowset_AuditsRecords";
	
	/**
	 * vytvori jeden zaznam auditu
	 * 
	 * @param Questionary_Item_Abstract $itemInstance instance prvku dotazniku
	 * @param Audit_Model_Row_Audit $audit audit
	 * @param Audit_Model_Row_AuditRecordGroup $aGroup skupina otazek
	 * @param Questionary_Model_Questionary $questionary radek dotazniku
	 */
	public function createRecord(
			Questionary_Item_Abstract $itemInstance,
			Audit_Model_Row_Audit $audit, 
			Audit_Model_Row_AuditRecordGroup $aGroup,
			Questionary_Model_Questionary $questionary = null) {
		
		// nacteni dotazniku, pokud je potreba
		if (is_null($questionary)) {
			// nacteni dotazniku
			$questionary = $audit->findParentRow("Questionary_Model_Questionaries");
		}
		
		// nacteni definice prvku
		$tableItems = new Questionary_Model_QuestionariesItems();
		
		$where = array(
				"questionary_id = " . $questionary->id,
				"name like " . $tableItems->getAdapter()->quote($itemInstance->getName())
		);
		
		$itemRow = $tableItems->fetchRow($where);
		
		if (!$itemRow) {
			// radke nebyl nalezen, vyhodi se chyba
			throw Zend_Exception("Questionary item named '" . $itemInstance->getName() . " has not been found");
		}
		
		// vyhodnoceni skore
		$score = null;
		
		switch ($itemInstance->getValue()) {	
			case self::N:
				$score = self::SCORE_N;
				break;
				
			case self::A:
				$score = self;
				break;
				
			case self::C:
				$score = self::SCORE_C;
				break;
				
			default:
				$score = self::SCORE_NT;
		}
		
		// nstaveni hodnot a ulozeni
		$retVal = $this->createRow(array(
				"audit_id" => $audit->id,
				"group_id" => $aGroup->id,
				"questionary_item_id" => $itemRow->id,
				"question" => $itemRow->label,
				"score" => $score
		));
		
		$retVal->save();
		
		return $retVal;
	}
	
	/**
	 * vytvori nove zaznamy z auditu a stare smaze
	 * 
	 * @param Audit_Model_Row_Audit $audit audit
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function createRecords(Audit_Model_Row_Audit $audit) {
		// smazani starych dat
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableGroups = new Audit_Model_AuditsRecordsGroups();
		
		$where = "audit_id = " . $audit->id;
		
		$tableMistakes->delete($where . " and record_id is not null");
		$this->delete($where);
		$tableGroups->delete($where);
		
		// nacteni formulare a vyplnenych dat
		$tableFilleds = new Questionary_Model_Filleds();
		$filled = $tableFilleds->getById($audit->form_filled_id);
		
		$questionary = $filled->toClass();
		
		// nacteni radku prvku dotazniku a indexace dle jmena
		$itemIndex = array();
		$itemList = $filled->findParentRow("Questionary_Model_Questionaries", "questionary")
				->findDependentRowset("Questionary_Model_QuestionariesItems", "questionary");
		
		foreach ($itemList as $item) {
			$itemIndex[$item->name] = $item;
		}
		
		$tableItems = new Questionary_Model_QuestionariesItems();
		
		// vygenerovani seznamu skupin
		$groups = $questionary->getItems();
		$records = array();
		$mistakes = array();
		$adapter = $this->getAdapter();
		
		// zamnknuti tabulky zaznamu a zahajeni transakce
		$adapter->query("lock tables " . $this->_name . " write");
		$adapter->beginTransaction();
		
		try {
			// nacteni id dalsiho zaznamu a zalohovani prvniho
			$recordId = $adapter->query("select Auto_increment from information_schema.tables where table_name='" . $this->_name . "' and table_schema = DATABASE()")->fetchColumn();
			$statId = $recordId;
			
			foreach ($groups as $group) {
				// zaneseni skupiny
				$groupRow = $tableGroups->createGroup($group->getLabel(), $audit);
				
				// zapis prvku
				$groupItems = $group->getItems();
				
				for ($i = 1; $i < count($groupItems); $i += 3) {
					// vyhodnoceni skore
					$score = null;
					$item = $groupItems[$i];
					$note = $groupItems[$i+2];
					
					$mistake = false;
					
					switch ($item->getValue()) {
						case self::A:
							$score = self::SCORE_A;
							break;
							
						case self::N:
							$score = self::SCORE_N;
							$mistake = true;
							break;
							
						default:
							$socre = self::SCORE_NT;
					}
					
					$explodedLabel = $this->_explodeWeight($item->getLabel());
					
					// nalezeni porznamky
					$oneRecord = array(
							$audit->id,
							$groupRow->id,
							$itemIndex[$item->getName()]->id,
							$adapter->quote($explodedLabel->label),
							$adapter->quote($note->getValue()),
							$adapter->quote($score),
							$adapter->quote($explodedLabel->weight)
					);
					
					$records[] = "(" . implode(",", $oneRecord) . ")";
					
					// vyhodnoceni neshody
					if ($mistake) {
						// nacteni itemu
						$mistGroup = $groupItems[$i + 1];
						$mistakeItems = $mistGroup->getItems();
						
						list($day, $motnh, $year) = explode(". ", $mistakeItems[6]->getValue());
						$removedAt = $year . "-" . $motnh . "-" . $day;
						echo ($audit->done_at);
						// sestaveni dat pro zapis neshody
						$item = array(
								$audit->id,											// id auditu
								$audit->client_id,									// id klienta
								$audit->subsidiary_id,								// id pobocky
								$recordId,											// id zaznamu
								$itemIndex[$item->getName()]->id,					// id polozky v dotazniku
								$adapter->quote($explodedLabel->weight),			// zavaznost
								$adapter->quote($explodedLabel->label),				// otazka
								$adapter->quote($mistakeItems[0]->getValue()),		// kategorie
								$adapter->quote($mistakeItems[1]->getValue()),		// podkategorie
								$adapter->quote($mistakeItems[2]->getValue()),		// upresneni
								$adapter->quote($mistakeItems[3]->getValue()),		// neshoda
								$adapter->quote($mistakeItems[4]->getValue()),		// navrh reseni
								$adapter->quote($mistakeItems[5]->getValue()),		// komentar
								$adapter->quote($audit->done_at),					// datum zjisteni neshody
								$adapter->quote($removedAt),						// bude odstaneno
								$adapter->quote($mistakeItems[7]->getValue())		// zodpovedna osoba
						);
						
						// sestaveni dat
						$mistakes[] = "(" . implode(",", $item) . ")";
					}
					
					// pricteni id zaznamu
					$recordId++;
				}
			}
			
			// priprava SQL pro zapis zaznamu
			$recordName = $this->_name;
			$sqlBase = "insert into `$recordName` (audit_id, group_id, questionary_item_id, question, note, score, weight) values ";
			$chunks = array_chunk($records, 100);
			
			// zapis dat zaznamu
			foreach ($chunks as $chunk) {
				$sql = $sqlBase . implode(",", $chunk);
				$adapter->query($sql);
			}
			
			// priprava SQL pro zapis neshod
			$mistakeName = $tableMistakes->info("name");
			
			$sqlBase = "insert into $mistakeName (audit_id, client_id, subsidiary_id, record_id, questionary_item_id, weight, question, category, subcategory, concretisation, mistake, suggestion, comment, notified_at, will_be_removed_at, responsibile_name) values ";
			$chunks = array_chunk($mistakes, 100);
			
			// zapis neshod do databaze
			foreach ($chunks as $chunk) {
				$sql = $sqlBase . implode(",", $chunk);
				$adapter->query($sql);
			}
			
			// nastaveni id neshody do zaznamu
			$sql = "update `$this->_name`, `$mistakeName` set $recordName.mistake_id = $mistakeName.id where $recordName.id = $mistakeName.record_id and $recordName.id beween $statId and $recordId";
			$adapter->query($sql);
			
			$adapter->commit();
		} catch (Exception $e) {
			// vraceni zmen zpusobenych transakce
			$adapter->rollBack();
		}
		die();
		// odemceni dat
		$adapter->query("unlock tables");
		
		// nacteni vlozenych dat
		return $audit->getRecords();
	}
	
	/**
	 * vraci seznam zazanmu dle auditu razenych dle skupiny
	 * @param Audit_Model_Row_Audit $audit
	 * @return Audit_Model_Rowset_AuditsRecords
	 */
	public function getByAudit(Audit_Model_Row_Audit $audit) {
		$where = array(
				"audit_id = " . $audit->id
		);
		
		return $this->fetchAll($audit, "group_id");
	}
	
	/**
	 * varci radek zaznamu dle id
	 * 
	 * @param int $id id zaznamu
	 * @return Audit_Model_Row_AuditRecord
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
		
		$retVal->label = $pureLabel;
		$retVal->weight = $weight;
		
		// kontrola jeslti je
		
		return $retVal;
	}
}