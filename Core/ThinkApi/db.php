<?php
class db{
	
	static public function checkDb($db){
        if($db == 'mysql'){
        	require_once './Core/Db/Mysql.php';
        	$config = self::analyze();
        	$mysql = new Mysql($config);
        	return $mysql;
        }
	}
	
	static public function analyze(){

		include './Conf/config.php';
        return $config['db']['mysql'];
	}
}