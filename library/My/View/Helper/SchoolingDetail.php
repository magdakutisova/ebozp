<?php
class My_View_Helper_SchoolingDetail extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function schoolingDetail($name, $value = null, $attribs = null){
		$this->html = '';
		$idSchooling = $schooling = $note = $private = '';
		
		$canViewPrivate = isset($attribs['canViewPrivate']) ? $attribs['canViewPrivate'] : null;
		
		if($value){
			$idSchooling = $value['id_schooling'];
			$schooling = $value['schooling'];
			$note = $value['note'];
			$private = $value['private'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperTextarea = new Zend_View_Helper_FormTextarea();
		$helperTextarea->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_schooling]', $idSchooling);
		$this->html .= '<td><label for="' . $name . '[schooling]">' . $value['schooling'] . '</label>' . $helperHidden->formHidden($name . '[schooling]', $schooling) . '</td>';
		$this->html .= '<td colspan=2><label for="' . $name . '[note]">Poznámka</label><br/>' . $helperTextarea->formTextarea($name . '[note]', $note) . '</td>';
		if($canViewPrivate){
			$this->html .= '<td colspan=2><label for="' . $name . '[private]">Soukromá poznámka</label><br/>' . $helperTextarea->formTextarea($name . '[private]', $private) . '</td>';
		}
		else{
			$this->html .= '<td colspan=2></td>';
		}
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}