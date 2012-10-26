<?php
class My_Validate_Position extends Zend_Validate_Abstract{
	
	const NO_NAME = 'noName';
	protected $_messageTemplates = array(
		self::NO_NAME => 'Vyplňte název pracovní pozice.',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		Zend_Debug::dump($value);
		if(!preg_match("/d+/", $value['position'])){
			$this->_error(self::NO_NAME);
			return false;
		}
		return true;
	}
 
	
}