<?php
class My_Responsibility{
	
	const NOTHING = 0;
	const ALCOHOL = 1;
	const INJURIES = 2;
	const ELECTRICITY = 3;
	const LIFTING = 4;
	const PRESS = 5;
	const GAS = 6;
	
	private static $responsibilities = array(
			self::NOTHING => '-----',
			self::ALCOHOL => 'Zaměstnanec provádějící kontrolu na alkohol',
			self::INJURIES => 'Zaměstnanec řešící pracovní úrazy',
			self::ELECTRICITY => 'Zaměstnanec odpovědný za VTZ - elektrická',
			self::LIFTING => 'Zaměstnanec odpovědný za VTZ - zdvíhací',
			self::PRESS => 'Zaměstnanec odpovědný za VTZ - tlaková',
			self::GAS => 'Zaměstnanec odpovědný za VTZ - plynová',
			);
	
	public static function getResponsibilities(){
		return self::$responsibilities;
	}
	
	public static function getResponsibilityName($responsibility){
		if(isset(self::$responsibilities[$responsibility])){
			return self::$responsibilities[$responsibility];
		}
	}
	
}