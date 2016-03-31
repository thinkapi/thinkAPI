<?php 
   
/*  
 *内存缓存管理  
 */ 
class Mem{  

   private $memcache = null;   
   
   public function __construct(){  
   }  

   /**  
    * 连接数据库  
    *  
    * @param mixed $host  
    * @param mixed $port  
    * @param mixed $timeout  
    */ 
    public  function connect($host,$port=11211,$timeout=1){  
        if(!function_exists(memcache_connect)){
            return FALSE;
        }  
        $this->memcache=@memcache_connect($host,$port,$timeout);  
        if(emptyempty($this->memcache)){  
            return FALSE;  
        }else{  
            return TRUE;  
        }  
    }  

    /**  
     * 存放值  
     *  
     * @param mixed $key  
     * @param mixed $var  
     * @param mixed $flag   默认为0不压缩  压缩状态填写：MEMCACHE_COMPRESSED  
     * @param mixed $expire  默认缓存时间(单位秒)  
     */ 
    public function set($key,$var,$flag=0,$expire=10){  
        
       $f=@memcache_set($this->memcache,$key,$var,$flag,$expire);  
       if(emptyempty($f)){  
           return FALSE;  
       }else{  
           return TRUE;  
       }  
    }  


    /**  
     * 取出对应的key的value  
     *  
     * @param mixed $key  
     * @param mixed $flags  
     * $flags 如果此值为1表示经过序列化，  
     * 但未经过压缩，2表明压缩而未序列化，  
     * 3表明压缩并且序列化，0表明未经过压缩和序列化  
     */ 
    public function get($key,$flags=0){  
        $val=@memcache_get($this->memcache,$key,$flags);  
        return $val;  
    }  

    /**  
     * 删除缓存的key  
     *  
     * @param mixed $key  
     * @param mixed $timeout  
     */ 
    public function delete($key,$timeout=1){  
        $flag=@memcache_delete($this->memcache,$key,$timeout);  
        return $flag;  
    } 

    /**  
     * 刷新缓存但不释放内存空间  
     *  
     */ 
    public function flush(){  
        memcache_flush($this->memcache);  
    } 

    /**  
     * 关闭内存连接  
     *  
     */ 
    public function close(){  
        memcache_close($this->memcache);  
    } 


    /**  
     * 替换对应key的value  
     *  
     * @param mixed $key  
     * @param mixed $var  
     * @param mixed $flag  
     * @param mixed $expire  
     */ 
    public function replace($key,$var,$flag=0,$expire=1){  
        $f=memcache_replace($this->memcache,$key,$var,$flag,$expire);  
        return $f;  
    }  

    /**  
     * 开启大值自动压缩  
     *  
     * @param mixed $threshold 单位b  
     * @param mixed $min_saveings 默认值是0.2表示20%压缩率  
     */ 
    public function setCompressThreshold($threshold,$min_saveings=0.2){  
        $f=@memcache_set_compress_threshold($this->memcache,$threshold,$min_saveings);  
        return $f;  
    }  


    /**  
     * 用于获取一个服务器的在线/离线状态  
     *  
     * @param mixed $host  
     * @param mixed $port  
     */ 
    public function getServerStatus($host,$port=11211){  
        $re=memcache_get_server_status($this->memcache,$host,$port);  
        return $re;  
    }  

    /**  
     * 缓存服务器池中所有服务器统计信息  
     *  
     * @param mixed $type 期望抓取的统计信息类型，可以使用的值有{reset, malloc, maps, cachedump, slabs, items, sizes}  
     * @param mixed $slabid  cachedump命令会完全占用服务器通常用于 比较严格的调  
     * @param mixed $limit 从服务端获取的实体条数  
     */ 
    public function getExtendedStats($type='',$slabid=0,$limit=100){  
        $re=memcache_get_extended_stats($this->memcache,$type,$slabid,$limit); 
        return $re;  
    }  
}


/***********测试区域********************/ 
//$mem=new Mem();  
   
//$f=$mem->connect('125.64.41.138',12000);  
//var_dump($f);  
//if($f){  
// $mem->setCompressThreshold(2000,0.2);  
//$mem->set('key','hello',0,30);  
//        var_dump($mem->delete('key1'));  
// $mem->flush();  
// var_dump($mem->replace('hao','d'));  
// echo $mem->get('key');  
//echo $mem->getServerStatus('127.0.0.1',12000);  
//echo $mem->get('key');  
// echo '<pre>';  
// print_r($mem->getExtendedStats());