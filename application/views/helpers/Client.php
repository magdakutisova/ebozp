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
				. ', ' . $client->getInvoiceCode()
				. ', ' . $client->getInvoiceTown()
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">IČO: </span>'
			. $client->getCompanyNumber()
			. '</p>';
		if ($client->getTaxNumber()){
			$content .= '<p class="no-margin"><span class="bold">DIČ: </span>'
				. $client->getTaxNumber()
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">Adresa sídla organizace: </span>'
			. $client->getHeadquartersStreet()
			. ', ' . $client->getHeadquartersCode()
			. ', ' . $client->getHeadquartersTown()
			. '</p><p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
			. $subsidiary->getContactPerson() . ', telefon: '
			. $subsidiary->getPhone() . ', e-mail: '
			. $subsidiary->getEmail() . '</p>';
		if ($client->getBusiness()){
			$content .= '<p class="no-margin"><span class="bold">Činnost klienta: </span>'
				. $client->getBusiness()
				. '</p>';
		}
		//TODO technik 
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
		$content .= '<p class="no-margin"><span class="bold">Pojišťovna: </span>'
			. $client->getInsuranceCompany()
			. '</p>';
		//TODO odpovědní zaměstnanci a soukromá poznámka

		return $content;
	}
	
}