<?php
class My_Validate_WorkplaceRisk extends Zend_Validate_Abstract{
	
	const NO_NAME = 'noName';
	protected $_messageTemplates = array(
		self::NO_NAME => 'Vyplňte název rizika.',
	);
	
	/**
	 * @param unknown_type $value
	 */
	public function isValid($value) {
		if($value['note'] != "" && $value['risk'] == ""){
			$this->_error(self::NO_NAME);
			return false;
		}
		return true;
	}
 
	
}