<?php
class Zend_View_Helper_Category extends Zend_View_Helper_Abstract {
	
	public function category(Audit_Model_Row_Category $category, $text = null) {
		// vyhodnoceni textu
		if (is_null($text)) $text = $category->name;
		
		$retVal = "<a href='/audit/category/get?category[id]=" . $category->id . "'>"; 
		$retVal .= $text;
		$retVal .= "</a>";
		
		return $retVal;
	}
}