<?php
class My_FileLogger{
	
	protected $logger;
	static $fileLogger = null;
	
	protected function __construct(){
		$this->logger = Zend_Registry::get('log');
	}
	
	public static function info($message){
		self::getInstance()->getLog()->info($message);
	}
	
	public static function getInstance(){
		if(self::$fileLogger === null){
			self::$fileLogger = new self();
		}
		return self::$fileLogger;
	}
	
	public function getLog(){
		return $this->logger;
	}
	
}