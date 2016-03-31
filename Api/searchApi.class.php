<?php
class searchApi extends commonApi{
	
	public function __construct(){

	    //$this->load('abc',array('jkl','dfg'));
		//$this->db = 
	}
    
	/**
	 * 搜索商品
	 * Enter description here ...
	 */
    public function index(){
    	$keyword = $_GET['keyword'];
    	$this->request('get.keyword');
    	$db = $this->db();
    	$db->field('*');
    	
    	$where = "`title` like '%".$keyword."%' or `keyword` like '%".$keyword."%'";
    	
        $limit = 8;
        if(isset($_GET['limit'])){
        	$limit = $_GET['limit'];
        	
        	if(!empty($_GET['page'])){
                $db->limit($limit*($_GET['page']-1).','.$limit);
        	}else{
        		$db->limit($limit);
        	}

        }

        if(!empty($_GET['page']) && empty($_GET['limit'])){
                $db->limit($limit*($_GET['page']-1).','.$limit);
        }
    	
    	$row = $db->table('goods')->where($where)->select();

        $data = array();
    	foreach ($row as $key => $value) {
    		$goods['id'] = $value['id'];
    		$goods['title'] = $value['title'];
    		$goods['type_id'] = $value['type_id'];
    		$goods['status'] = $value['status'];
    		$goods['amount'] = $value['amount'];
    		$goods['partake'] = $value['partake'];
    		$goods['left'] = (int)$value['amount'] - (int)$value['partake'];
    		$goods['number'] = $value['number'];
    		$goods['thumb'] = self::$config['image_url'].$value['thumb'];
    		if($value['amount'] == 0){
    			$goods['schedule'] = 0;
    		}else{
    			$goods['schedule']  = sprintf('%.2f',$value['partake'] / $value['amount']) * 100;
    		}
    		$data[] = $goods;
    	}
    	return $data;

    }
    
    public function tags(){
    	
    	$db = $this->db();
    	
    	if(!empty($_GET['order'])){
    		if($_GET['order'] == 'hot')
    		$db->order('hot DESC');
    	}
    	
        $limit = 5;
        if(isset($_GET['limit'])){
        	$limit = $_GET['limit'];
        	
        	if(!empty($_GET['page'])){
                $db->limit($limit*($_GET['page']-1).','.$limit);
        	}else{
        		$db->limit($limit);
        	}

        }

        if(!empty($_GET['page']) && empty($_GET['limit'])){
                $db->limit($limit*($_GET['page']-1).','.$limit);
        }
    	
    	$tags = $db->table('tags')->select();
    	return $tags;
    }

}