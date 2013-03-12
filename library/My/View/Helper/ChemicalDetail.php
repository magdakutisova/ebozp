<?php
class My_View_Helper_ChemicalDetail extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function chemicalDetail($name, $value = null, $attribs = null){
		$this->html = '';
		$idChemical = $chemical = $usePurpose = $usualAmount = '';
		
		if($value){
			$idChemical = $value['id_chemical'];
			$chemical = $value['chemical'];
			$usePurpose = $value['use_purpose'];
			$usualAmount = $value['usual_amount'];
		}
		
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_chemical]', $idChemical);
		$this->html .= '<td><label for="' . $name . '[chemical]">' . $value['chemical'] . '</label>' . $helperHidden->formHidden($name . '[chemical]', $chemical) . '</td>';
		$this->html .= '<td><label for="' . $name . '[usual_amount]">Obvyklé množství</label><br/>' . $helperText->formText($name . '[usual_amount]', $usualAmount) . '</td><td><label for="' . $name . '[use_purpose]">Účel použití</label><br/>' . $helperText->formText($name . '[use_purpose]', $usePurpose) . '</td>';
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}