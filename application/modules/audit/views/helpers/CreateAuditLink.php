<?php
class Zend_View_Helper_CreateAuditLink extends Zend_View_Helper_Abstract {
	
	public function createAuditLink($diary, $caption = null) {
		// kontrola caption
		if (is_null($caption)) {
			$caption = $diary->subsidiary_name;
		}
		
		$link = $this->view->url(array(
				"clientId" => $diary->client_id,
				"subsidiaryId" => $diary->id_subsidiary
		), "audit-create");
		
		$retVal = "<a href='" . $link . "'>" . $caption . "</a>";
		
		return $retVal;
	}
}