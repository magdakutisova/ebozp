<?php
class My_Questionary_Callback_Save extends My_Questionary_Callback_Abstract {

	public function callback($questionary, $row=null, array $params = array()) {
		// nalezeni dat
		$type = $this->getQuestionaryType($row, $params["clientId"]);

		// vyhodnoceni typu vyplneneho dotazniku
		switch ($type->assign_type) {
			case Application_Model_DbTable_QuestAssignments::TYPE_CLIENT:
				$this->_processClient($questionary, $params["clientId"]);
				break;

			case Application_Model_DbTable_QuestAssignments::TYPE_SUBSIDIARY:
				$this->_processSubsidiary($questionary, $params["clientId"], $params["subsidiaryId"]);
				break;
			
			case Application_Model_DbTable_QuestAssignments::TYPE_EMPLOYEE:
				$this->_processEmployee($questionary, $params["clientId"], $params["employeeId"]);
				break;

			case Application_Model_DbTable_QuestAssignments::TYPE_POSITION:
				$this->_processPosition($questionary, $params["clientId"], $params["positionId"]);
				break;

			default:
				# code...
				break;
		}
	}

	/**
	 * zpracuje informace o klientovi
	 * @param $questionary instance dotazniku s daty
	 * @param $clientId idnetifikacni cislo klienta
	 */
	protected function _processClient($questionary, $clientId) {
		// ziskani prvku a iterace nad nimi
		$items = $questionary->getItems();

		// nacteni radku klienta
		$tableClients = new Application_Model_DbTable_Client();
		$client = $tableClients->find($clientId)->current();

		foreach ($items as $item) {
			// kontrola jmena
			$name = $item->getName();

			if (strpos($name, "client-") === 0) {
				// kontrola, zda je nastavena nejaka hodnota
				$value = $item->getValue();
				if (empty($value)) continue;

				// jedna se mozna o podporovany prvek
				list($trash, $partName) = explode("-", $name, 2);

				$client[$partName] = $value;
			} elseif ($name == "subsidiaries") {
				// nacteni seznamu existujicich pobocek
				$subsidiaries = $item->getValue();
				$subIds = array(0);

				foreach ($subsidiaries as $sub) {
					// nacteni nebo vytvoreni pobocky
					if ($sub["id_subsidiary"]) {
						$subIds[] = $sub["id_subsidiary"];
					}
				}

				$tableSubs = new Application_Model_DbTable_Subsidiary();
				$subRows = $tableSubs->find($subIds);
				$subIndex = array();

				// indexace dle id
				foreach ($subRows as $sub) {
					$subIndex[$sub->id_subsidiary] = $sub;
				}

				// zpracovani pobocek
				foreach ($subsidiaries as & $sub) {
					// pokud pobocka neexistuje, vytvori se
					if (!isset($subIndex[$sub["id_subsidiary"]])) {
						$subIndex[$sub["id_subsidiary"]] = $tableSubs->createRow();
					}

					$row = $subIndex[$sub["id_subsidiary"]];
					$row["subsidiary_town"] = $sub["town"];
					$row["subsidiary_street"] = $sub["street"];
					$row["subsidiary_code"] = $sub["code"];
					$row["subsidiary_name"] = $sub["name"];
					$row["client_id"] = $clientId;

					$row->save();
					$sub["id_subsidiary"] = $row["id_subsidiary"];
				}

				$item->setValue($subsidiaries);
			}
		}

		$client->save();
	}

