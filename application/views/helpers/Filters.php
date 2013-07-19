<?php
class Zend_View_Helper_Filters extends Zend_View_Helper_Abstract{
	
	public function filters(){
		return '<div class="box"><h3>Filtrování</h3><a class="list" action="'
		. $this->view->url(array('mode' => 'nazev'), 'clientFilter')
		. '">Podle názvu</a> | <a class="alphabet" action="'
		. $this->view->url(array('mode' => 'abeceda'), 'clientFilter')
		. '">Podle abecedy</a> | <a class="technician" action="'
		. $this->view->url(array('mode' => 'bt'), 'clientFilter')
		. '">Podle bezpečnostního technika</a> | <a class="coordinator" action="'
		. $this->view->url(array('mode' => 'koo'), 'clientFilter')
		. '">Podle koordinátora</a> | <a class="town" action="'
		. $this->view->url(array('mode' => 'obec'), 'clientFilter')
		. '">Podle obce</a> | <a class="district" action="'
		. $this->view->url(array('mode' => 'okres'), 'clientFilter')
		. '">Podle okresu</a> | <a class="lastOpen" action="'
		. $this->view->url(array('mode' => 'naposledy'), 'clientFilter')
		. '">Naposledy otevřené</a></div>';
	}
}