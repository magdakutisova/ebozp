<?php
class My_Form_Decorator_WorkplaceFactor extends Zend_Form_Decorator_Abstract{
	
	protected $_format = '<td><label for="%s">%s</label></td><td><input id="%s" name="%s[]" type="%s" value="%s"/></td>';
	protected $_formatFactor = '<td><input %7$s id="%3$s" name="%4$s[]" type="%5$s" value="%6$s"/></td><td><label for="%1$s">%2$s</label></td>';
	
	public function render($content){
		$el = $this->getElement();
		if(!$el instanceof My_Form_Element_WorkplaceFactor){
			return $content;
		}
		
		$view = $el->getView();
		if(!$view instanceof Zend_View_Interface){
			return $content;
		}
		
		$markup = $this->renderFactor($el) . $this->renderApplies($el) . $this->renderNote($el);
		
		switch($this->getPlacement()){
			case self::PREPEND:
				return $markup . $this->getSeparator() . $content;
			case self::APPEND:
			default:
				return $content . $this->getSeparator() . $markup;
		}
	}
	
	protected function renderFactor($el){
		$disabled = '';
		if ($el->getFactorLabel() != ''){
			$disabled = 'disabled';
		}
		return $this->renderEl($this->_formatFactor, 'factor', $el->getFactor(), $el, 'text', $disabled);
	}
	
	protected function renderApplies($el){
		return $this->renderEl($this->_format, 'applies', $el->getApplies(), $el, 'checkbox');
	}
	
	protected function renderNote($el){
		return $this->renderEl($this->_format, 'note', $el->getNote(), $el, 'text');
	}
	
	protected function renderEl($format, $type, $value, $el, $elementType, $disabled = ''){
		$name = $el->getFullyQualifiedName();
		return sprintf($format, $el->getID($type), $el->getLabel($type), $el->getID($type), $name . '[' . $type . ']', $elementType, $value, $disabled);
	}
	
}