	private function _processSubsidiary($questionary, $clientId, $subsidiaryId) {
		// nacteni dat
		$tableSubsidiaries = new Application_Model_DbTable_Subsidiary();
		$tableClients = new Application_Model_DbTable_Client();

		// nacteni pobocky
		$subsidiary = $tableSubsidiaries->find($subsidiaryId)->current();

		// zpracovani dat
		$employees = null;
		$devices = null;
		$contacts = null;
		$workplaces = null;
		$positions = null;

		$items = $questionary->getItems();

		foreach ($items as $item) {
			// nacteni a vyhodnoceni jmena
			$name = $item->getName();

			// vyhodnoceni, jestli se jedna primo o prvek pobocky nebo o rozsirene informace
			if (strpos($name, "subsidiary-") === 0) {
				// jedna se o prvek pobocky
				list($trash, $name) = explode("-", $name);

				if (isset($subsidiary[$name])) {
					$subsidiary[$name] = $item->getValue();
				}
			} else {
				// jedna se o jiny prvek - vyhodnoti se o ktery
				switch ($name) {
					case 'employees':
						// jedna se o tabulku zamestnancu
						$employees = $item;
						break;

					case "devices":
						// tabulka technickych zarizeni
						$devices = $item;
						break;

					case "contacts":
						// tabulka kontaktnich osob
						$contacts = $item;
						break;

					case "workplaces":
						$workplaces = $item;
						break;

					case "positions":
						$positions = $item;
						break;
				}
			}
		}

		// zapis hodnot do databaze
		$subsidiary->save();

		// zpracovani kontaktnich osob
		if ($contacts) {
			// nacteni kontaktnich osob nalezicich k pobocce
			$tableContacts = new Application_Model_DbTable_ContactPerson();

			// smazani stavajicich kontaktnich osob
			$tableContacts->delete(array("subsidiary_id = ?" => $subsidiaryId));

			// zpracovani vyplnenych dat
			$data = $contacts->getValue();

			foreach ($data as $i => $record) {
				// pokud neni vyplnene jmeno, pak se radek preskakuje
				if (empty($record["name"])) continue;

				// zjisteni id
				$id = $record["id_contact_person"];
				$row = $tableContacts->createRow(array("subsidiary_id" => $subsidiaryId));

				$row->name = $record["name"];
				$row->email = $record["email"];
				$row->phone = $record["phone"];

				// ulozeni radku a zapis do realnych dat
				$row->save();
				$data[$i]["id_contact_person"] = $row->id_contact_person;
			}

			$contacts->setValue($data);
		}

		// zpracovani technickych prostredku
		if ($devices) {
			// vytvoreni tabulky a smazani starych dat
			$tableDevices = new Application_Model_DbTable_TechnicalDevice();
			$tableDevices->delete(array("subsidiary_id = ?" => $subsidiaryId));

			// zpracovani dat
			$data = $devices->getValue();

			foreach ($data as $i => $record) {
				// pokud nejsou vyplnena data, pak se radek preskakuje
				if (empty($record["tech_type"]) && empty($record["tech_sort"])) continue;

				$row = $tableDevices->createRow(array(
					"subsidiary_id" => $subsidiaryId,
					"type" => $record["tech_type"],
					"sort" => $record["tech_sort"]
				));

				$row->save();
				$data[$i]["id_technical_device"] = $row->id_technical_device;
			}

			// ulozeni modifikovanych dat
			$devices->setValue($data);
		}

		// zpracovani dat zamestnancu
		if ($employees) {
			// nacteni a indexace dat
			$tableEmployees = new Application_Model_DbTable_Employee();
			$empRows = $tableEmployees->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));

			$empIndex = array();

			foreach ($empRows as $emp) {
				$empIndex[$emp->id_employee] = $emp;
			}

			// seznam nalezenych id
			$foundIds = array(0);

			$data = $employees->getValue();

			foreach ($data as $i => $record) {
				// pokud data nejsou vyplnena, radek se preskakuje
				if (empty($record["first_name"]) && empty($record["surname"])) continue;

				// vyhodnoceni, zda zaznam uz existuje nebo ne
				$row = null;

				if (isset($empIndex[$record["id_employee"]])) {
					$row = $empIndex[$record["id_employee"]];
				} else {
					$row = $tableEmployees->createRow(array(
						"subsidiary_id" => $subsidiaryId,
						"client_id" => $clientId
						));
				}

				// aktualizace dat
				$row["first_name"] = $record["first_name"];
				$row["surname"] = $record["surname"];

				$row->save();

				// zapis do nalezenych id a do datove tabulky
				$foundIds[] = $row->id_employee;
				$data[$i]["id_employee"] = $row->id_employee;
			}

			// smazani zamestnancu, kteri nebyli nalezeni
			$tableEmployees->delete(array(
				"subsidiary_id = ?" => $subsidiaryId,
				"id_employee not in (?)" => $foundIds));

