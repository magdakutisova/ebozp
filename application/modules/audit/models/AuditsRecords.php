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
		$this->delete("audit_id = " . $audit->id);
		
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
		$tableGroups = new Audit_Model_AuditsRecordsGroups();
		
		// vygenerovani seznamu skupin
		$groups = $questionary->getItems();
		$records = array();
		$adapter = $this->getAdapter();
		
		foreach ($groups as $group) {
			// zaneseni skupiny
			$groupRow = $tableGroups->createGroup($group->getLabel(), $audit);
			
			// zapis prvku
			$groupItems = $group->getItems();
			
			for ($i = 1; $i < count($groupItems); $i += 2) {
				// vyhodnoceni skore
				$score = null;
				$item = $groupItems[$i];
				$note = $groupItems[$i+1];
				
				switch ($item->getValue()) {
					case self::A:
						$score = self::SCORE_A;
						break;
						
					case self::C:
						$score = self::SCORE_C;
						break;
						
					case self::N:
						$score = self::SCORE_N;
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
			}
		}
		
		// priprava SQL
		$sql = "insert into `" . $this->_name . "` (audit_id, group_id, questionary_item_id, question, note, score, weight) values ";
		$sql .= implode(",", $records);
		
		// odeslani dat
		$adapter->query($sql);
		
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