<?php
class My_View_Helper_Position extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function position($name, $value = null, $attribs = null){
		$this->html = '';
		$idPosition = $position = $note = $private = '';
		if(isset($attribs['multiOptions'])){
			$multiOptions = $attribs['multiOptions'];
		}
		else{
			$multiOptions = null;
		}		
		
		if($value){
			$idPosition = $value['id_position'];
			$position = $value['name'];
			$note = $value['note'];
			$private = $value['private'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="'. $name . '" class="main">';
		$this->html .= $helperHidden->formHidden($name . '[id_position]', $idPosition);
		$this->html .= '<td colspan=2><label for="' . $name . '[name]">Název pracovní pozice</label></td><td colspan=4>' . $helperSelect->formSelect($name . '[name]', $position, null, $multiOptions) . '</td>';
		$this->html .= '<td class="hint"><a class="showNotes">Poznámka</a></td>';
		$this->html .= '</tr><tr id="' . $name . '" class="hidden">';
		$this->html .= '<td><label for="' . $name . '[note]">Poznámka</label></td><td>' . $helperText->formText($name . '[note]', $note) . '</td>';
		$this->html .= '<td><label for="' . $name . '[private]">Soukromá poznámka</td><td>' . $helperText->formText($name . '[private]', $private) . '</td>';
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}