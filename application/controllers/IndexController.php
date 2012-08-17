<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->title = 'Index';
        $this->view->headTitle($this->view->title);
    }

    public function indexAction()
    {          	
    	$diary = new Application_Model_DbTable_Diary();
    	
    	$messages = $diary->getDiary();
    	
    	if ($this->getRequest()->isPost() && in_array('Filtrovat', $this->getRequest()->getPost())){
    		$formData = $this->getRequest()->getPost();
    		$this->_helper->diaryFiltering($messages, $formData['users'], $formData['subsidiaries']);
    	}
    	else{
    		$this->_helper->diaryFiltering($messages, 0, 0);
    	}
    	
    	$this->view->formSearch = new Application_Form_Search();
    	if ($this->getRequest()->isPost() && in_array('Hledat', $this->getRequest()->getPost())){
    		$formData = $this->getRequest()->getPost();
    		$query = $formData['query'];

    		try{
				$index = Zend_Search_Lucene::open(APPLICATION_PATH . '/searchIndex');
			}
			catch (Zend_Search_Lucene_Exception $e){
				$this->$this->_helper->redirector->gotoRoute ( array (), 'searchIndex' );
			}
				
			$results = $index->find($query);
			
			$messages = array();
			if ($results){
				foreach($results as $result){
					if ($result->type == 'diary'){
						$record = array();
						$record['id_diary'] = $result->diaryId;
						$record['date'] = $result->date;
						$record['message'] = $result->message;
						$record['subsidiary_id'] = $result->subsidiaryId;
						$record['author'] = $result->author;
						$message = new Application_Model_Diary($record);
						$messages[] = $message;
					}
				}
				$this->_helper->diaryFiltering($messages, 0, 0);
			}
    	}
    }


}



