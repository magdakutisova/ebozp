<?php
class Audit_Model_AuditsForms extends Zend_Db_Table_Abstract {
	
	protected $_name = "audit_audits_forms";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_referenceMap = array(
			"form" => array(
					"columns" => "form_id",
					"refTableClass" => "Audit_Model_Forms",
					"refColumns" => "questionary_id"
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
	 * @return Audit_Model_Row_AuditForm
	 */
	public function createForm(Audit_Model_Row_Audit $audit, Audit_Model_Row_Form $form) {
		$retVal = $this->createRow(array(
				"form_id" => $form->questionary_id,
				"audit_id" => $audit->id,
				"name" => $form->name
		));
		
		$retVal->save();
		
		// prekopirovani obsahu formulare na 
		$questionary = $form->getQuestionary()->toClass();
		
		$groupList = $questionary->getItems();
		
		// zapis skupin a dalsich dat
		$tableGroups = new Audit_Model_AuditsRecordsGroups();
		$tableRecords = new Audit_Model_AuditsRecords();
		$tableMistakes = new Audit_Model_AuditsRecordsMistakes();
		$tableItems = new Questionary_Model_QuestionariesItems();
		$tableAuditsMistakes = new Audit_Model_AuditsMistakes();
		
		$nameGroups = $tableGroups->info("name");
		$nameRecords = $tableRecords->info("name");
		$nameMistakes = $tableMistakes->info("name");
		$nameItems = $tableItems->info("name");
		$nameAuditMistakes = $tableAuditsMistakes->info("name");
		
		// zacatek transakce a zjisteni id
		$adapter = $this->getAdapter();
		
		$adapter->beginTransaction();
		$adapter->query("set foreign_key_checks = 0;");
		$adapter->query("lock tables `$nameGroups` write, `$nameMistakes` write, `$nameRecords` write, `$nameItems` write, `$nameAuditMistakes` write");
		
		try {
			// nacteni poslednich id zaznamu
			$recordId = $adapter->query("select Auto_increment from information_schema.tables where table_name='$nameRecords' and table_schema = DATABASE()")->fetchColumn();
			$groupId = $adapter->query("select Auto_increment from information_schema.tables where table_name='$nameGroups' and table_schema = DATABASE()")->fetchColumn();
			$mistakeId = $adapter->query("select Auto_increment from information_schema.tables where table_name='$nameMistakes' and table_schema = DATABASE()")->fetchColumn();
			
			// nacteni jmen a id prvku formulare a indexace
			$sql = "select id, name from " . $tableItems->info("name") . " where questionary_id = " . $retVal->form_id;
			$items = $adapter->query($sql);
			$itemIndex = array();
			
			while ($item = $items->fetch()) {
				$itemIndex[$item["name"]] = $item["id"];
			}
			
			// priprava dat
			$mistakes = array();
			$groups = array();
			$records = array();
			$assocs = array();
			
			$null = new Zend_Db_Expr("NULL");
			
			foreach ($groupList as $group) {
				// zapis skupiny
				$groups[] = "(" . $adapter->quote($group->getLabel()) . ", $retVal->id)";
				
				// zapis prvku
				$items = $group->getItems();
				$maxI = count($items);
				
				for ($i = 1; $i < $maxI; $i += 2) {
					// zjisteni dat o otazce
					$qInfo = $this->_explodeWeight($items[$i]->getLabel());
					$item = $items[$i];
					
					// vygenerovani zaznamu
					$record = array(
							$recordId,
							$audit->id,
							$retVal->id,
							$groupId,
							$mistakeId,
							$adapter->quote($itemIndex[$item->getName()]),
							$adapter->quote($qInfo->question),
							Audit_Model_AuditsRecords::SCORE_NT,
							$adapter->quote($qInfo->weight)
					);
					
					$records[] = "(" . implode(",", $record) . ")";
					
					$mistakeItems = $items[$i + 1]->getItems();
					
					// vygenerovani neshody
					$mistake = array(
							$mistakeId,													// id zaznamu
							$audit->id,													// id auditu
							$audit->client_id,											// id klienta
							$audit->subsidiary_id,										// id pobocky
							$recordId,													// od zaznamu auditu
							$adapter->quote($itemIndex[$item->getName()]),				// id itemu dotazniku
							$adapter->quote($qInfo->weight),							// zavaznost
							$adapter->quote($qInfo->question),							// otazka
							$adapter->quote($mistakeItems[0]->getValue()),				//kategorie
							$adapter->quote($mistakeItems[1]->getValue()),				//podkategorie
							$adapter->quote($mistakeItems[2]->getValue()),				//specifikace
							$adapter->quote($mistakeItems[3]->getValue()),				//neshoda
							$adapter->quote($mistakeItems[4]->getValue()),				//navrh
							"''",														// komentar
							"CURRENT_DATE",												// bude odstraneno
							"''"														// zodpovedna osoba
					);
					
					$mistakes[] = "(" . implode(",", $mistake) . ")";
					
					// zapis asociace
					$assocs[] = "($audit->id, $mistakeId, $recordId)";
					
					$recordId++;
					$mistakeId++;
				}
				
				$groupId++;
			}
			
			// zpis skupin
			if ($groups) {
				$sql = "insert into `$nameGroups` (name, audit_form_id) values " . implode(",", $groups);
				$adapter->query($sql);
			}
			
			// zapis zaznamu
			if ($records) {
				$sqlBase = "insert into $nameRecords (id, audit_id, audit_form_id, group_id, mistake_id, questionary_item_id, question, score, weight) values ";
				
				$chunks = array_chunk($records, 1000);
				
				foreach ($chunks as $chunk) {
					$sql = $sqlBase . implode(",", $chunk);
					$adapter->query($sql);
				}
			}
			
			// zapis neshod
			if ($mistakes) {
				$sqlBase = "insert into `$nameMistakes` (id, audit_id,client_id,subsidiary_id, record_id, questionary_item_id,weight, question,category,subcategory,concretisation,mistake, suggestion, comment, will_be_removed_at, responsibile_name) values ";
				
				$chunks = array_chunk($mistakes, 1000);
				
				foreach ($chunks as $chunk) {
					$sql = $sqlBase . implode(",", $chunk);
					$adapter->query($sql);
				}
			}
			
			// zapis asociace
			if ($assocs) {
				$sql = "insert into `$nameAuditMistakes` (audit_id, mistake_id, record_id) values " . implode(",", $assocs);
				$adapter->query($sql);
			}
			
			$adapter->commit();
		} catch (Zend_Exception $e) {
			$adapter->rollBack();
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
				"form_id = " . $form->questionary_id
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