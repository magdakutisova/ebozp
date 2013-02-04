<?php
class My_View_Helper_EnvironmentFactor extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function environmentFactor($name, $value = null, $attribs = null){
		$this->html = '';
		$idEnvironmentFactor = $factor = $category = $protectionMeasures = $measurementTaken = $note = $private = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
		$multiOptions2 = isset($attribs['multiOptions2']) ? $attribs['multiOptions2'] : null;
		$multiOptions3 = isset($attribs['multiOptions3']) ? $attribs['multiOptions3'] : null;
		$canViewPrivate = isset($attribs['canViewPrivate']) ? $attribs['canViewPrivate'] : null;
		
		if($value){
			$idEnvironmentFactor = $value['id_environment_factor'];
			$factor = $value['factor'];
			$category = $value['category'];
			$protectionMeasures = $value['protection_measures'];
			$measurementTaken = $value['measurement_taken'];
			$note = $value['note'];
			$private = $value['private'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_environment_factor]', $idEnvironmentFactor);
		$this->html .= '<td colspan="2"><label for="' . $name . '[factor]">Faktor pracovního prostředí</label></td>';
		$this->html .= '<td><label for="' . $name . '[category]">Zařazeno do kategorie</label></td>';
		$this->html .= '<td colspan="2"><label for="' . $name . '[protection_measures]">Ochranná opatření proti FPP</label></td>';
		$this->html .= '<td><label for="' . $name . '[measurement_taken]">Měření provedeno</label></td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="2">' . $helperSelect->formSelect($name . '[factor]', $factor, null, $multiOptions) . '</td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[category]', $category, null, $multiOptions2) . '</td>';
		$this->html .= '<td colspan="2">' . $helperText->formText($name . '[protection_measures]', $protectionMeasures) . '</td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[measurement_taken]', $measurementTaken, null, $multiOptions3) . '</td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="3"><label for="' . $name . '[note]">Poznámka k FPP</label><br/>' . $helperText->formText($name . '[note]', $note) . '</td>';
		if($canViewPrivate){
			$this->html .= '<td colspan="3"><label for="' . $name . '[private]">Soukromá poznámka k FPP</label><br/>' . $helperText->formText($name . '[private]', $private) . '</td>';
		}
		else{
			$this->html .= '<td colspan="3"></td>';
		}
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}