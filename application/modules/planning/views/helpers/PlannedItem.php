<?php

class Planning_View_Helper_PlannedItem extends Zend_View_Helper_Abstract {
	
	public function createButton($date, $userId) {
		return sprintf("<button name='add-item' g7:date='%s' g7:user='%s'>Nový ukol</a>", $date, $userId);
	}

	/**
	 * vygeneruje prazdny radek
	 */
	public function emptyRow($date, $users) {
		$retVal = "<tr class='calendar-empty-row'><td>" . $date . "</td>";

		foreach ($users as $user) {
			$retVal .= "<td>" . $this->createButton($date, $user->user_id) . "</td>";
		}

		return $retVal;
	}

	/**
	 * vypise seznam polozek
	 * @return string
	 */
	public function items($items, $date, $userId) {
		$retVal = "<ol>";

		foreach ($items as $item) {
			$retVal .= $this->plannedItem($item);
		}

		$retVal .= "</ol>";

		// pripojeni tlacitka pro pridani noveho ukolo
		$retVal .= $this->createButton($date, $userId);

		return $retVal;
	}

	public function plannedItem($item = null) {
		if (is_null($item)) {
			return $this;
		}

		// url pro detaul prvku
		$url = "/planning/task/get?itemId=" . $item->id;

		// vygenerovani radku
		list($date, $time) = explode(" ", $item->planned_on);
		$retVal = sprintf("<li>%s - <a href='%s' g7:itemId='%s' g7:type='planning-item'>%s</a><br />%s - %s</li>", $time, $url, $item->id, $item->name, $item->company_name, $item->subsidiary_town);

		return $retVal;
	}

	/**
	 * vraci jmeno typu ukolu
	 */
	public function type($type) {
		switch ($type) {
		case Planning_Model_Items::TASK_AUDIT:
			return "Audit";
			break;

		case Planning_Model_Items::TASK_WATCH:
			return "Dohlídka";
			break;

		case Planning_Model_Items::TASK_CHECK:
			return "Prověrka";
			break;
		
		default:
			"Neznámý";
		}
	}
}