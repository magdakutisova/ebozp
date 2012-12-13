<?php
class Audit_Model_Rowset_AuditsResponsibiles extends Zend_Db_Table_Rowset_Abstract {
	
	const ITEM_SEPARATOR = ", ";
	
	public function __toString() {
		
		$names = array();
		
		foreach ($this as $item) {
			$names[] = $item->name;
		}
		
		return implode(self::ITEM_SEPARATOR, $names);
	}
}