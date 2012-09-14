<?php
class My_Validate_WorkplaceFactor extends Zend_Validate_Abstract{
	
	const NO_NAME = 'noName';
	protected $_messageTemplates = array(
		self::NO_NAME => 'Vyplňte název FPP.',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		if($value['applies'] == "1" && $value['factor'] == ""){
			$this->_error(self::NO_NAME);
			return false;
		}
		return true;
	}
 
	
}