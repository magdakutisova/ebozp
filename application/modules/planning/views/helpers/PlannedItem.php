<?php

class Planning_View_Helper_PlannedItem extends Zend_View_Helper_Abstract {
	
	/**
	 * vypise seznam polozek
	 * @return string
	 */
	public function items($items) {
		$retVal = "<ol>";

		foreach ($items as $item) {
			$retVal .= $this->plannedItem($item);
		}

		$retVal .= "</ol>";

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
		$retVal = sprintf("<li>%s - <a href='%s' g7:itemId='%s' g7:type='planning-item'>%s</a>", $time, $url, $item->id, $item->name);

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