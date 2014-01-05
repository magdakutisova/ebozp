<?php
class Document_Model_RecordsNames extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_records_names";
	
	protected $_primary = "id";
	
	protected $_sequence = true;
	
	protected $_rowClass = "Document_Model_Row_RecordName";
	
	protected $_rowsetClass = "Document_Model_Rowset_RecordsNames";
}