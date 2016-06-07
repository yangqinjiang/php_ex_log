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

		$raw_msg = $redis->sort('ex_post:'.$who,array(
				'by'=>'ex_post:postid:'.$who.':*->time',
				'SORT'=>'DESC',
				'get'=>array(
						'ex_post:postid:'.$who.':*->msg'
					)
			));


		// var_dump($raw_msg);
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

//-----------------------------------------------------------

//1,请求授权 https://open.worktile.com/oauth2/authorize
$app->get('/worktile/authorize',function ($request, $response, $args)
{
	$url = 'https://open.worktile.com/oauth2/authorize?client_id=2b4ddbd6f526434285f62b0006cebc0f&redirect_uri=http://trace.qbgoo.com/worktile/response';
	$ch = curl_init();
        $this_header = array(
            "charset=UTF-8"
        );

    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);
    curl_close($ch);
    var_dump($ret);
    echo '<a href="'.$ret.'">点击</a>';
});
//2,获取access_token  https://api.worktile.com/oauth2/access_token
$app->get('/worktile/response',function ($request, $response, $args)
{
	if(empty($_GET['code'])){
		exit('error call me.');
	}
	$code = $_GET['code'];
	$url = 'https://api.worktile.com/oauth2/access_token?client_id=2b4ddbd6f526434285f62b0006cebc0f&&client_secret=3d6b481a3dc04bf183651e062cbfc0e6&code='.$code;
	$ch = curl_init();
        $this_header = array(
            "Content-Type"=>'application/json'
        );

    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);
    curl_close($ch);
    var_dump($ret);

});
//-----------------------------------------------------------
$app->run();
