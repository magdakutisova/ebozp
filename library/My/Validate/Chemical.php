<?php
class My_Validate_Chemical extends Zend_Validate_Abstract{
	
	const TOO_MANY = 'tooMany';
	protected $_messageTemplates = array(
		self::TOO_MANY => 'Buď vyberte chemickou látku ze seznamu, nebo ji vyplňte do textového pole (ne obojí). Pro přidání více látek stiskněte tlačítko "Další chemická látka".',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		if($value['chemical'] != 0 && $value['new_chemical'] != ""){
			$this->_error(self::TOO_MANY);
			return false;
		}
		return true;
	}
 
	
}