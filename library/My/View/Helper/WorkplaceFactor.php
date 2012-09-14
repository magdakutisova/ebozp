<?php
class My_View_Helper_WorkplaceFactor extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function workplaceFactor($name, $value = null, $attribs = null){
		$this->html = '';
		$factor = $applies = $note = '';
		if($value){
			$factor = $value['factor'];
			$applies = $value['applies'];
			$note = $value['note'];
		}

		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		$helperCheckbox = new Zend_View_Helper_FormCheckbox();
		$helperCheckbox->setView($this->view);
		
		$checked = isset($value['applies']) && $value['applies'];
		
		$this->html .= '<td><label for="' . $name . '[factor]">Faktor</label></td><td>' . $helperText->formText($name . '[factor]', $factor, array('filters' => array('StringTrim', 'StripTags'))) . '</td>';
		$this->html .= '<td><label for="' . $name . '[applies]">Platí</label></td><td>' . $helperCheckbox->formCheckbox($name . '[applies]', $applies, array('value' => 1, 'checked' => $checked, 'isArray' => true), array(1, null)) . '</td>';
		$this->html .= '<td><label for="' . $name . '[note]">Poznámka</label></td><td>' . $helperText->formText($name . '[note]', $note, array('filters' => array('StringTrim', 'StripTags')));

		return $this->html;
	}
	
}