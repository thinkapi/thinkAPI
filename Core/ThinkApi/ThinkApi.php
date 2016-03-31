<?php
class ThinkApi {
    
	static $config = array();
	static $code = 1000;
	
	public function __construct(){
        header('Content-type:text/json; charset=utf-8'); 
	}
	
    /**
     * 载入类文件并实例化
     * @param string $className
     */
    public function load($className,$args){
    	$cs = implode(',', $args);
    	echo $cs;
    	return new $className($cs);
    }
    
    /**
     * 路由调度
     * Enter description here ...
     * @param unknown_type $url
     */
    static private function rote($url){
    	require 'route.php';
    	$route = new route($url);
    	$route = $route->analyze();
    	$action = $route['action'];
    	
    	function __autoload($className){
    		require_once './Api/'.$className.'.class.php';
    	}
    	    	
    	$className = $route['api'].'Api';

    	$api = new $className();
    	
    	echo trim(json_encode(self::data($api->$action()),JSON_UNESCAPED_UNICODE), "\xEF\xBB\xBF");
    }

    static private function data($list){
    	$data = array();
    	$data['code'] = 1000;
        $data['massage'] = '请求成功';
        $data['data'] = $list;
        return $data;
    }
    
    
    /**
     * 程序开始
     * Enter description here ...
     */
    static public function start() {
    	if(!isset($_GET['TOKEN']) && empty($_GET['TOKEN'])){
    		exit(json_encode(array(
    			'code' => 20001,
    			'massage'  => '参数错误',
    			'data' => ''
    		),JSON_UNESCAPED_UNICODE));
    	}
    	
    	!empty($_GET['i']) && $i = $_GET['i'];
    	if(empty($_GET['i']) || empty($_GET)){
    		$i = 'index/index';
    	}
    	
    	self::check($_GET['TOKEN']);
    	self::rote($i);
    }
    
    /**
     * 实例化DB
     * Enter description here ...
     */
    public function db(){
    	require_once 'db.php';
    	return db::checkDb('mysql');
    }
    
    static private function config(){
    	require_once './Conf/config.php';
    	self::$config = $config;
    	return self::$config;
    }
    
    /**
     * 读取配置值
     * Enter description here ...
     * @param $token
     */
    static private function check($token){
    	$config = self::config();
    	if(md5($config['key']) !== $token){   		
    		echo json_encode(array(
    			'code' => 20001,
    			'massage'  => '参数错误',
    			'data' => ''
    		),JSON_UNESCAPED_UNICODE);
    		exit();
    	}
    }
    
    /**
     * 自动载入
     * Enter description here ...
     */
    static private function autoload(){
        //   function __autoload($className){
    	// 	require_once './Api/'.$className.'.class.php';
    	// }
    }
    
    /**
     * 安全过滤
     * Enter description here ...
     * @param unknown_type $request
     */
    protected function request($request){
    	require_once './Core/ThinkApi/safe.php';
    	$safe = new safe();
    	$req = explode('.', $request);
    	switch ($req[0]){
    		case 'post' :
    			isset($_POST[$req[1]]) ? 
    			$x = $_POST[$req[1]]
    			: exit(json_encode(array('code'=>2001,'message'=>'参数错误','data' => array()) ));
    			$check = true;
    		break;
    		case 'get' :
    			isset($_GET[$req[1]]) ? 
    			$check = $safe->inject_check($_GET[$req[1]])
    			: exit(json_encode(array('code'=>2001,'message'=>'参数错误','data' => array()) ));
    			$x = $_GET[$req[1]];
    		break;
    	}
    	if($check == true){
    		echo json_encode(array('code'=>3001,'message'=>'系统错误','data' => array()) );
    		exit();
    	}else{
    		return $x;
    	}
    }
    
    /**
     * 密码加密
     */
    public function password($pwd){
    	return sha1(substr(md5($pwd.self::$config['pwdKey']),0,20).self::$config['pwdKey']);
    } 
}