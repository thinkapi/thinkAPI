<?php
class safe{

	public function FunStringExist($str){

        //php 批量过滤post,get敏感数据 
        if (get_magic_quotes_gpc()) { 
            $_GET = stripslashes_array($_GET); 
            $_POST = stripslashes_array($_POST); 
        } 

	}


    function stripslashes_array(&$array) {
        while(list($key,$var) = each($array)) { 
            if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) { 
                if (is_string($var)) { 
                    $array[$key] = stripslashes($var); 
                } 
                if (is_array($var)) { 
                    $array[$key] = stripslashes_array($var); 
                } 
            } 
        } 
        return $array; 
    }
    
    function inject_check($sql_str) { 
       return preg_match ( '/select|inert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile/', $sql_str ); 
    }

    //过滤 
    public function htmlencode($str){
        if(empty($str)) return; 
        if($str=="") return $str; 
        $str=trim($str); 
        $str=str_replace("&","&",$str); 
        $str=str_replace(">",">",$str); 
        $str=str_replace("<","<",$str); 
        $str=str_replace(chr(32)," ",$str); 
        $str=str_replace(chr(9)," ",$str); 
        $str=str_replace(chr(9)," ",$str); 
        $str=str_replace(chr(34),"&",$str); 
        $str=str_replace(chr(39),"'",$str); 
        $str=str_replace(chr(13)," ",$str); 
        $str=str_replace("'","''",$str); 
        $str=str_replace("select","select",$str); 
        $str=str_replace("SCRIPT","SCRIPT",$str); 
        $str=str_replace("script","script",$str); 
        $str=str_replace("join","join",$str); 
        $str=str_replace("union","union",$str); 
        $str=str_replace("where","where",$str); 
        $str=str_replace("insert","insert",$str); 
        $str=str_replace("delete","delete",$str); 
        $str=str_replace("update","update",$str); 
        $str=str_replace("like","like",$str); 
        $str=str_replace("drop","drop",$str); 
        $str=str_replace("create","create",$str); 
        $str=str_replace("modify","modify",$str); 
        $str=str_replace("rename","rename",$str); 
        $str=str_replace("alter","alter",$str); 
        $str=str_replace("cast","cas",$str); 
        return $str; 
    }


    //解码 
    public function htmldecode($str){
        if(empty($str)) return; 
        if($str=="") return $str; 
        $str=str_replace("select","select",$str); 
        $str=str_replace("join","join",$str); 
        $str=str_replace("union","union",$str); 
        $str=str_replace("where","where",$str); 
        $str=str_replace("insert","insert",$str); 
        $str=str_replace("delete","delete",$str); 
        $str=str_replace("update","update",$str); 
        $str=str_replace("like","like",$str); 
        $str=str_replace("drop","drop",$str); 
        $str=str_replace("create","create",$str); 
        $str=str_replace("modify","modify",$str); 
        $str=str_replace("rename","rename",$str); 
        $str=str_replace("alter","alter",$str); 
        $str=str_replace("cas","cast",$str); 
        $str=str_replace("&","&",$str); 
        $str=str_replace(">",">",$str); 
        $str=str_replace("<","<",$str); 
        $str=str_replace(" ",chr(32),$str); 
        $str=str_replace(" ",chr(9),$str); 
        $str=str_replace(" ",chr(9),$str); 
        $str=str_replace("&",chr(34),$str); 
        $str=str_replace("'",chr(39),$str); 
        $str=str_replace(" 
        ",chr(13),$str); 
        $str=str_replace("''","'",$str); 
        return $str; 
    }



    // 函数：string_filter($string, $match_type=1) 
    // 功能：过滤非法内容 
    // 参数： 
    // $string 需要检查的字符串 
    // $match_type 匹配类型,1为精确匹配, 2为模糊匹配，默认为1 
    // 
    // 返回：有非法内容返回True，无非法内容返回False 
    // 其他：非法关键字列表保存在txt文件里, 分为普通非法关键字和严重非法关键字两个列表 
    // 
    //====================================================================== 
    public function lib_lawless_string_filter($string, $match_type=1) {
        //字符串空直接返回为非法 
        $string = trim($string); 
        if (empty($string)) { 
            return false; 
        } 
        //获取重要关键字列表和普通关键字列表 
        $common_file = "common_list.txt"; //通用过滤关键字列表 
        $signify_file = "signify_list.txt"; //重要过滤关键字列表 
        //如果任何列表文件不存在直接返回false，否则把两个文件列表读取到两个数组里 
        if (!file_exists($common_file) || !file_exists($signify_file)) { 
            return false; 
        } 
        $common_list = file($common_file);
        $signify_list = file($signify_file); 
        //精确匹配 
        if ($match_type == 1) { 
            $is_lawless = exact_match($string, $common_list); 
        } 
        //模糊匹配 
        if ($match_type == 2) { 
            $is_lawless = blur_match($string, $common_list, $signify_list); 
        } 
        //判断检索结果数组中是否有数据，如果有，证明是非法的 
        if (is_array($is_lawless) && !empty($is_lawless))  { 
            return true; 
        } else{ 
            return false; 
        } 
    }



    //--------------------- 
    // 精确匹配,为过滤服务 
    //--------------------- 
    function exact_match($string, $common_list){
        $string = trim($string); 
        $string = lib_replace_end_tag($string); 
        //检索普通过滤关键字列表 
        foreach($common_list as $block) { 
            $block = trim($block); 
            if (preg_match("/^$string$/i", $block)) { 
                $blist[] = $block; 
            } 
        } 
        //判断有没有过滤内容在数组里 
        if (!empty($blist)){ 
            return array_unique($blist); 
        } 
        return false; 
    }


    //---------------------- 
    // 模糊匹配,为过滤服务 
    //---------------------- 
    public function blur_match($string, $common_list, $signify_list) {
        $string = trim($string); 
        $s_len = strlen($string); 
        $string = lib_replace_end_tag($string); 
        //检索普通过滤关键字列表 
        foreach($common_list as $block) { 
            $block = trim($block); 
            if (preg_match("/^$string$/i", $block)) { 
                $blist[] = $block; 
            } 
        } 
        //检索严重过滤关键字列表 
        foreach($signify_list as $block)  { 
            $block = trim($block); 
            if ($s_len>=strlen($block) && preg_match("/$block/i", $string)) { 
            $blist[] = $block; 
            } 
        } 
        //判断有没有过滤内容在数组里 
        if (!empty($blist)) { 
            return array_unique($blist); 
        } 
        return false; 
    }


    //-------------------------- 
    // 替换HTML尾标签,为过滤服务 
    //-------------------------- 
    public function lib_replace_end_tag($str) { 
    if (empty($str)) return false; 
    $str = htmlspecialchars($str); 
    $str = str_replace( '/', "", $str); 
    $str = str_replace("\\", "", $str); 
    $str = str_replace(">", "", $str); 
    $str = str_replace("<", "", $str); 
    $str = str_replace("", "", $str); 
    $str = str_replace("", "", $str); 
    $str=str_replace("select","select",$str); 
    $str=str_replace("join","join",$str); 
    $str=str_replace("union","union",$str); 
    $str=str_replace("where","where",$str); 
    $str=str_replace("insert","insert",$str); 
    $str=str_replace("delete","delete",$str); 
    $str=str_replace("update","update",$str); 
    $str=str_replace("like","like",$str); 
    $str=str_replace("drop","drop",$str); 
    $str=str_replace("create","create",$str); 
    $str=str_replace("modify","modify",$str); 
    $str=str_replace("rename","rename",$str); 
    $str=str_replace("alter","alter",$str); 
    $str=str_replace("cas","cast",$str); 
    $str=str_replace("&","&",$str); 
    $str=str_replace(">",">",$str); 
    $str=str_replace("<","<",$str); 
    $str=str_replace(" ",chr(32),$str); 
    $str=str_replace(" ",chr(9),$str); 
    $str=str_replace(" ",chr(9),$str); 
    $str=str_replace("&",chr(34),$str); 
    $str=str_replace("'",chr(39),$str); 
    $str=str_replace(" 
    ",chr(13),$str); 
    $str=str_replace("''","'",$str); 
    $str=str_replace("css","'",$str); 
    $str=str_replace("CSS","'",$str); 
    return $str; 
    //HTML标签，可以作为扩展过滤 
    /* 
    $tags = array("/html", "/head", "/body", "/div", "/span", "/DOCTYPE", "/title", "/link", "/meta", "/style", "/p", "/h1,", "/h2,", "/h3,", "/h4,", "/h5,", "/h6", "/strong", "/em", "/abbr", "/acronym", "/address", "/bdo", "/blockquote", "/cite", "/q", "/code", "/ins", "/del", "/dfn", "/kbd", "/pre", "/samp", "/var", "/br", "/a", "/img", "/area", "/map", "/object", "/param", "/ul", "/ol", "/li", "/dl", "/dt", "/dd", "/table", "/tr", "/td", "/th", "/tbody", "/thead", "/tfoot", "/col", "/colgroup", "/caption", "/form", "/input", "/textarea", "/select", "/option", "/optgroup", "/button", "/label", "/fieldset", "/legend", "/script", "/noscript", "/b", "/i", "/tt", "/sub", "/sup", "/big", "/small", "/hr" ); 
    */
    }

}