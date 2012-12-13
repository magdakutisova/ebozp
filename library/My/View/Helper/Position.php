<?php
class My_View_Helper_Position extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function position($name, $value = null, $attribs = null){
		$this->html = '';
		$idPosition = $position = $newPosition = '';
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
			$idPosition = $value['id_position'];
			$position = $value['position'];
			$newPosition = $value['new_position'];
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
			$this->html .= $helperHidden->formHidden($name . '[id_position]', $idPosition);
			$this->html .= '<td><label for="' . $name . '[position]">Pracovní pozice</label></td><td colspan="4">' . $helperText->formText($name . '[position]', $position, array('readonly' => 'readonly')) . '</td><td colspan="2">' . $helperSubmit->formSubmit($name . '[submit]', 'Odebrat', array('class' => 'remove_position')) . '</td>';
			$this->html .= '</tr>';	
		}
		else{
			$this->html .= '<tr id="'. $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_position]', $idPosition);
			$this->html .= '<td><label for="' . $name . '[position]">Vyberte pracovní pozici</label></td><td>' . $helperSelect->formSelect($name . '[position]', $position, null, $multiOptions) . '</td><td><label for="' . $name . '[new_position]">nebo vepište novou</label></td><td>' . $helperText->formText($name . '[new_position]', $newPosition) . '</td>';
			$this->html .= '</tr>';	
		}
		
		return $this->html;
	}
	
}