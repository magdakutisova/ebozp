<?php
class My_View_Helper_ChemicalComplete extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function chemicalComplete($name, $value = null, $attribs = null){
		$this->html = '';
		$idChemical = $chemical = $newChemical = $usePurpose = $usualAmount = '';
		
		if(isset($attribs['multiOptions'])){
			$multiOptions = $attribs['multiOptions'];
		}
		else{
			$multiOptions = null;
		}
		
		$toEdit = false;
		if(isset($attribs['toEdit'])){
			$toEdit = $attribs['toEdit'];
		}
		
		if($value){
			$idChemical = $value['id_chemical'];
			$chemical = $value['chemical'];
			$newChemical = $value['new_chemical'];
			$usePurpose = $value['use_purpose'];
			$usualAmount = $value['usual_amount'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		$helperSubmit = new Zend_View_Helper_FormSubmit();
		$helperSubmit->setView($this->view);
		
		if($toEdit){
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_chemical]', $idChemical);
			$this->html .= '<td><label for="' . $name . '[chemical]">Chemická látka</label></td><td colspan="4">' . $helperText->formText($name . '[chemical]', $chemical, array('readonly' => 'readonly')) . '</td><td colspan="2">' . $helperSubmit->formSubmit($name . '[submit]', 'Odebrat', array('class' => 'hideTr')) . '</td>';
			$this->html .= '</tr><tr>';
			$this->html .= '<td><label for="' . $name . '[usual_amount]">Obvyklé množství</label></td><td>' . $helperText->formText($name . '[usual_amount]', $usualAmount) . '</td><td><label for="' . $name . '[use_purpose]">Účel použití</label></td><td>' . $helperText->formText($name . '[use_purpose]', $usePurpose) . '</td>';
			$this->html .= '</tr>';
		}
		else{
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_chemical]', $idChemical);
			$this->html .= '<td><label for="' . $name . '[chemical]">Vyberte chemickou látku</label></td><td>' . $helperSelect->formSelect($name . '[chemical]', $chemical, null, $multiOptions) . '</td><td><label for="' . $name . '[new_chemical]">nebo vepište novou</label></td><td>' . $helperText->formText($name . '[new_chemical]', $newChemical) . '</td>';
			$this->html .= '</tr><tr>';
			$this->html .= '<td><label for="' . $name . '[usual_amount]">Obvyklé množství</label></td><td>' . $helperText->formText($name . '[usual_amount]', $usualAmount) . '</td><td><label for="' . $name . '[use_purpose]">Účel použití</label></td><td>' . $helperText->formText($name . '[use_purpose]', $usePurpose) . '</td>';
			$this->html .= '</tr>';
		}
		
		return $this->html;
	}
	
}