<?php
class My_View_Helper_WorkDetail extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function workDetail($name, $value = null, $attribs = null){
		$this->html = '';
		$idWork = $work = $frequency = $newFrequency = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
			
		if($value){
			$idWork = $value['id_work'];
			$work = $value['work'];
			$frequency = $value['frequency'];
			$newFrequency = $value['new_frequency'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_work]', $idWork);
		$this->html .= '<td><label for="' . $name . '[work]">' . $value['work'] . '</label></td>';
		$this->html .= '<td colspan="2"><label for="' . $name . '[frequency]">Četnost pracovní činnosti</label><br/>';
		$this->html .= $helperSelect->formSelect($name . '[frequency]', $frequency, null, $multiOptions) . '</td>';
		$this->html .= '<td colspan="2"><label for="' . $name . '[new_frequency]" class="hidden">Jiná četnost (ne menší než 1x měsíčně)</label><br/>';
		$this->html .= $helperText->formText($name . '[new_frequency]', $newFrequency, array('hidden' => true)) . '</td>';
		$this->html .= '</tr>';
				
		return $this->html;
		
	}
	
}