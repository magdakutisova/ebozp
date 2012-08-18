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
			. $subsidiary->getSubsidiaryStreet()
			. ', ' . $subsidiary->getSubsidiaryCode()
			. ', ' . $subsidiary->getSubsidiaryTown()
			. '</p>';
		
		$content .= '<p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
			. $subsidiary->getContactPerson() . ', telefon: '
			. $subsidiary->getPhone() . ', e-mail: '
			. $subsidiary->getEmail() . '</p>';
		
		if($subsidiary->getSupervisionFrequency()){
			$content .= '<p class="no-margin"><span class="bold">Četnost dohlídek: </span>'
				. $subsidiary->getSupervisionFrequency()
				. '</p>';
		}
		if ($subsidiary->getDoctor()){
			$content .= '<p class="no-margin"><span class="bold">Poskytovatel pracovnělékařské péče: </span>'
				. $subsidiary->getDoctor()
				. '</p>';
		}
		//TODO odpovědní zaměstnanci
		if($subsidiary->getPrivate() && $this->view->canViewPrivate){
			$content .= '<p class="no-margin"><span class="bold">Soukromá poznámka: </span>'
				. $subsidiary->getPrivate()
				. '</p>';
		}

		return $content;
	}
	
}