<?php
class Document_Model_Names extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_names";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_rowClass = "Document_Model_Row_Name";
	
	protected $_rowsetClass = "Document_Model_Rowset_Names";
}