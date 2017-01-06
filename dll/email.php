<?php
defined('SAFE')or die('Not allowed');
require_once(__DIR__.'/mailer/mailer.lib.php');

class email extends mailer{

private $sender;
var $subject;
var $recipients;
var $message;
var $flag;
var $attatchment;
private $queue;
private $errors = array();
public static $instance;
public $tasks = array(
'beforeSend',
'AfterSuccess',
);

	public function __construct(){
		self::$instance = $this;
	}

	public function setRecipients(array $to){
		//get the validator
		
		
			//if recipients is already set change to array
				if(is_string($this->recipients)){
					$this->recipients = explode(',',$this->recipients);
				}
				
		foreach($to as $email){
			
				$this->recipients[] = $email;
				
			
		}
		
		return $this;
	}
	
	public function setRecipient($to){
	
		if(is_array($to)){
		return $this->setRecipients($to);
		}else{
			$this->recipients = $to;
			return $this;
			}
	}
	
	public function setmessage($str){
		$this->message = $str;
	}
	
	public function setSubject($str){
		$this->subject = $str;
	}
	
	public function setAttatchment($file){
		if(is_file($file) AND file_exists($file)){
			$file = implode(DELIMITER_DIR,array_filter(explode(DELIMITER_DIR,$file)));
			//Convert to absolute url
			
			$this->attatchment = $file;
		}
	}
	
	private function setFlag(){
	
	}
	
	public function setQueue(){
		$this->queue = $this->recipients;
			$this->recipients = null;
	}
	
	public function setSender($sender = null){
	
	}
	
	public function getQueue(){
	
	}
	
	public function clearQueue(){
	
	}
	public function setError($e){
		$this->errors[] = $e;
	}
	
	public function push($plain=true){
		$to = isset($this->recipients) && !is_null($this->recipients);
			$sender = isset($this->sender);
			
			if($to){
			if(is_array($this->recipients)){
					$this->recipients = array_filter($this->recipients);
					
				foreach($this->recipients as $to){
					if($plain){
				$push = mailer::sendPlain($to,Config::get()->siteEmail,$this->subject,$this->message);
				}else{
				$push = mailer::sendHTML($to,Config::get()->siteEmail,$this->subject,$this->message,false,false,false);
				}
				if($push ==false){
				$this->setError('Cannot send mail to ['.$to.']. Operation failed! ');
					}
				}
				}else{
				$to = $this->recipients;
					if($plain){
				$push = mailer::sendPlain($to,Config::get()->siteEmail,$this->subject,$this->message);
				}else{
				$push = mailer::sendHtml($to,Config::get()->siteEmail,$this->subject,$this->message,false,false,true);
				}
				if($push ==false){
				$this->setError('Cannot send mail to ['.$to.']. Operation failed! ');
					}
				}
			}
			
			if(is_array($this->errors) and count($this->errors)>0){
			//Debug::message(implode('<br>',$this->errors),'error',false);
			}	
				return $push;
	
	}
		


}
?>