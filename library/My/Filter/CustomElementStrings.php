<?php
class My_Filter_CustomElementStrings implements Zend_Filter_Interface{
	
	public function filter($value){
		if(is_array($value)){
			foreach ($value as $name => &$input){
				if(is_string($input)){
					$value[$name] = trim($input);
					$value[$name] = strip_tags($input);
				}
			}
			
		}
		return $value;
	}
	
}