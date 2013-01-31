<?php
class My_View_Helper_Chemical extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function chemical($name, $value = null, $attribs = null){
		$this->html = '';
		$idChemical = $chemical = $newChemical = $exposition = '';
		
		if(isset($attribs['multiOptions'])){
			$multiOptions = $attribs['multiOptions'];
		}
		else{
			$multiOptions = null;
		}
		
		if($value){
			$idChemical = $value['id_chemical'];
			$chemical = $value['chemical'];
			$newChemical = $value['new_chemical'];
			$exposition = $value['exposition'];
		}
		
		$helperSelect = new Zend_View_Helper_FormSelect();
		$helperSelect->setView($this->view);
		$helperHidden = new Zend_View_Helper_FormHidden();
		$helperHidden->setView($this->view);
		$helperText = new Zend_View_Helper_FormText();
		$helperText->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= $helperHidden->formHidden($name . '[id_chemical]', $idChemical);
		$this->html .= '<td colspan="3"><label for="' . $name . '[chemical]">Vyberte chemickou látku</label><br/>' . $helperSelect->formSelect($name . '[chemical]', $chemical, null, $multiOptions) . '</td><td colspan="3"><label for="' . $name . '[new_chemical]">nebo vepište novou</label><br/>' . $helperText->formText($name . '[new_chemical]', $newChemical) . '</td>';
		$this->html .= '</tr><tr>';
		$this->html .= '<td colspan="3"><label for="' . $name . '[exposition]">Expozice</label><br/>' . $helperText->formText($name . '[exposition]', $exposition) . '</td><td colspan="3"></td>';
		$this->html .= '</tr>';
		
		return $this->html;
	}
	
}