<?php
/**
+----------------------------------------------------------
 * Mysql操作类
+----------------------------------------------------------
 * 文件名称  mysql.php
+----------------------------------------------------------
 * 文件描述  mysql操作类
+----------------------------------------------------------
 */
final class Mysql{
    //数据库连接标识
    protected $link = null;
    //当前操作的表
    public $table = '';
    //查询参数
    protected $options = array();
    //当前执行的SQL语句
    public $sql = '';
    //用什么编码传递数据
    protected $dbCharset = 'utf8';

    //缓存路径
    protected $cachePath = './Cache/';
    //缓存扩展名
    protected $cacheFileExt = "php";
    //缓存文件名
    protected $cacheFileName;
    //是否缓存
    protected $cache = false;
    //缓存更新时间秒数
    protected $cacheLimitTime = 60;

    //数据返回类型, 1代表数组, 2代表对象
    protected $returnType = 1;

    protected $prefix = '';
    
    public function __construct($config){
    	$this->connect($config);
    }

  /*
   * 根据当前动态文件生成缓存文件名
   */
	function setCacheFileName($fileName) {
		$cacheFileName = $this->cachePath . strtoupper(md5($fileName)).".".$this->cacheFileExt;
		$this->cacheFileName=$cacheFileName;
    }
  /*
   * 根据当前动态文件生成缓存文件名
   */
	function getCacheFileName() {
		return  $this->cacheFileName;
    }
    /**
     * 连接数据库
     *
     * @access      public
     * @param       array    $db  数据库配置
     * @return      resource 数据库连接标识
     */
    public function connect($db){
        //根据配置使用不同函数连接数据库
        $db['host'] = isset($db['port']) ? $db['host'].':'.$db['port']: $db['host'];
        $db['char'] = isset($db['char']) ? $db['char']: $this->dbCharset;
        $this->prefix = isset($db['prefix']) ? $db['prefix']: $this->prefix;
        $func = $db['pconnect'] ? 'mysql_pconnect' : 'mysql_connect';
        $this->link = $func($db['host'], $db['user'], $db['pwd']);
        mysql_select_db($db['database'], $this->link);
        mysql_query("SET NAMES '{$db['char']}'");
        $this->cachePath = isset($db['cachepath']) ? $db['cachepath']: $this->cachePath;
        return $this->link;
    }
    /**
     * 查询符合条件的一条记录
     *
     * @access      public
     * @param       string    $where  查询条件
     * @param       string    $field  查询字段
     * @param       string    $table  表
     * @return      mixed             符合条件的记录
     */
    public function find($where = NULL, $field = '*', $table = ''){
        return $this->select($where = NULL, $field = '*', $table = '', FALSE);
    }
    /**
     * 查询符合条件的所有记录
     *
     * @access      public
     * @param       string    $where  查询条件
     * @param       string    $field  查询字段
     * @param       string    $table  表
     * @return      mixed             符合条件的记录
     */
    public function select($where = NULL, $field = '*', $table = '', $all = TRUE){
        $this->options['field'] = isset($this->options['field']) ? $this->options['field']: $field;
        $this->options['table'] = $table == '' ? $this->table: $table;
        $sql = "SELECT {$this->options['field']} FROM `{$this->options['table']}` ";
        $sql .= isset($this->options['join']) ? ' LEFT JOIN '.$this->options['join']: '';
        $sql .= isset($this->options['where']) ? ' WHERE '.$this->options['where']: '';
        $sql .= isset($this->options['group']) ? ' GROUP BY '.$this->options['group']: '';
        $sql .= isset($this->options['having']) ? ' HAVING '.$this->options['having']: '';
        $sql .= isset($this->options['order']) ? ' ORDER BY '.$this->options['order']: '';
        $sql .= isset($this->options['limit']) ? ' LIMIT '.$this->options['limit']: '';
        $this->sql = $sql; 
        $row = NULL;
        //如果开启了缓存, 那么重缓存中获取数据
        if ($this->cache === TRUE){
			$this->setCacheFileName($this->sql);
            $row = $this->readCache();
        }
        //如果读取失败, 或则没有开启缓存
        if (is_null($row)){
            $result = $this->query();
            $row = $all === TRUE ? $this->fetchAll($result): $this->fetch($result);
            //如果开启了缓存, 那么就写入
            if ($this->cache === TRUE){
                $this->writeCache($row);
            }
            $this->options = array();
        }
        return $row;
    }
    /**
     * 读取结果集中的所有记录到数组中
     *
     * @access public
     * @param  resource  $result  结果集
     * @return array
     */
    public function fetchAll($result = NULL){
        $rows = array();
        while ($row = $this->fetch($result)){
            $rows[] = $row;
        }
        return $rows;
    }
    /**
     * 读取结果集中的一行记录到数组中
     *
     * @access public
     * @param  resource  $result  结果集
     * @param  int       $type    返回类型, 1为数组, 2为对象
     * @return mixed              根据返回类型返回
     */
    public function fetch($result = NULL, $type = NULL){
        $result = is_null($result) ? $this->result: $result;
        $type = is_null($type) ? $this->returnType: $type;
        $func = $type === 1 ? 'mysql_fetch_assoc' : 'mysql_fetch_object';
        return $func($result);
    }
    /**
     * 执行SQL命令
     *
     * @access      public
     * @param       string    $sql    SQL命令
     * @param       resource  $link   数据库连接标识
     * @return      mixed             数据库结果集
     */
    public function query($sql = '', $link = NULL){
        $sql = empty($sql) ? $this->sql: $sql;
        $link = is_null($link) ? $this->link: $link;
        $this->result = mysql_query($sql, $link);
        if (is_resource($this->result)){
            return $this->result;
        }
        //如果执行SQL出现错误, 那么抛出异常
        exit('<strong>Mysql error:</strong>'.$this->getError());
    }
    /**
     * 执行SQL命令
     *
     * @access      public
     * @param       string    $sql    SQL命令
     * @param       resource  $link   数据库连接标识
     * @return      bool              是否执行成功
     */
    public function execute($sql = '', $link = NULL){
        $sql = empty($sql) ? $this->sql: $sql;
        $link = is_null($link) ? $this->link: $link;
        if (mysql_query($sql, $link)){
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 插入记录
     *
     * @access public
     * @param  array  $data  插入的记录, 格式:array('字段名'=>'值', '字段名'=>'值');
     * @param  string $table 表名
     * @return bool          当前记录id
     */
    public function add($data, $table = NULL){
        $table = is_null($table) ? $this->table: $table;
        $sql = "INSERT INTO `{$table}`";
        $fields = $values = array();
        $field = $value = '';
        //遍历记录, 格式化字段名称与值
        foreach($data as $key => $val){
            $fields[] = "`{$table}`.`{$key}`";
            $values[] = is_numeric($val) ? $val : "'{$val}'";
        }
        $field = join(',', $fields);
        $value = join(',', $values);
        unset($fields, $values);
        $sql .= "({$field}) VALUES({$value})";
        $this->sql = $sql;
        $this->execute();
        return $this->insertId();
    }
    /**
     * 删除记录
     *
     * @access public
     * @param  string  $where  条件
     * @param  string  $table  表名
     * @return bool            影响行数
     */
    public function delete($where = NULL, $table = NULL){
        $table = is_null($table) ? $this->table: $table;
        $where = is_null($where) ? @$this->options['where']: $where;
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $this->sql = $sql;
        $this->execute();
        return $this->affectedRows();
    }
    /**
     * 更新记录
     *
     * @access public
     * @param  array   $data   更新的数据 格式:array('字段名' => 值);
     * @param  string  $where  更新条件
     * @param  string  $table  表名
     * @return bool            影响多少条信息
     */
    public function update($data, $where = NULL, $table = NULL){
        $table = is_null($table) ? $this->table: $table;
        $where = is_null($where) ? @$this->options['where']: $where;
        $sql = "UPDATE `{$table}` SET ";
        $values = array();
        foreach($data as $key => $val){
            $val = is_numeric($val) ? $val : "'{$val}'";
            $values[] = "`{$table}`.`{$key}` = {$val}";
        }
        $value = join(',', $values);
        $this->sql = $sql.$value." WHERE {$where}";
        $this->execute();
        return $this->affectedRows();
    }
    
    public function plus($field,$num = 1,$where = NULL, $table = NULL){
    	$table = is_null($table) ? $this->table: $table;
        $where = is_null($where) ? @$this->options['where']: $where;
        $sql = "UPDATE `{$table}` SET `{$field}` = `{$field}` + $num";
        $this->sql = $sql." WHERE {$where}";
        $this->execute();
        return $this->affectedRows();
    }
    
    
    public function subtract($field,$num = 1,$where = NULL, $table = NULL){
    	$table = is_null($table) ? $this->table: $table;
        $where = is_null($where) ? @$this->options['where']: $where;
        $sql = "UPDATE `{$table}` SET `{$field}` = `{$field}` - $num";
        $this->sql = $sql." WHERE {$where}";
        $this->execute();
        return $this->affectedRows();
    }
    
    /**
     * 读取缓存
     *
     * @access      public
     * @return      mixed   如果读取成功返回缓存内容, 否则返回NULL
     */
    protected function readCache(){
        $file = $this->getCacheFileName();
        if (file_exists($file)){
            //缓存过期
            if ((filemtime($file) + $this->cacheLimitTime) < time()){
                @unlink($file);
                return NULL;
            }
            if (1 === $this->returnType){
                $row = include $file;
            }
            else{
                $data = file_get_contents($file);
                $row = unserialize($data);
            }
            return $row;
        }
        return NULL;
    }
    /**
     * 写入缓存
     *
     * @access      public
     * @param       mixed   $data   缓存内容
     * @return      bool            是否写入成功
     */
    public function writeCache($data){
        $file = $this->getCacheFileName();
        if ($this->makeDir(dirname($file))){
            if (1 === $this->returnType){
				$data = '<?php return '.var_export($data, TRUE).';?>';
			}else{
				$data = serialize($data);
			}
		}
        return file_put_contents($file, $data);
    }
	/*
	 * 清除缓存文件
	 * string $fileName 指定文件名(含函数)或者all（全部）
	 * 返回：清除成功返回true，反之返回false
	 */
	function clearCache( $fileName = "all" ) {
		if( $fileName != "all" ) {
			if( file_exists( $fileName ) ) {
				return @unlink( $fileName );
			}else return false;
		}
		if ( is_dir( $this->cachePath ) ) {
			if ( $dir = @opendir( $this->cachePath ) ) {
				while ( $file = @readdir( $dir ) ) {
					$check = is_dir( $file );
					if ( !$check )
					@unlink( $this->cachePath . $file );
				}
				@closedir( $dir );
				return true;
			}else{
				return false;
			}
		}else{
		  return false;
		}
	}
	  /*
	   * 连续建目录
	   * string $dir 目录字符串
	   * int $mode   权限数字
	   * 返回：顺利创建或者全部已建返回true，其它方式返回false
	   */
	function makeDir( $dir, $mode = "0777" ) {
		if( ! $dir ) return 0;
		$dir = str_replace( "\\", "/", $dir );
		
		$mdir = "";
		foreach( explode( "/", $dir ) as $val ) {
			$mdir .= $val."/";
			if( $val == ".." || $val == "." || trim( $val ) == "" ) continue;
		  
			if( ! file_exists( $mdir ) ) {
				if(!@mkdir( $mdir, $mode )){
					return false;
				}
			}
		}
		return true;
	}
	//自动加载函数, 实现特殊操作
    public function __call($func, $args)
    {
         if(in_array($func, array('field', 'join', 'where', 'order', 'group', 'limit', 'having'))){
         	   if($func == 'where' && is_array($args[0])){
         	   	  if(empty($args[0])) goto x;
         	   	  $this->options['where'] = '';
         	   	  $and = ' and ';
         	   	  $or = '';
         	   	  foreach ($args[0] as $key => $val){
         	   	  	  if(is_array($args[0][$key]) && $args[0][$key][0] == 'or'){
         	   	  	  	  $or = ' or '.$key.' = '.$val[1];
         	   	  	  }else{
         	   	  	  	  $this->options['where'] .= '`'.$key.'` = '.$val.$and;    
         	   	  	  }  	  	          	   	  	    	   	  	  
         	   	  }
         	   	  $this->options['where'] =  substr($this->options['where'],0,strlen($this->options['where'])-5).$or;
         	   }else{
         	   	  $this->options[$func] = array_shift($args);
         	   } 
         	   x:
               return $this;
         } elseif($func === 'table'){
               $this->options['table'] = array_shift($args);
               $this->table            = $this->prefix.$this->options['table'];
               return $this;
         }
         
        //如果函数不存在, 则抛出异常
         exit('Call to undefined method Db::' . $func . '()');
     }
//-------------------------------------------------------------------------------
        //返回上一次操作所影响的行数
    public function affectedRows($link = null){
		$link = is_null($link) ? $this->link : $link;
		return mysql_affected_rows($link);
    }

        //返回上一次操作记录的id
    public function insertId($link = null){
        $link = is_null($link) ? $this->link : $link;
        return mysql_insert_id($link);
    }

        //清空结果集
    public function free($result = null){
         $result = is_null($result) ? $this->result : $result;
         return mysql_free_result($result);
    }

        //返回错误信息
	public function getError($link = NULL){
        $link = is_null($link) ? $this->link : $link;
        return mysql_error($link);
    }

        //返回错误编号
	public function getErrno($link = NULL){
        $link = is_null($link) ? $this->link : $link;
        return mysql_errno($link);
    }
}