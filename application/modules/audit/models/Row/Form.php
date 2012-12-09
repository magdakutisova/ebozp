<?php
class Audit_Model_Row_Form extends Zend_Db_Table_Row_Abstract {
	
	/**
	 * vraci rodicovsky dotaznik
	 * 
	 * @return Questionary_Model_Row_Questionary
	 */
	public function getQuestionary() {
		return $this->findParentRow("Questionary_Model_Questionaries", "questionary");
	}
	
	/**
	 * zapise obsah formulare do systemu
	 * 
	 * @param Questionary_Questionary $questionary dotaznik
	 * @return Audit_Model_Row_Form
	 */
	public function writeQuestionary(Questionary_Questionary $questionary) {
		// vyprazdneni dat
		$tableCategories = new Audit_Model_FormsCategories();
		$tableQuestions = new Audit_Model_FormsCategoriesQuestions();
		$tableQuestionaryQuestions = new Questionary_Model_QuestionariesItems();
		
		$tableCategories->delete("questionary_id = " . $this->questionary_id);
		
		// zapis kategorii
		$categoryItems = $questionary->getItems();
		$categories = array();
		
		foreach ($categoryItems as $item) {
			$categories[] = $tableCategories->createCategory($item->getLabel(), $this);
		}
		
		// prochazeni kategorii a zapis prvku
		reset($categories);
		$adapter = $tableCategories->getAdapter();
		$qList = array();
		
		foreach ($categoryItems as $item) {
			// nacteni radku kategorie
			$category = current($categories);
			
			// ziskani seznamu jmen
			$names = array("");
			$questions = $item->getItems();
			$maxI = count($questions);
			
			for($i = 1; $i < $maxI; $i += 2) {
				$question = $questions[$i];
				$names[] = $question->getName();
			}
			
			// nacteni dat
			$questionRowset = $tableQuestionaryQuestions->fetchAll(array(
					$adapter->quoteInto("`name` in (?)", $names),
					"questionary_id = " . $this->questionary_id
			));
			
			// indexace dat
			$questionIndex = array();
			
			foreach ($questionRowset as $row) {
				$questionIndex[$row->name] = $row;
			}
			
			// nacteni a zapis dat
			for($i = 1; $i < $maxI; $i += 2) {
				$question = $questions[$i];
				$mistakeItems = $questions[$i + 1]->getItems();
				
				$qInfo = $this->_explodeWeight($question->getLabel());
				
				// zapis hodnot
				$tmpArray = array();
				
				$tmpArray[] = $category->id;
				$tmpArray[] = $questionIndex[$question->getName()]->id;
				$tmpArray[] = $adapter->quote($qInfo->weight);
				$tmpArray[] = $adapter->quote($qInfo->label);
				$tmpArray[] = $adapter->quote($mistakeItems[0]->getValue());
				$tmpArray[] = $adapter->quote($mistakeItems[1]->getValue());
				$tmpArray[] = $adapter->quote($mistakeItems[2]->getValue());
				$tmpArray[] = $adapter->quote($mistakeItems[3]->getValue());
				$tmpArray[] = $adapter->quote($mistakeItems[4]->getValue());
				
				$qList[] = "(" . implode(",", $tmpArray) . ")";
			}
			
			// posun na dalsi radek kategorie
			next($categories);
		}
		
		// priprava SQL
		$sqlBase = "insert into `" . $tableQuestions->info("name") . "` (group_id, questionary_item_id, weight, question, category, subcategory, concretisation, mistake, suggestion) values ";
		
		// rozdeleni pole a zapis do databaze
		$chunks = array_chunk($qList, 100);
		
		foreach ($chunks as $chunk) {
			$sql = $sqlBase . implode(",", $chunk);
			$adapter->query($sql);
		}
		
		return $this;
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