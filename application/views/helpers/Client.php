<?php
class Zend_View_Helper_Client extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function client(){
		$client = $this->view->client;
		$subsidiary = $this->view->subsidiary;
		$content = '';

		if($client->getInvoiceStreet()){
			$content .= '<p class="no-margin"><span class="bold">Fakturační adresa: </span>'
				. $client->getInvoiceStreet()
				. ', ' . $client->getInvoiceTown()
				. ' ' . $client->getInvoiceCode()
				. '</p>';
		}
		if($client->getCompanyNumber()){
			$content .= '<p class="no-margin"><span class="bold">IČO: </span>'
				. $client->getCompanyNumber()
				. '</p>';
		}
		if ($client->getTaxNumber()){
			$content .= '<p class="no-margin"><span class="bold">DIČ: </span>'
				. $client->getTaxNumber()
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">Adresa sídla organizace: </span>'
			. $client->getHeadquartersStreet()
			. ', ' . $client->getHeadquartersTown()
			. ' ' . $client->getHeadquartersCode()
			. '</p>';
		if($subsidiary['subsidiary']->getDistrict()){
			$content .= '<p class="no-margin"><span class="bold">Okres: </span>'
					. $subsidiary['subsidiary']->getDistrict()
					. '</p>';
		}
		if($subsidiary['contact_persons'][0]->getIdContactPerson() != null){
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
		if($subsidiary['doctors'][0]->getIdDoctor() != null){
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
		if($subsidiary['responsibles'][0]['responsibility'] != null){
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
		if ($client->getBusiness()){
			$content .= '<p class="no-margin"><span class="bold">Činnost klienta: </span>'
				. $client->getBusiness()
				. '</p>';
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
		if($client->getPrivate() && $this->view->canViewPrivate){
			$content .= '<p class="no-margin"><span class="bold">Soukromá poznámka: </span>'
				. $client->getPrivate()
				. '</p>';
		}

		return $content;
	}
	
}