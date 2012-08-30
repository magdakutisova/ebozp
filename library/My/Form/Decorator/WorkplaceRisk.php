<?php
class My_Form_Decorator_WorkplaceRisk extends Zend_Form_Decorator_Abstract{
	
	protected $_format = '<td><label for="%s">%s</label></td><td colspan="2"><input id="%s" name="%s[]" type="text" value="%s"/></td>';
	
	public function render($content){
		$el = $this->getElement();
		if(!$el instanceof My_Form_Element_WorkplaceRisk){
			return $content;
		}
		
		$view = $el->getView();
		if(!$view instanceof Zend_View_Interface){
			return $content;
		}
		
		$markup = $this->renderRisk($el) . $this->renderNote($el);
		
		switch($this->getPlacement()){
			case self::PREPEND:
				return $markup . $this->getSeparator() . $content;
			case self::APPEND:
			default:
				return $content . $this->getSeparator() . $markup;
		}
	}
	
	protected function renderRisk($el){
		return $this->renderEl('risk', $el->getRisk(), $el);
	}
	
	protected function renderNote($el){
		return $this->renderEl('note', $el->getNote(), $el);
	}
	
	protected function renderEl($type, $value, $el){
		$name = $el->getFullyQualifiedName();
		return sprintf($this->_format, $el->getID($type), $el->getLabel($type), $el->getID($type), $name . '[' . $type . ']', $value);
	}
}