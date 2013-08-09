<?php
class Zend_View_Helper_Subsidiary extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function subsidiary(){
		$client = $this->view->client;
		$subsidiary = $this->view->subsidiary;
		$content = '';
		
		$content .= '<p class="no-margin"><span class="bold">Status: </span> ';
		if($subsidiary['subsidiary']->getActive()){
			$content .= 'aktivní</p>';
		}
		else{
			$content .= 'neaktivní</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">Adresa pobočky: </span>'
			. $subsidiary['subsidiary']->getSubsidiaryStreet()
			. ', ' . $subsidiary['subsidiary']->getSubsidiaryTown()
			. ' ' . $subsidiary['subsidiary']->getSubsidiaryCode()
			. '</p>';
		if($subsidiary['subsidiary']->getDistrict()){
			$content .= '<p class="no-margin"><span class="bold">Okres: </span>'
					. $subsidiary['subsidiary']->getDistrict()
					. '</p>';
		}
		if(isset($subsidiary['contact_persons'])){
			foreach($subsidiary['contact_persons'] as $contactPerson){
				$content .= '<p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
						. $contactPerson->getName();
				if($contactPerson->getPhone() != ''){
					$content .= ', telefon: '. $contactPerson->getPhone();
				}
				if($contactPerson->getEmail() != ''){
					$content .= ', e-mail: ' . $contactPerson->getEmail();
				}
				$content .= '</p>';
			}
		}
		if(isset($subsidiary['doctors'])){
			foreach($subsidiary['doctors'] as $doctor){
				$content .= '<p class="no-margin"><span class="bold">Poskytovatel pracovnělékařské péče: </span>'
						. $doctor->getName();
				if($doctor->getStreet() != '' || $doctor->getTown() != ''){
					$content .= ', adresa:';
					if($doctor->getStreet() != ''){
						$content .= ' ' . $doctor->getStreet();
					}
					if($doctor->getTown() != ''){
						$content .= ' ' . $doctor->getTown();
					}
				}
				$content .= '</p>';
			}
		}
		if(isset($subsidiary['responsibles'])){
			foreach($subsidiary['responsibles'] as $responsible){
				$content .= '<p class="no-margin"><span class="bold">' . $responsible['responsibility'] . ': </span>'
						. $responsible['employee']->getFirstName() . ' ' . $responsible['employee']->getSurname();
				if($responsible['employee']->getPhone() != ''){
					$content .= ', telefon: '. $responsible['employee']->getPhone();
				}
				if($responsible['employee']->getEmail() != ''){
					$content .= ', e-mail: ' . $responsible['employee']->getEmail();
				}
			}
		}
		if ($this->view->technicians){
			$content .= '<p class="no-margin"><span class="bold">Technik: </span>'
				. $this->view->technicians
				. '</p>';
		}
		if($subsidiary['subsidiary']->getSupervisionFrequency()){
			$content .= '<p class="no-margin"><span class="bold">Četnost dohlídek: </span>'
				. $subsidiary['subsidiary']->getSupervisionFrequency() . 'x ročně'
				. '</p>';
		}
		if($subsidiary['subsidiary']->getDifficulty()){
			$content .= '<p class="no-margin"><span class="bold">Náročnost trvání dohlídky: </span>'
					. $subsidiary['subsidiary']->getDifficulty() . ' dne'
					. '</p>';
		}
		if($subsidiary['subsidiary']->getInsuranceCompany()){
			$content .= '<p class="no-margin"><span class="bold">Pojišťovna: </span>'
					. $subsidiary['subsidiary']->getInsuranceCompany()
					. '</p>';
		}
		if($subsidiary['subsidiary']->getPrivate() && $this->view->canViewPrivate){
			$content .= '<p class="no-margin"><span class="bold">Soukromá poznámka: </span>'
				. $subsidiary['subsidiary']->getPrivate()
				. '</p>';
		}

		return $content;
	}
	
}