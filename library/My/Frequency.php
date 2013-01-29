<?php
class My_Frequency{
	
	private static $frequencies = array(
			0 => '------',
			1 => 'Celou pracovní směnu',
			2 => 'Více než polovinu pracovní směny (4 hodiny)',
			3 => 'Méně než polovinu pracovní směny (alespoň 0,5 hodiny denně)',
			4 => '1x týdně',
			5 => '1x měsíčně',
			6 => 'Jiná',			
			);
	
	public static function getFrequencies(){
		return self::$frequencies;
	}
	
}