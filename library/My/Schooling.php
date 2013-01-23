<?php
class My_Schooling{
	
	private static $schoolings = array(
			0 => '-----',
			1 => 'Požární ochrana',
			2 => 'Bezpečnost práce',
			3 => 'Řidiči referentských vozidel',
			4 => 'Práce ve výškách',
			5 => 'Neelektrikáři (vyhl. č. 50/1978 Sb., §3, §4)',
			6 => 'Elektrikáři §5 - 10',
			7 => 'Dřevoobrábění',
			8 => 'Obsluhy tlakových nádob',
			9 => 'Svářeči',
			10 => 'Vazači břemen',
			11 => 'Obsluhy zdvihacích zařízení',
			12 => 'Jeřábníci',
			13 => 'Lešenáři',
			14 => 'Řidiči z povolání',
			15 => 'Řidiči motorových vozíků',
			16 => 'Obsluha kovových nádob na plyny - mimo svářeče',
			17 => 'Obsluha motorových řetězových pil',
			18 => 'Obsluha křovinořezů',
			19 => 'Obsluha plynových zařízení do 50 kW',
			20 => 'Obsluha stavebních a zemních strojů',
			21 => 'Pracovníci pracují s chemickými látkami',
			22 => 'Topiči nízkotlakých kotelen nad 50 kW',
			23 => 'Topiči parních a horkovodních kotlů',
			);
	
	public static function getSchoolings(){
		return self::$schoolings;
	}
	
}