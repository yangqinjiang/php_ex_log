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
$app->get('/', function ($request, $response, $args) {
	// echo "hello world";
	header('location: /A');exit;
});
$app->get('/{who}', function ($request, $response, $args) {
	try {
		$redis = new Redis();
	   $redis->connect('127.0.0.1');
	   $redis->select(1);
	} catch (Exception $e) {
		echo $e->getMessage();
		die('连不上Redis');
	}
		$prefix_pool = $this->trace_pool;
		$who = $args['who'];
		if(!in_array(strtoupper($who), array_values($prefix_pool))){
			$who = 'A';
		}
		// $list = $redis->keys('ex_post:postid:'.$who.':*');

		// $raw_msg = array();
		// foreach ($list as $value) {

		// 	$rr = $redis->hmget($value,array('msg'));
		// 	if(empty($rr['msg'])){
		// 		continue;
		// 	}
		// 	$raw_msg[$value] = $rr;
		// }

		$r = $redis->sort('ex_post:'.$who,array(
				'by'=>'ex_post:postid:'.$who.':*->time',
				// 'SORT'=>'DESC',
				'get'=>array(
						'ex_post:postid:'.$who.':*->msg'
					)
			));
		$raw_msg = $r;
		// var_dump($r);
		// exti;
	include 'tpl.php';    
    return $response;
});
$app->get('/kill/{who}/{key}',function ($request, $response, $args){
	$who = $args['who'];
	$key = $args['key'];
	$this->tracer->kill($who,$key);
	header('location: /'.$who);exit;

});
$app->post('/record',function($request, $response, $args){
	$data = $request->getParsedBody();
	$msg = $data['msg'];
	$prefix_key = $data['form'];
	if( empty($data['msg']) || $msg == 'Array'){
		return;
	}
	$uri = $request->getUri();
	$prefix_pool = $this->trace_pool;


	$this->tracer->record(array('msg'=>$msg),empty($prefix_pool[$prefix_key]) ? '' : $prefix_pool[$prefix_key]);
	$response->withJson(array('code'=>200,'msg'=>$msg));
	return $response;
});
$app->run();