			$employees->setValue($data);
		}

		// zpracovani pracovist
		if (!is_null($workplaces)) {
			// nacteni a indexace dat
			$tableWorkplaces = new Application_Model_DbTable_Workplace();
			$workRows = $tableWorkplaces->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));

			$workIndex = array();

			foreach ($workRows as $work) {
				$workIndex[$work->id_workplace] = $work;
			}

			$data = $workplaces->getValue();
			$foundIds = array(0);

			foreach ($data as $i => $record) {
				// vyhodnoceni, jestli je pracoviste vyplneno
				$row = null;

				if (isset($workIndex[$record["id_workplace"]])) {
					// radek existuje
					$row = $workIndex[$record["id_workplace"]];
				} else {
					// vytvori se novy radek
					$row = $tableWorkplaces->createRow(array(
						"subsidiary_id" => $subsidiaryId,
						"client_id" => $clientId
					));
				}

				// zapis hodnoty
				$row->name = $record["name"];

				$row->save();
				$foundIds[] = $row->id_workplace;
				$data[$i]["id_workplace"] = $row->id_workplace;
			}

			// smazani hodnot
			$tableWorkplaces->delete(array(
				"subsidiary_id = ?" => $subsidiaryId,
				"id_workplace not in (?)" => $foundIds));

			$workplaces->setValue($data);
		}

		// zpracovani pracovnich pozic
		if (!is_null($positions)) {
			// nacteni a indexace dat
			$tablePositions = new Application_Model_DbTable_Position();
			$rowPos = $tablePositions->fetchAll(array("subsidiary_id = ?" => $subsidiaryId));

			$posIndex = array();

			foreach ($rowPos as $pos) {
				$posIndex[$pos->id_position] = $pos;
			}

			$data = $positions->getValue();
			$foundIds = array(0);

			foreach ($data as $i => $record) {
				// vyhodnoceni, jestli je pracoviste vyplneno
				$row = null;

				if (isset($posIndex[$record["id_position"]])) {
					// radek existuje
					$row = $posIndex[$record["id_position"]];
				} else {
					// vytvori se novy radek
					$row = $tablePositions->createRow(array(
						"subsidiary_id" => $subsidiaryId,
						"client_id" => $clientId
					));
				}

				// zapis hodnoty
				$row->position = $record["position"];

				$row->save();
				$foundIds[] = $row->id_position;
				$data[$i]["id_position"] = $row->id_position;
			}

			// smazani hodnot
			$tablePositions->delete(array(
				"subsidiary_id = ?" => $subsidiaryId,
				"id_position not in (?)" => $foundIds));

			$positions->setValue($data);
		}
	}

	/**
	 * zpracuje dotaznik o zamestnanci a zapise data do databaze
	 */
	protected function _processEmployee($questionary, $clientId, $employeeId) {
		// nacteni dat
		$tableClients = new Application_Model_DbTable_Client();
		$tableEmployees = new Application_Model_DbTable_Employee();

		$employee = $tableEmployees->find($employeeId)->current();

		// pokud zamestanec nebyl nalezen, tak se nic delat nebude
		if (!$employee) return;

		// ziskani itemu a zapis dat
		$items = $questionary->getItems();

		foreach ($items as $item) {
			// kontrola, zda se jedna o podporovanou hodnotu
			$itemName = $item->getName();

			if (strpos($itemName, "employee-") === 0) {
				// rozlozeni dat
				list($trash, $name) = explode("-", $itemName);

				// pokud hodnota existuje, zapise se
				if (isset($employee[$name])) {
					$value = $item->getValue();
					$employee[$name] = empty($value) ? null : $value;
				}
			}
		}

		// ulozeni zamestance
		$employee->save();
	}

	/**
	 * zpracuje pracovni pozici
	 */
	public function _processPosition($questionary, $clientId, $positionId) {
		// nacteni informaci
		$tableClients = new Application_Model_DbTable_Client();
		$tablePositions = new Application_Model_DbTable_Position();

		$client = $tableClients->find($clientId)->current();
		$position = $tablePositions->find($positionId)->current();

		// zpracovani dat dotazniku

		$items = $questionary->getIndex();
		$works = null;
		$employees = null;
		$devices = null;

		foreach ($items as $item) {
			$itemName = $item->getName();

			// vyhodnoceni jmena
			if (strpos($itemName, "position-") === 0) {
				// jedna se o prvek podporovany primo radkem databaze
				list($garbage, $colName) = explode("-", $itemName);

				if (isset($position[$colName])) $position[$colName] = $item->getValue();
			} else {
				// jedna se o jiny prvek
				switch ($itemName) {
				case "employees":
					$employees = $item;
					break;

				case "works":
					$works = $item;
					break;

				case "technical_devices":
					$devices = $item;
					break;
				}
			}
		}

		$position->save();

		// vyhodnoceni a zapis zamestnancu
		if ($employees) {
			// smazani starych dat
			$tableEmpAssocs = new Application_Model_DbTable_PositionHasEmployee();
			$tableEmpAssocs->delete(array("id_position = ?" => $position->id_position));

			// zapis novych dat
			$data = $employees->getValue();
			$employeeIds = array();

			foreach ($data as $item) {
				if ($item["use"]) {
					$employeeIds[] = $item["id_employee"];
				}
			}

			foreach ($employeeIds as $employeeId) {
				$tableEmpAssocs->insert(array(
					"id_employee" => $employeeId,
					"id_position" => $position->id_position));
			}
		}

		// vytvoreni a zapis techickych zarizeni
		if ($devices) {
			// smazani starych dat
			$tableTechAssocs = new Application_Model_DbTable_PositionHasTechnicalDevice();
			$tableTechAssocs->delete(array("id_position = ?" => $position->id_position));

			// zapis novych dat
			$data = $devices->getValue();
			$techIds = array();

			foreach ($data as $item) {
				if ($item["use"]) {
					$techIds[] = $item["id_technical_device"];
				}
			}

			foreach ($techIds as $techId) {
				$tableTechAssocs->insert(array(
					"id_technical_device" => $techId,
					"id_position" => $position->id_position));
			}
		}

		// smazani a znovuzavedeni praovnich cinnosti
		if ($works) {
			// smazani starych asociaci
			$tableWorkAssocs = new Application_Model_DbTable_PositionHasWork();
			$tableWorks = new Application_Model_DbTable_Work();

			$tableWorks->delete(array(
				"id_work in (?)" => new Zend_Db_Expr(sprintf("select id_work from %s where id_position = %s", $tableWorkAssocs->info("name"), $position->id_position))
				));

			// zpracovani dat
			$data = $works->getValue();

			foreach ($data as $item) {
				$name = $item["work_name"];

				$workId = $tableWorks->insert(array("work" => $name));
				$tableWorkAssocs->insert(array("id_position" => $position->id_position, "id_work" => $workId));
			}
		}
	}
}