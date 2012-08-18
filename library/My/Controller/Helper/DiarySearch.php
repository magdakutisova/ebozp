<?php
class My_Controller_Helper_DiarySearch extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct($query) {
		
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$results = $index->find ( $query, 'date', SORT_REGULAR, SORT_DESC );		
	
		
		$messages = array ();
		$df = new My_Controller_Helper_DiaryFiltering();
		if ($results) {
			foreach ( $results as $result ) {
				if ($result->type == 'diary') {
					$record = array ();
					$record ['id_diary'] = $result->diaryId;
					$record ['date'] = $result->date;
					$record ['message'] = $result->message;
					$record ['subsidiary_id'] = $result->subsidiaryId;
					$record ['author'] = $result->author;
					$message = new Application_Model_Diary ( $record );
					$messages [] = $message;
				}
			}
			$df->direct ( $messages, 0, 0 );
		} else {
			$df->direct ( $messages, 0, 0 );
		}
	}

}