<?php

class Application_Model_DbTable_Diary extends Zend_Db_Table_Abstract
{

    protected $_name = 'diary';

    protected $_referenceMap = array(
    	'Subsidiary' => array(
    		'columns' => 'subsidiary_id',
    		'refTableClass' => 'Application_Model_DbTable_Subsidiary',
    		'refColumns' => 'id_subsidiary'
    	),
    );
    
    public function getMessage($id){
    	$id = (int)$id;
    	$row = $this->fetchRow('id_diary = ' . $id);
    	if (!$row){
    		throw new Exception("Záznam č. $id neexistuje");
    	}
    	return new Application_Model_Diary($row->toArray());
    }
    
    public function addMessage($record){
    	$data = $record->toArray();
    	$inserted = $this->insert($data);
    	$record = $this->getMessage($inserted);
    	
    	//indexace pro vyhledávání
		try {
			$index = Zend_Search_Lucene::open ( APPLICATION_PATH . '/searchIndex' );
		} catch ( Zend_Search_Lucene_Exception $e ) {
			$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		}
		
		$document = new Zend_Search_Lucene_Document();
		$document->addField(Zend_Search_Lucene_Field::unIndexed('diaryId', $record->getIdDiary(), 'utf-8'));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('date', $record->getDate(), 'utf-8'));
		$document->addField(Zend_Search_Lucene_Field::text('message', $record->getMessage(), 'utf-8'));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('subsidiaryId', $record->getSubsidiaryId(), 'utf-8'));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('author', $record->getAuthor(), 'utf-8'));
		$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'diary', 'utf-8' ) );
		$index->addDocument($document);
		
		$index->commit ();
		$index->optimize ();
    }
    
    //public function updateMessage($diary){
    	//$data = $diary->toArray();
    	//$this->update($data, 'id_diary = ' . $diary->getIdDiary());
    //}
    
    //public function deleteMessage($id){
    	//$this->delete('id_diary = ' . (int)$id);
    //}
    
    public function getDiary(){
    	$select = $this->select()->from('diary')->order('date DESC');
    	$result = $this->fetchAll($select);
    	return $this->process($result);
    }
    
    public function getDiaryLastMonths($count){
    	$select = $this->select()
    		->from('diary')
    		->where('date BETWEEN NOW() - INTERVAL ? MONTH AND NOW()', $count)
    		->order('date DESC');
    	$result = $this->fetchAll($select);
    	return $this->process($result);
    }
    
    public function getDiaryByClient($id){
    	$select = $this->select()
    		->from('diary')	
    		->columns(array('date', 'message'))	
    		->join('subsidiary', 'subsidiary.id_subsidiary = diary.subsidiary_id')
    		->where('client_id = ?', $id)
  			->order('date DESC');
    	$select->setIntegrityCheck(false);
    	$result = $this->fetchAll($select);
    	return $this->process($result);
    }
    
	public function getDiaryBySubsidiary($id){
    	$select = $this->select()
    		->from('diary')	
    		->columns(array('date', 'message'))	
    		->where('subsidiary_id = ?', $id)
  			->order('date DESC');
    	$result = $this->fetchAll($select);
    	return $this->process($result);
    }
    
    private function process($result){
    	if ($result->count()){
			$diary = array();
			foreach($result as $record){
				$record = $result->current();
				$diary[] = $this->processRecord($record);
			}
			return $diary;
		}
    }
    
    private function processRecord($record){
    	$data = $record->toArray();
		return new Application_Model_Diary($data);
    }

}

