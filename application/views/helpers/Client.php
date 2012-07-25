<?php
class Zend_View_Helper_Client extends Zend_View_Helper_Abstract{
	
	public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }
	
	public function client(){
		$client = $this->view->client;
		$content = '';

		if($client['invoice_street']){
			$content .= '<p class="no-margin"><span class="bold">Fakturační adresa: </span>'
				. $client['invoice_street']
				. ', ' . $client['invoice_code']
				. ', ' . $client['invoice_town']
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">IČO: </span>'
			. $client['company_number']
			. '</p>';
		if ($client['tax_number']){
			$content .= '<p class="no-margin"><span class="bold">DIČ: </span>'
				. $client['tax_number']
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">Adresa sídla organizace: </span>'
			. $client['headquarters_street']
			. ', ' . $client['headquarters_code']
			. ', ' . $client['headquarters_town']
			. '</p><p class="no-margin"><span class="bold">Kontaktní osoba BOZP a PO: </span>'
			. $client['contact_person'] . ', telefon: '
			. $client['phone'] . ', e-mail: '
			. $client['email'] . '</p>';
		if ($client['business']){
			$content .= '<p class="no-margin"><span class="bold">Činnost klienta: </span>'
				. $client['business']
				. '</p>';
		}
		//TODO technik 
		if($client['supervision_frequency']){
			$content .= '<p class="no-margin"><span class="bold">Četnost dohlídek: </span>'
				. $client['supervision_frequency']
				. '</p>';
		}
		if ($client['doctor']){
			$content .= '<p class="no-margin"><span class="bold">Poskytovatel pracovnělékařské péče: </span>'
				. $client['doctor']
				. '</p>';
		}
		$content .= '<p class="no-margin"><span class="bold">Pojišťovna: </span>'
			. $client['insurance_company']
			. '</p>';
		//TODO odpovědní zaměstnanci a soukromá poznámka

		return $content;
	}
	
}