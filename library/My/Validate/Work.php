<?php
class My_Validate_Work extends Zend_Validate_Abstract{
	
	const TOO_MANY = 'tooMany';
	protected $_messageTemplates = array(
		self::TOO_MANY => 'Buď vyberte pracovní činnost ze seznamu, nebo ji vyplňte do textového pole (ne obojí). Pro přidání více činností stiskněte tlačítko "Další pracovní činnost".',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		if($value['work'] != 0 && $value['new_work'] != ""){
			$this->_error(self::TOO_MANY);
			return false;
		}
		return true;
	}
 
	
}