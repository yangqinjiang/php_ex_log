<?php

define('U', 'yes,is me.');
defined('U') or die('Access Denied');
ini_set('display_errors','1');
error_reporting(E_ALL);
require 'vendor/autoload.php';
require_once 'lib.php';
date_default_timezone_set("PRC");
//区分本地与远程服务器的
if($_SERVER['SERVER_ADDR'] == '127.0.0.1'){
    include_once 'config.php';
}else{
    include_once 'config.online.php';
}

$app = new Slim\App(["settings" => $config]);
require_once 'container.config.php';
// function exception_handler($exception) {
//   echo "Uncaught exception: " , $exception->getMessage(), "\n";
// }

// set_exception_handler('exception_handler');

//中间件 http://www.slimframework.com/docs/concepts/middleware.html
// $app->add(function ($request, $response, $next) {
//     $auth_info = require_once 'auth.config.php';
//     //注册  调用商
//     if(isset($_GET['from']) && isset($_GET['token'])){
//         $remote_from = $_GET['from'];
//         $remote_token = $_GET['token'];
//         //检测 请求来源与请求token的有效性
//         if(isset($auth_info[$remote_from]) 
//             && $auth_info[$remote_from] == $remote_token ){
            
//             $response = $next($request, $response);
//             return $response;
//         }
        
//     }

//    exit('Access Denied! Illegal token.');
   
// });
$app->get('/', function ($request, $response, $args) {
	try {
		$redis = new Redis();
	   $redis->connect('127.0.0.1');
	   $redis->select(1);
	} catch (Exception $e) {
		echo $e->getMessage();
		die('连不上Redis');
	}



	   //列出数据
	   $max_post_id = $redis->get('global:ex_postid');
	   $max_post_id = intval($max_post_id);
	   echo '共  '.$max_post_id.'条<br/>';
	   echo '<meta http-equiv="refresh" content="10" />';
	   for ($i=1; $i <= $max_post_id; $i++) {
	   	$r = $redis->hmget('ex_post:postid:'.$i,array('msg'));
	   	echo "<pre>";
	   	echo $r['msg'];
	   	echo "</pre>";
	   }
    $response->write("User Server");
    return $response;
});

$app->post('/record',function($request, $response, $args){
	$data = $request->getParsedBody();
	$msg = $data['msg'];
	$prefix_key = $data['form'];
	if( empty($data['msg']) || $msg == 'Array'){
		die('数据格式有误');
	}
	$uri = $request->getUri();
	$prefix_pool = array('trace.qbgoo.com'=>'A','user.cengfan7.com'=>'B');


	$this->tracer->record(array('msg'=>$msg),empty($prefix_pool[$prefix_key]) ? '' : $prefix_pool[$prefix_key]);
	$response->withJson(array('code'=>200,'msg'=>$msg));
	return $response;
});
$app->run();
