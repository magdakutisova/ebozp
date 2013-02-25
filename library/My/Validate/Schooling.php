<?php
class My_Validate_Schooling extends Zend_Validate_Abstract{
	
	const INVALID_DATE = 'invalidDate';
	const NO_NAME = 'noName';
	protected $_messageTemplates = array(
			self::INVALID_DATE => 'Zadejte datum ve formátu DD.MM.RRRR',
			self::NO_NAME => 'Zadejte název školení',
			);
	
	public function isValid($value){
		if(($value['note'] != '' || $value['private'] != '') && ($value['schooling'] == '' || $value['schooling'] == 0)){
			$this->_error(self::NO_NAME);
			return false;
		}
		return true;
	}
	
}