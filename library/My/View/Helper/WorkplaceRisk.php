<?php
class My_View_Helper_WorkplaceRisk extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function workplaceRisk($name, $value = null, $attribs = null){
		$this->html = '';
		$idWorkplaceRisk = $risk = $note = '';
		if($value){
			$idWorkplaceRisk = $value['id_workplace_risk'];
			$risk = $value['risk'];
			$note = $value['note'];
		}
		
		$helper = new Zend_View_Helper_FormText();
		$helper->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		
		$this->html .= $helperHidden->formHidden($name . '[id_workplace_risk]', $idWorkplaceRisk);
		$this->html .= '<td><label for="' . $name . '[risk]">Riziko</label></td><td colspan="2">' . $helper->formText($name . '[risk]', $risk) . '</td>';
		$this->html .= '<td><label for="' . $name . '[note]">Popis, poznámka</label></td><td colspan="2">' . $helper->formText($name . '[note]', $note) . '</td>';
		
		
		return $this->html;
	}
	
}