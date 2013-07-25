<?php
class Zend_View_Helper_Filters extends Zend_View_Helper_Abstract{
	
	public function filters($archived){
		if($archived){
			return '<div class="box"><h3>Filtrování</h3><a class="archive-list" action="'
					. $this->view->url(array('mode' => 'nazev'), 'clientArchivefilter')
					. '">Podle názvu</a> | <a class="archive-alphabet" action="'
							. $this->view->url(array('mode' => 'abeceda'), 'clientArchivefilter')
							. '">Podle abecedy</a> | <a class="archive-technician" action="'
									. $this->view->url(array('mode' => 'bt'), 'clientArchivefilter')
									. '">Podle bezpečnostního technika</a> | <a class="archive-coordinator" action="'
											. $this->view->url(array('mode' => 'koo'), 'clientArchivefilter')
											. '">Podle koordinátora</a> | <a class="archive-town" action="'
													. $this->view->url(array('mode' => 'obec'), 'clientArchivefilter')
													. '">Podle obce</a> | <a class="archive-district" action="'
															. $this->view->url(array('mode' => 'okres'), 'clientArchivefilter')
															. '">Podle okresu</a> | <a class="archive-lastOpen" action="'
																	. $this->view->url(array('mode' => 'naposledy'), 'clientArchivefilter')
																	. '">Naposledy otevřené</a></div>';
		}
		else{
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
}