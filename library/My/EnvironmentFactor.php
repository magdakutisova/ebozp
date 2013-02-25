<?php
class My_EnvironmentFactor{
	
	const NONE = 0;
	const DUST = 1;
	const CHEMICALS = 2;
	const NOISE = 3;
	const VIBRATIONS = 4;
	const RADIATION_ELECTROMAGNETIC = 5;
	const PHYSICAL_STRAIN = 6;
	const WORKING_POSITION = 7;
	const HEAT_STRAIN = 8;
	const COLD_STRAIN = 9;
	const MENTAL_STRAIN = 10;
	const EYESIGHT_STRAIN = 11;
	const BIOLOGICAL = 12;
	const HIGH_AIR_PRESSURE = 13;
	
	private static $environmentFactors = array(
			self::NONE => '------',
			self::DUST => 'Prach',
			self::CHEMICALS => 'Chemické látky',
			self::NOISE => 'Hluk',
			self::VIBRATIONS => 'Vibrace',
			self::RADIATION_ELECTROMAGNETIC => 'Neionizující záření a elektromagnetická pole',
			self::PHYSICAL_STRAIN => 'Fyzická zátěž',
			self::WORKING_POSITION => 'Pracovní poloha',
			self::HEAT_STRAIN => 'Zátěž teplem',
			self::COLD_STRAIN => 'Zátěž chladem',
			self::MENTAL_STRAIN => 'Psychická zátěž',
			self::EYESIGHT_STRAIN => 'Zraková zátěž',
			self::BIOLOGICAL => 'Práce s biologickými činiteli',
			self::HIGH_AIR_PRESSURE => 'Práce ve zvýšeném tlaku vzduchu',
			);
	
	private static $categories = array(
			'1' => '1',
			'2' => '2',
			'2R' => '2R',
			'3' => '3',
			'4' => '4',
			);
	
	public static function getEnvironmentFactors(){
		return self::$environmentFactors;
	}
	
	public static function getCategories(){
		return self::$categories;
	}
	
	public static function getEnvironmentFactorName($environmentFactor){
		if(isset(self::$environmentFactors[$environmentFactor])){
			return self::$environmentFactors[$environmentFactor];
		}
	}
	
}