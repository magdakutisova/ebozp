<?php
class My_View_Helper_Work extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function work($name, $value = null, $attribs = null){
		$this->html = '';
		$idWork = $work = $newWork = '';
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
			$idWork = $value['id_work'];
			$work = $value['work'];
			$newWork = $value['new_work'];
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
			$this->html .= $helperHidden->formHidden($name . '[id_work]', $idWork);
			$this->html .= '<td><label for"' . $name . '[work]">Pracovní činnost</label></td><td colspan="4">' . $helperText->formText($name . '[work]', $work, array('readonly' => 'readonly')) . '</td><td colspan="2">' . $helperSubmit->formSubmit($name . '[submit]', 'Odebrat', array('class' => 'remove_work')) . '</td>';
			$this->html .= '</tr>';
		}
		else{
			$this->html .= '<tr id="' . $name . '">';
			$this->html .= $helperHidden->formHidden($name . '[id_work]', $idWork);
			$this->html .= '<td><label for"' . $name . '[work]">Vyberte pracovní činnost</label></td><td>' . $helperSelect->formSelect($name . '[work]', $work, null, $multiOptions) . '</td><td><label for="' . $name . '[new_work]">nebo vepište novou</label></td><td>' . $helperText->formText($name . '[new_work]', $newWork) . '</td>';
			$this->html .= '</tr>';
		}
		
		return $this->html;
	}
	
}