<?php

// 记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));

if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();

// 版本信息
const ThinkApi_version     =   '0.9.0';

// 类文件后缀
const EXT               =   '.class.php'; 

require_once './Core/ThinkApi/ThinkApi.php';

ThinkApi::start();