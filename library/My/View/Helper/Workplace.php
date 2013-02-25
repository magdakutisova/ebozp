<?php
class My_View_Helper_Workplace extends Zend_View_Helper_FormElement{
	
	protected $html = '';
	
	public function workplace($name, $value = null, $attribs = null){
		$this->html = '';
		$workplaces = $newWorkplaces = '';
		
		$multiOptions = isset($attribs['multiOptions']) ? $attribs['multiOptions'] : null;
			
		if($value){
			$workplaces = $value['workplaces'];
			$newWorkplaces = $value['new_workplaces'];
		}
		
		$helperMultiCheckbox = new Zend_View_Helper_FormMultiCheckbox();
		$helperMultiCheckbox->setView($this->view);
		$helperTextarea = new Zend_View_Helper_FormTextarea();
		$helperTextarea->setView($this->view);
		
		$this->html .= '<tr id="' . $name . '">';
		$this->html .= '<td colspan="2"><label for="' . $name . '[workplaces]">Vyberte pracoviště, kde je pracovní pozice vykonávána</label><br/>';
		if($multiOptions != 0){
			$this->html .= '<div class="multiCheckbox">' . $helperMultiCheckbox->formMultiCheckbox($name . '[workplaces]', $workplaces, null, $multiOptions) . '</div></td>';
		}
		else{
			$this->html .= '<div class="multiCheckbox">Tento klient nemá zatím žádná pracoviště.</div>';
		}
		$this->html .= '<td colspan="3"><label for="' . $name . '[new_workplaces]">Případně zadejte v seznamu neuvedená pracoviště (každé na nový řádek)</label><br/>';
		$this->html .= $helperTextarea->formTextarea($name . '[new_workplaces]', $newWorkplaces) . '</td>';
		$this->html .= '</tr>';
				
		return $this->html;
		
	}
	
}