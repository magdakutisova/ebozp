<?php
class My_Form_Validator_PersonEmail extends Zend_Validate_Abstract{
	
	const INVALID_EMAIL = 'invalid_email';
	
	protected $_messageTemplates = array(
			self::INVALID_EMAIL => 'Zadejte platnou emailovou adresu.',
			);
	
	public function isValid($value){
		if($value['email'] != ''){
			$validator = new Zend_Validate_EmailAddress();
			if(!$validator->isValid($value['email'])){
				$this->_error(self::INVALID_EMAIL);
				return false;
			}
		}
		return true;
	}
	
}