<?php 
class route{
	
	public $url,$api,$action;
	
	public function __construct($url){
		$this->url = $url;
	}
	
	/**
	 * 解析路由
	 * Enter description here ...
	 */
	public function analyze(){
		$this->safe();
		$route = explode('/', $this->url);
		$this->api = $route[0];
		$this->action = $route[1];
		if(empty($this->api)){
			$eorre = '';
		}
		if(empty($this->action)){
			$eorre = '';
		}
		return array(
		    'api'  => $this->api,
		    'action' => $this->action
		);
	}
	
    private function safe(){
    	$str = substr( $this->url, 0, 1 );
    	if($str == '/'){
            $this->url = substr($this->url,1);
    	}
    }

    public function __destruct(){
    	//unset($_GET['i']);
    }

}