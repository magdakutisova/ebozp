<?php
class My_Validate_Employee extends Zend_Validate_Abstract{
	
	const INCOMPLETE_NAME = 'incompleteName';
	protected $_messageTemplates = array(
			self::INCOMPLETE_NAME => 'U zaměstnance vyplňte minimálně jméno a příjmení.',
			);
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if(($value['first_name'] != "" && $value['surname'] == "") || ($value['surname'] != "" && $value['first_name'] == "")){
			
		}
	}

	
}