<?php
class My_View_Helper_TechnicalDevice extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function technicalDevice($name, $value = null, $attribs = null){
		$this->html = '';
		$idTechnicalDevice = $sort = $newSort = $type = $newType = $note = $private = '';
		
		if(isset($attribs['multiOptions'])){
			$multiOptions = $attribs['multiOptions'];
		}
		else{
			$multiOptions = null;
		}
		
		if(isset($attribs['multiOptions2'])){
			$multiOptions2 = $attribs['multiOptions2'];
		}
		else{
			$multiOptions2 = null;
		}

		if($value){
			$idTechnicalDevice = $value['id_technical_device'];
			$sort = $value['sort'];
			$newSort = $value['new_sort'];
			$type = $value['type'];
			$newType = $value['new_type'];
			$note = $value['note'];
			$private = $value['private'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '" class="main">';
		$this->html .= $helperHidden->formHidden($name . '[id_technical_device]', $idTechnicalDevice);
		$this->html .= '<td>Vyberte technický prostředek</td><td colspan=2><label for ="' . $name . '[sort]">Název </label>' . $helperSelect->formSelect($name . '[sort]', $sort,  null, $multiOptions) . '</td><td colspan=2><label for ="' . $name . '[type]">Typ </label>' . $helperSelect->formSelect($name . '[type]', $type, null, $multiOptions2) . '</td></tr>';
		$this->html .= '<tr><td>Nebo vepište nový</td><td colspan=2><label for="' . $name . '[new_sort]">Název </label>' . $helperText->formText($name . '[new_sort]', $newSort) . '</td><td style="width: 300px;" colspan=2><label for="' . $name . '[new_type]">Typ </label>' . $helperText->formText($name . '[new_type]', $newType) . '</td>';
		$this->html .= '<td class="hint"><a class="showNotes">Poznámka</a></td>';
		$this->html .= '</tr><tr id="' . $name . '" class="hidden">';
		$this->html .= '<td><label for="' . $name . '[note]">Poznámka</label></td><td>' . $helperText->formText($name . '[note]', $note) . '</td>';
		$this->html .= '<td><label for="' . $name . '[private]">Soukromá poznámka</td><td>' . $helperText->formText($name . '[private]', $private) . '</td>';
		$this->html .= '</tr>';
		
		//udělat do řádků
		
		return $this->html;
	}
	
}