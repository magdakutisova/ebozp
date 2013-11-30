<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ShowHidePanel
 *
 * @author petr
 */
class My_View_Helper_ShowHidePanel extends Zend_View_Helper_Abstract {
    
    private static $_id = 0;
    
    public function showHidePanel($content, $caption, $buttonCaption, $hidden = false) {
        $pattern = "<fieldset><legend>%s</legend><div>%s</div><div style='%s'>%s</div></fieldset>";
        
        $button = $this->view->formButton("show-hide-" . self::$_id++, $buttonCaption, array(
            "onclick" => "$(this).parent().next().toggle()"
        ));
        
        return sprintf($pattern, $caption, $button, $hidden ? "display:none;" : "", $content);
    }
}

?>
