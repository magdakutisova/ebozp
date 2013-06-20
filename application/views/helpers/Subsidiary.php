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
			. ', ' . $subsidiary->getSubsidiaryTown()
			. ' ' . $subsidiary->getSubsidiaryCode()
			. '</p>';
		if($subsidiary->getDistrict()){
			$content .= '<p class="no-margin"><span class="bold">Okres: </span>'
					. $subsidiary->getDistrict()
					. '</p>';
		}
		if($subsidiary->getContactPerson()){
			$content .= '<p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
				. $subsidiary->getContactPerson() . ', telefon: '
				. $subsidiary->getPhone() . ', e-mail: '
				. $subsidiary->getEmail() . '</p>';
		}	
		if ($this->view->technicians){
			$content .= '<p class="no-margin"><span class="bold">Technik: </span>'
				. $this->view->technicians
				. '</p>';
		}
		if($subsidiary->getSupervisionFrequency()){
			$content .= '<p class="no-margin"><span class="bold">Četnost dohlídek: </span>'
				. $subsidiary->getSupervisionFrequency()
				. '</p>';
		}
		if($subsidiary->getDifficulty()){
			$content .= '<p class="no-margin"><span class="bold">Náročnost: </span>'
					. $subsidiary->getDifficulty()
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