<?php
defined('SAFE')or die();
class Config{
		
		private  $host = "localhost";
		private  $Dbuser = "root";
		private  $Dbpass = "";
		private  $Db = "aramide";
		private  $Dbport = '';
	
	function __get($name){
		return $this->{$name};
	}
	function get(){
		return new config;
	}
}
?>