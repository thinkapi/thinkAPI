<?php
class advertApi extends commonApi{

    public function index(){
        $db = $this->db();
    	$db->field('title,images,link');
    	$limit = '8';
        $map = array();
        if(isset($_GET['limit'])){
        	$limit = $_GET['limit'];       	
        	if(!empty($_GET['page'])){
                $db->limit($limit*($_GET['page']-1),$limit);
        	}else{
        		$db->limit($limit);
        	}
        }
      
        if(!empty($_GET['id'])){
        	$map['id'] = $_GET['id'];
        }else{
        	$db->limit($limit);
        }

    	$db->where($map);
    	$data = $db->table('advert')->order('id DESC')->select();

    	return $data;
    }

}