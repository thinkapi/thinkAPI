<?php

//初始化配置
$config = array();

//数据库连接
$config['db']['mysql']['host'] = '192.168.1.162';
$config['db']['mysql']['database'] = 'qbao';
$config['db']['mysql']['user'] = 'root';
$config['db']['mysql']['pwd'] = '123456';
$config['db']['mysql']['prefix'] = 'qb_';
$config['db']['mysql']['port'] = '3306';
$config['db']['mysql']['char'] = 'utf8';
$config['db']['mysql']['pconnect'] = false;

//加密方式   1.数据签名 url参数token带入一串md5加密的密钥字符串  2.rc4加密
$config['encrypt'] = 'md5';
$config['key'] = '^&%6DFH@#%Fd^$~!@#h*)4@#D%&^*&(#%$^5fg';

//密码加密密钥
$config['pwdKey'] = '6sf%*sd5#$^d#$^#$@%4af';

//url
$config['image_url'] = 'http://image.api.com';