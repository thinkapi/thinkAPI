<?php
/**
 * 商品处理 
 * Enter description here ...
 * @author Cherish
 *
 */
class goodsApi extends commonApi{

	/**
	 * 取得商品列表
	 * Enter description here ...
	 * @access string
	 */
    public function all(){
    	$db = $this->db();
    	$db->field('*');
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
        
        if(!empty($_GET['type_id'])){
        	$map['type_id'] = $_GET['type_id'];
        }

        if(!empty($_GET['cate_id'])){
        	$map['cate_id'] = $_GET['cate_id'];
        }
        
        $map['status'] = 1;

    	$db->where($map);
    	//schedule
    	if(isset($_GET['order'])){
        	if($_GET['order'] == 'count'){
        		$db->order('count DESC');
        	}elseif($_GET['order'] == 'amount'){
        		$db->order('amount DESC');
        	}elseif($_GET['order'] == 'number'){
        		$db->order('number DESC');
        	}elseif($_GET['order'] == 'schedule'){
        		$db->order('(partake / amount) DESC');
        	}
    	}else{
    		$db->order('id DESC');
    	}
    	
    	$row = $db->table('goods')->select();
    	//echo $db->sql;
    	$data = array();
    	foreach ($row as $key => $value) {
    		$goods['id'] = $value['id'];
    		$goods['title'] = $value['title'];
    		$goods['type_id'] = $value['type_id'];
    		$goods['status'] = $value['status'];
    		$goods['amount'] = $value['amount'];
    		
    		if($value['type_id'] == 2){
    			$goods['people'] = $value['amount']/10;
    		}
    		   		
    		$goods['partake'] = $value['partake'];
    		$goods['left'] = (int)$value['amount'] - (int)$value['partake'];
    		$goods['number'] = $value['number'];
    		$goods['thumb'] = ltrim($value['thumb'], ".");
    		if($value['amount'] == 0){
    			$goods['schedule'] = 0;
    		}else{
    			$goods['schedule']  = sprintf('%.2f',$value['partake'] / $value['amount']) * 100;
    		}
    		$data[] = $goods;
    	}
    	return $data;
    }
    
    /**
     * 商品详情
     * Enter description here ...
     */
    public function detail(){

         $goods_id = $this->request('get.id');

         $map['id'] = $goods_id;
         $map['status'] = 1;
         $rel = $this->db()->table('goods')->where($map)->find();

         $data['id'] = $rel['id'];
         $data['title'] = $rel['title'];
         $data['type_id'] = $rel['type_id'];
         $data['amount'] = $rel['amount'];
         
         if($rel['type_id'] == 2){
    			$data['people'] = $rel['people']/10;
    	 }
         
         $data['images'] = $this->images($goods_id);
         $data['number'] = $rel['number'];
         $data['text'] = $rel['text'];
         $data['partake'] = $this->partake($goods_id);
         $data['left'] = (int)$rel['amount'] - (int)$rel['partake'];
         if($rel['amount'] == 0){
    			$data['schedule'] = 0;
    	 }else{
    	 		$data['schedule']  = sprintf('%.2f',$rel['partake'] / $rel['amount']) * 100;
    	 }
    	 $this->set_goods($goods_id);
         return $data;
    }

    private function partake(){

    }
    
    /**
     * 获取商品图片
     * Enter description here ...
     * @param unknown_type $id
     */
    private function images($id){
        return $this->db()->table('goods_images')->field('image_url')->where(array('id'=>$id))->select();
    }
    
    /**
     * 插入历史记录
     * Enter description here ...
     * @param unknown_type $id
     * @param unknown_type $uid
     */
    private function history($id,$uid = 1){
    	$this->db()->table('history')->add(array('uid'=>$uid,'goods_id'=>$id));
    }
    
    /**
     * 记录浏览次数
     * Enter description here ...
     * @param $gid
     */
    private function set_goods($gid){
    	$this->db()->table('goods')->where(array('id'=>$gid))->plus('count',1);
    }
    
    private function create_number(){
    	
    }
    
}