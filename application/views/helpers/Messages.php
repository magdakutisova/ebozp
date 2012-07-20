<?php
class Zend_View_Helper_Messages extends Zend_View_Helper_Abstract {
    public function messages() {

        $messages = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages();

        $text  = '';

        if (count($messages) > 0) { 
            $text .= '<div class="messages">';
            foreach ($messages as $message) {
                $text .= '<div class="message">';
                $text .= '<p>' 
                            . $message 
                       . '</p>';
                $text .= '</div>';
            } 
            $text .= '</div>';
        }

        return $text;
    }

}