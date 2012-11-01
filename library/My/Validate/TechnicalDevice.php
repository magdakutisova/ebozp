<?php
class My_Validate_TechnicalDevice extends Zend_Validate_Abstract{
	
	const TOO_MANY = 'tooMany';
	protected $_messageTemplates = array(
		self::TOO_MANY => 'Buď vyberte technický prostředek ze seznamů, nebo ho vyplňte do textových polí (ne obojí). Pro přidání dalšího technického prostředku stiskněte tlačítko "Další technický prostředek".',
	);
	
	public function isValid($value){
		if(($value['sort'] != 0 || $value['type']) && ($value['new_sort'] != "" || $value['new_type'] != "")){
			$this->_error(self::TOO_MANY);
			return false;
		}
		return true;
	}
	
}