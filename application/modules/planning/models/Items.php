<?php

class Planning_Model_Items extends Zend_Db_Table_Abstract {

    protected $_name = "planning_items";

    protected $_sequence = true;

    protected $_primary = array("id");

    protected $_referenceMap = array(
        "user" => array(
            "columns" => "user_id",
            "refTableClass" => "Application_Model_DbTable_User",
            "refColumns" => array("id_user")
            ),
        "creator" => array(
            "columns" => "created_by",
            "refTableClass" => "Application_Model_DbTable_User",
            "refColumns" => "id_user"));
}