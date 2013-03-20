<?php
class My_View_Helper_EnvironmentFactorDetail extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function environmentFactorDetail($name, $value = null, $attribs = null){
		$this->html = '';
		$idEnvironmentFactor = $factor = $category = $protectionMeasures = $measurementTaken = $note = $private = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
		$multiOptions2 = isset($attribs['multiOptions2']) ? $attribs['multiOptions2'] : null;
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
		$helperTextarea = new Zend_View_Helper_FormTextarea();
		$helperTextarea->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_environment_factor]', $idEnvironmentFactor);
		$this->html .= '<td colspan="6"><label for="' . $name . '[factor]">' . $value['factor'] . '</label></td>'
				. $helperHidden->formHidden($name . '[factor]', $factor);
		$this->html .= '</tr><tr>';
		$this->html .= '<td><label for="' . $name . '[category]">Zařazeno do kategorie</label></td>';
		$this->html .= '<td colspan="2">' . $helperSelect->formSelect($name . '[category]', $category, null, $multiOptions) . '</td>';
		$this->html .= '<td><label for="' . $name . '[measurement_taken]">Měření provedeno</label></td>';
		$this->html .= '<td>' . $helperSelect->formSelect($name . '[measurement_taken]', $measurementTaken, null, $multiOptions2) . '</td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="6"><label for="' . $name . '[protection_measures]">Ochranná opatření proti FPP</label><br/>' . $helperTextarea->formTextarea($name . '[protection_measures]', $protectionMeasures) . '</td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="3"><label for="' . $name . '[note]">Poznámka k FPP</label><br/>' . $helperTextarea->formTextarea($name . '[note]', $note) . '</td>';
		if($canViewPrivate){
			$this->html .= '<td colspan="3"><label for="' . $name . '[private]">Soukromá poznámka k FPP</label><br/>' . $helperTextarea->formTextarea($name . '[private]', $private) . '</td>';
		}
		else{
			$this->html .= '<td colspan="3"></td>';
		}
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}