<?php
class My_View_Helper_Chemical2Detail extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function chemical2Detail($name, $value = null, $attribs = null){
		$this->html = '';
		$idChemical = $chemical = $exposition = '';
		
		if($value){
			$idChemical = $value['id_chemical'];
			$chemical = $value['chemical'];
			$exposition = $value['exposition'];
		}
		
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_chemical]', $idChemical);
		$this->html .= '<td><label for="' . $name . '[chemical]">' . $value['chemical'] . '</label>' . $helperHidden->formHidden($name . '[chemical]', $chemical) . '</td>';
		$this->html .= '<td><label for="' . $name . '[exposition]">Expozice</label><br/>' . $helperText->formText($name . '[exposition]', $exposition) . '</td>';
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}