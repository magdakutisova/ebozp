<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Alone
 *
 * @author petr
 */
class Application_Form_TechnicalDevice_Alone extends Application_Form_TechnicalDevice {
    
    public function init() {
        $elementDecorator = array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		);
        
        $this->addElement("select", "workplace_id", array(
            "required" => false,
            "label" => "Pracoviště",
            "multiOptions" => array("0" => "-- Žádné --"),
            "decorators" => $elementDecorator
        ));
        
        $this->addElement("select", "position_id", array(
            "required" => false,
            "label" => "Pracovní pozice",
            "multiOptions" => array("0" => "-- Žádná --"),
            "decorators" => $elementDecorator
        ));
        
        parent::init();
        
        $this->addElement("submit", "save_technicaldevice", array(
            "label" => "Uložit technický prostředek",
            "decorators" => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            )
        ));
    }
}

?>
