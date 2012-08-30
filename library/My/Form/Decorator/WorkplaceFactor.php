<?php
class My_Form_Decorator_WorkplaceFactor extends Zend_Form_Decorator_Abstract{
	
	protected $_format = '<td><label for="%s">%s</label></td><td><input id="%s" name="%s[]" type="%s" value="%s" %s/></td>';
	
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
		$otherMarkup = '';
		if ($el->getFactor() != ''){
			$otherMarkup = 'readonly="true"';
		}
		return $this->renderEl($this->_format, 'factor', $el->getFactor(), $el, 'text', $otherMarkup);
	}
	
	protected function renderApplies($el){
		$otherMarkup = '';
		if ($el->getFactor() == ''){
			$otherMarkup = 'checked="checked"';
		}
		return $this->renderEl($this->_format, 'applies', $el->getApplies(), $el, 'checkbox', $otherMarkup);
	}
	
	protected function renderNote($el){
		return $this->renderEl($this->_format, 'note', $el->getNote(), $el, 'text');
	}
	
	protected function renderEl($format, $type, $value, $el, $elementType, $otherMarkup = ''){
		$name = $el->getFullyQualifiedName();
		return sprintf($format, $el->getID($type), $el->getLabel($type), $el->getID($type), $name . '[' . $type . ']', $elementType, $value, $otherMarkup);
	}
	
}