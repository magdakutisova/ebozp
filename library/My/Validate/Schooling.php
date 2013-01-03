<?php
class My_Validate_Schooling extends Zend_Validate_Abstract{
	
	const INVALID_DATE = 'invalidDate';
	protected $_messageTemplates = array(
			self::INVALID_DATE => 'Zadejte datum ve formátu DD.MM.RRRR',
			);
	
	public function isValid($value){
		$dateValidator = new Zend_Validate_Date(array('format' => 'dd.MM.yyyy'));
		if(!$dateValidator->isValid($value['last_execution'])){
			$this->_error(self::INVALID_DATE);
			return false;
		}
		return true;
	}
	
}