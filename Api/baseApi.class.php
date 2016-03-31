<?php
class baseApi extends ThinkApi{
	function index(){
		echo sha1(substr(md5('123456'.self::$config['pwdKey']),0,20).self::$config['pwdKey']);
	}
}