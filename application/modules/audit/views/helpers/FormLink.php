<?php
class Zend_View_Helper_FormLink extends Zend_View_Helper_Abstract {
	
	public function formLink(Audit_Model_Row_Form $form, $caption = null, $link = null) {
		// nastaveni defaultnich hodnot
		if (is_null($caption)) $caption = $form->name;
		
		if (is_null($link)) $link = "/audit/form/get";
		
		// sestaveni adresy
		$link .= "?form[id]=" . $form->id;
		
		// sestaveni retval
		$retVal = "<a href='$link'>$caption</a>";
		
		return $retVal;
	}
}