<?php
class My_View_Helper_WorkplaceRisk extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function workplaceRisk($name, $value = null, $attribs = null){
		$this->html = '';
		$risk = $note = '';
		if($value){
			$risk = $value['risk'];
			$note = $value['note'];
		}
		
		$helper = new Zend_View_Helper_FormText();
		$helper->setView($this->view);
		
		$this->html .= '<td><label for="' . $name . '[risk]">Riziko</label></td><td colspan="2">' . $helper->formText($name . '[risk]', $risk, array('filters' => array('StringTrim', 'StripTags'))) . '</td>';
		$this->html .= '<td><label for="' . $name . '[note]">Popis, poznámka</label></td><td colspan="2">' . $helper->formText($name . '[note]', $note, array('filters' => array('StringTrim', 'StripTags'))) . '</td>';
		
		return $this->html;
	}
	
}