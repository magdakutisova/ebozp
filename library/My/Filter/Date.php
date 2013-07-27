<?php
class My_Filter_Date implements Zend_Filter_Interface {
	
	const PATTERN_CZ = "/\s*(\d{1,2}\.\ ?){2}(\d{2}|\d{4})\s*/";
	
	const PATTERN_SQL = "/\d{4}(-\d{2})/";
	
	public function filter($value) {
		// pokud neni hodnta zadana, nic se nefiltruje
		if (is_null($value)) return $value;
		
		// kontrola, jestli je hodnta v SQL formatu
		if (preg_match(self::PATTERN_SQL, $value)) return $value;
		
		// kontrola ceskeho formatu
		if (preg_match(self::PATTERN_CZ, $value)) {
			/*
			 * datum je v ceskem formatu
			 */
			// rozlozeni na sekce
			list($day, $month, $year) = explode(".", $value);
			
			// uprava do standardniho tvaru
			$day = $this->_normaliseDayMonth($day);
			$month = $this->_normaliseDayMonth($month);
			$year = $this->_normaliseYear($year);
			
			// sestaveni SQL data
			return "$year-$month-$day";
		}
		
		throw new Zend_Filter_Exception("Invalid date format");
	}
	
	public static function revert($value) {
		// rozlozeni SQL formatu
		list($year, $month, $day) = explode("-", $value);
		
		return "$day. $month. $year";
	}
	
	private function _normaliseDayMonth($str) {
		$str = trim($str);
		
		if (strlen($str) == 1)
			$str = "0" . $str;
		
		return $str;
	}
	
	private function _normaliseYear($str) {
		$str = trim($str);
		
		if (strlen($str) == 2) {
			// vyhodnoceni velikosti
			if (intval($str) > 80) {
				// patrne se tyka devadesatych let
				$str = "19" . $str;
			} else {
				// patrne se tyka po roku 2000
				$str = "20" . $str;
			}
		}
		
		return $str;
	}
}