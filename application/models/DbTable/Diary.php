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
    	if (!row){
    		throw new Exception("Záznam č. $id neexistuje");
    	}
    	return new Application_Model_Diary($row->toArray());
    }
    
    public function addMessage($diary){
    	$data = $diary->toArray();
    	$this->insert($data);
    }
    
    public function updateMessage($diary){
    	$data = $diary->toArray();
    	$this->update($data, 'id_diary = ' . $diary->getIdDiary());
    }
    
    public function deleteMessage($id){
    	$this->delete('id_diary = ' . (int)$id);
    }
    
    public function getDiary(){
    	$select = $this->select()->from('diary')->order('date DESC');
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

