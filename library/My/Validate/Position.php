<?php
class My_Validate_Position extends Zend_Validate_Abstract{
	
	const TOO_MANY = 'tooMany';
	protected $_messageTemplates = array(
		self::TOO_MANY => 'Buď vyberte pracovní pozici ze seznamu, nebo ji vyplňte do textového pole (ne obojí). Pro přidání více pozic stiskněte tlačítko "Další pracovní pozice".',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		if($value['position'] != 0 && $value['new_position'] != ""){
			$this->_error(self::TOO_MANY);
			return false;
		}
		return true;
	}
 
	
}