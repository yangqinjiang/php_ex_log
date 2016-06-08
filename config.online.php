<?php 
defined('U') or die('Access Denied');
$config['displayErrorDetails'] = true;

//数据库的配置


//Redis数据库的配置
$config['redis_db']['host']   = "localhost";
$config['redis_db']['port']   = 6379;
$config['redis_db']['auth']   = "";
$config['redis_db']['db_id']   = 1;

$config['worktile']['client_id'] = '2b4ddbd6f526434285f62b0006cebc0f';
$config['worktile']['client_secret'] = '2b4ddbd6f526434285f62b0006cebc0f';

define('MEMBER_OK', 1);//会员状态,正常