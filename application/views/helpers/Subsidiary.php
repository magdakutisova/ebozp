<?php
class Zend_View_Helper_Subsidiary extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function subsidiary(){
		$client = $this->view->client;
		$subsidiary = $this->view->subsidiary;
		$content = '';
		
		$content .= '<p class="no-margin"><span class="bold">Adresa pobočky: </span>'
			. $subsidiary['subsidiary_street']
			. ', ' . $subsidiary['subsidiary_code']
			. ', ' . $subsidiary['subsidiary_town']
			. '</p>';
		
		if($subsidiary['invoice_street']){
			$content .= '<p class="no-margin"><span class="bold">Fakturační adresa: </span>'
				. $subsidiary['invoice_street']
				. ', ' . $subsidiary['invoice_code']
				. ', ' . $subsidiary['invoice_town']
				. '</p>';
		}
		
		$content .= '<p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
			. $subsidiary['contact_person'] . ', telefon: '
			. $subsidiary['phone'] . ', e-mail: '
			. $subsidiary['email'] . '</p>';
		
		if($subsidiary['supervision_frequency']){
			$content .= '<p class="no-margin"><span class="bold">Četnost dohlídek: </span>'
				. $subsidiary['supervision_frequency']
				. '</p>';
		}
		if ($subsidiary['doctor']){
			$content .= '<p class="no-margin"><span class="bold">Poskytovatel pracovnělékařské péče: </span>'
				. $subsidiary['doctor']
				. '</p>';
		}
		//TODO odpovědní zaměstnanci a soukromá poznámka

		return $content;
	}
	
}