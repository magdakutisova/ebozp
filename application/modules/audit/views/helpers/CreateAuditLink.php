<?php
class Zend_View_Helper_CreateAuditLink extends Zend_View_Helper_Abstract {
	
	public function createAuditLink($diary, $caption = null) {
		// kontrola caption
		if (is_null($caption)) {
			$caption = $diary->subsidiary_name;
		}
		
		$retVal = "<a href='/audit/audit/create?audit[subsidiary_id]=" . $diary->id_subsidiary . "'>" . $caption . "</a>";
		
		return $retVal;
	}
}