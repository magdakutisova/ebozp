<?php
class Document_Model_Versions extends Zend_Db_Table_Abstract {
	
	protected $_name = "document_versions";
	
	protected $_sequence = true;
	
	protected $_primary = "id";
	
	protected $_referenceMap = array(
			"file" => array(
					"columns" => "file_id",
					"refTableClass" => "Document_Model_Files",
					"refColumns" => "id"
			)
	);
	
	protected $_rowClass = "Document_Model_Row_Version";
	
	protected $_rowsetClas = "Document_Model_Rowset_Versions";
	
	/**
	 * vytvori zaznam v databzi o verzi
	 * presune soubor do uloziste
	 * 
	 * @param Document_Model_Row_File $file radek souboru
	 * @param string $path cesta k docasnemu souboru
	 * @param string $mime mime typ
	 * @return Document_Model_Row_Version
	 * @throws Zend_File_Transfer_Exception
	 */
	public function createVersionFormFile($file, $path, $mime) {
		// kontrola, jestli cil existuje
		if (!is_file($path)) throw new Zend_File_Transfer_Exception("File not found in $path");
		
		// vytvoreni a zapis verze
		$retVal = $this->createRow(array(
				"file_id" => $file->id,
				"mime" => $mime,
				"size" => filesize($path)
		));
		
		$retVal->save();
		
		// presun dat
		copy($path, DOCUMENT_PATH_DIR . "/revision_" . $retVal->id . ".dat");
		
		return $retVal;
	}
	
	/**
	 * vytvori soubor z retezce
	 * 
	 * @param Document_Model_Row_File $file radek souboru
	 * @param string $content obsah souboru
	 * @param string $mime mimetyp
	 * @return Document_Model_Row_Version
	 */
	public function createVersionFromString($file, $content, $mime) {
		// vytvoreni a zapis verze
		$retVal = $this->createRow(array(
				"file_id" => $file->id,
				"mime" => $mime,
				"size" => strlen($content)
		));
		
		$retVal->save();
		
		// zapis souboru
		file_put_contents(DOCUMENT_PATH_DIR . "/revision_" . $retVal->id . ".dat", $content);
		
		return $retVal;
	}
}