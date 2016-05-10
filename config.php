<?php 
defined('U') or die('Access Denied');
$config['displayErrorDetails'] = true;


//Redis数据库的配置
$config['redis_db']['host']   = "localhost";
$config['redis_db']['port']   = 6379;
$config['redis_db']['auth']   = "";
$config['redis_db']['db_id']   = 1;

define('MEMBER_OK', 1);//会员状态,正常