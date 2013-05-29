<?php
class Document_Model_DirectoriesFiles extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_directories_files";
	
	protected $_sequence = false;
	
	protected $_primary = array("directory_id", "file_id");
	
	protected $_referenceMap = array(
			"file" => array(
					"columns" => "file_id",
					"refTableClass" => "Document_Model_Files",
					"refColumns" => "id"
			),
			
			"directory" => array(
					"columns" => "directory_id",
					"refTableClass" => "Document_Model_Directories",
					"refColumns" => "id"
			)
	);
}