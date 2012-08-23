<?php
class My_Debug{
	
	public static function dump($var){
		Zend_Debug::dump($var);
		die();
	}
	
}