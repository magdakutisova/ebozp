<?php
class Zend_View_Helper_Filters extends Zend_View_Helper_Abstract{
	
	public function filters($archived){
		if($archived){
			return '<div class="box"><h3>Filtrování</h3>
					<a class="archive-list">Podle názvu</a> | 
					<a class="archive-alphabet">Podle abecedy</a> | 
					<a class="archive-technician">Podle bezpečnostního technika</a> | 
					<a class="archive-coordinator">Podle koordinátora</a> | 
					<a class="archive-town">Podle obce</a> | 
					<a class="archive-district">Podle okresu</a> | 
					<a class="archive-lastOpen">Naposledy otevřené</a>';
		}
		else{
			return '<div class="box"><h3>Filtrování</h3>
					<a class="list">Podle názvu</a> | 
					<a class="alphabet">Podle abecedy</a> | 
					<a class="technician">Podle bezpečnostního technika</a> | 
					<a class="coordinator">Podle koordinátora</a> | 
					<a class="town">Podle obce</a> | 
					<a class="district">Podle okresu</a> | 
					<a class="lastOpen">Naposledy otevřené</a>'
					. '<br/><input type="checkbox" checked="checked" class="active" value="1">Aktivní pobočky</input>
					<br/><input type="checkbox" class="inactive" value="0">Neaktivní pobočky</input></div>
					<div class="current" name="nazev"><div class="type" name="klienti"></div></div>';
		}
	}
}