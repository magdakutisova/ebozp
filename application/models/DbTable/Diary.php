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
    	return $row->toArray();
    }
    
    public function addMessage($message, $subsidiaryId, $author){
    	$data = array(
    		'message' => $message,
    		'subsidiary_id' => $subsidiaryId,
    		'author' => $author,
    	);
    	$this->insert($data);
    }
    
    public function updateMessage($id, $message, $subsidiaryId, $author){
    	$data = array(
    		'message' => $message,
    		'subsidiary_id' => $subsidiaryId,
    		'author' => $author,
    	);
    	$this->update($data, 'id_diary = ' . (int)$id);
    }
    
    public function deleteMessage($id){
    	$this->delete('id_diary = ' . (int)$id);
    }
    
    public function getDiary(){
    	$select = $this->select()->from('diary')->order('date DESC');
    	return $this->fetchAll($select);
    }
    
    public function getDiaryByClient($id){
    	$select = $this->select()
    		->from('diary')	
    		->columns(array('date', 'message'))	
    		->join('subsidiary', 'subsidiary.id_subsidiary = diary.subsidiary_id')
    		->where('client_id = ?', $id)
  			->order('date DESC');
    	$select->setIntegrityCheck(false);
    	return $this->fetchAll($select);
    }

}

