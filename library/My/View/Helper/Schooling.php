<?php
class My_View_Helper_Schooling extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function schooling($name, $value = null, $attribs = null){
		$this->html = '';
		$idSchooling = $schooling = $lastExecution = $note = $private = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
		$canViewPrivate = isset($attribs['canViewPrivate']) ? $attribs['canViewPrivate'] : null;
		
		if($value){
			$idSchooling = $value['id_schooling'];
			$schooling = $value['schooling'];
			$lastExecution = $value['last_execution'];
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
		$this->html .= $helperHidden->formHidden($name . '[id_schooling]', $idSchooling);
		$this->html .= '<td colspan="3"><label for="' . $name . '[schooling]">Název školení</label></td>';
		$this->html .= '<td colspan="3"><label for="' . $name . '[last_execution]">Naposledy provedeno</label></td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="3">' . $helperSelect->formSelect($name . '[schooling]', $schooling, null, $multiOptions) . '</td>';
		$this->html .= '<td colspan="3">' . $helperText->formText($name . '[last_execution]', $lastExecution);
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="3"><label for="' . $name . '[note]">Poznámka ke školení</label><br/>' . $helperText->formText($name . '[note]', $note) . '</td>';
		if($canViewPrivate){
			$this->html .= '<td colspan="3"><label for="' . $name . '[private]">Soukromá poznámka ke školení</label></br>' . $helperText->formText($name . '[private]', $private) . '</td><td></td>';
		}
		else{
			$this->html .= '<td colspan="3"></td>';
		}
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}