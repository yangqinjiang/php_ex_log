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
	
	$who = $args['who'];
	include 'tpl.php'; 
    return $response;
});
$app->get('/list/{who}',function($request,$response,$args){
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

		$raw_msg = $redis->sort('ex_post:'.$who,array(
				'by'=>'ex_post:postid:'.$who.':*->time',
				'SORT'=>'DESC',
				'get'=>array(
						'ex_post:postid:'.$who.':*->msg'
					)
			));
		foreach ($raw_msg as $key => $value) {
			$item = (array)json_decode($value);
			$exist = $redis->sIsMember('ok_post:'.$who,$item['id']);
			if($exist){
				unset($raw_msg[$key]);
				continue;
			}
			$raw_msg[$key] = $item;
		}

		$response->withJson(['prefix_pool'=>$prefix_pool,'list'=>$raw_msg]);
});
$app->get('/kill/{who}/{key}',function ($request, $response, $args){
	$who = $args['who'];
	$key = $args['key'];

	$this->tracer->kill($who,$key);
	header('location: /'.$who);exit;

});
$app->get('/archive/{who}/{key}',function ($request, $response, $args){
	$who = $args['who'];
	$key = $args['key'];

	$this->tracer->archive($who,$key);
	header('location: /'.$who);exit;
	// exit;

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
	var_dump($code);
	$url = 'https://api.worktile.com/oauth2/access_token';
	$ch = curl_init();
    //post data
    //?client_id=xxx&client_secret=yyy&code=zzz'
     $post = ['client_id'=>'2b4ddbd6f526434285f62b0006cebc0f','client_secret'=>'3d6b481a3dc04bf183651e062cbfc0e6','code'=>$code];
     $post_str = json_encode($post);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array(                                                                          
	    'Content-Type: application/json',                                                                                
	    'Content-Length: ' . strlen($post_str))
    );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
     curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);
    curl_close($ch);


	$ret_arr = json_decode($ret,true);
	var_dump($ret_arr);
	if($ret_arr['error_code'] == '100004' || $ret_arr['error_code'] == '1003' ){
		die($ret_arr['error_message']);
	}
	//保存正确的数据
	file_put_contents(__DIR__.'/temp/worktile_access_token.json',$ret);
	header('location: /worktile/user/profile');exit;

});
//人个资料
$app->get('/worktile/user/profile',function($request, $response, $args){
	$access_token_str = file_get_contents(__DIR__.'/temp/worktile_access_token.json');
	$access_token = json_decode($access_token_str,true);
	var_dump($access_token);

	//
	$url = 'https://api.worktile.com/v1/user/profile';
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$ret = curl_exec($ch);
	curl_close($ch);

	$ret = json_decode($ret,true);
	echo '人个资料';
	var_dump($ret);
	echo '<a href="/worktile/projects">获取用户所有项目</a>';

});
//获取用户所有项目
$app->get('/worktile/projects',function ($request, $response, $args)
{
	$access_token_str = file_get_contents(__DIR__.'/temp/worktile_access_token.json');
	$access_token = json_decode($access_token_str,true);

	$url = 'https://api.worktile.com/v1/projects';
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$ret = curl_exec($ch);
	curl_close($ch);
	$ret = json_decode($ret,true);
	echo '获取用户所有项目';
	var_dump($ret);
	
	echo '<a href="/worktile/project_members">获取项目成员</a>';
});

//获取项目成员
$app->get('/worktile/project_members/{pid}',function ($request, $response, $args)
{
	$access_token_str = file_get_contents(__DIR__.'/temp/worktile_access_token.json');
	$access_token = json_decode($access_token_str,true);

	$url = 'https://api.worktile.com/v1/projects/'.$args['pid'].'/members';
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$ret = curl_exec($ch);
	curl_close($ch);
	$status = $response->getStatusCode();
	$ret = json_decode($ret,true);
	echo '获取项目成员';
	var_dump($ret);
	echo '<a href="/worktile/entries">获取项目成员</a>';
});
//获取项目的任务组列表
$app->get('/worktile/entries/{pid}',function ($request, $response, $args)
{
	$access_token_str = file_get_contents(__DIR__.'/temp/worktile_access_token.json');
	$access_token = json_decode($access_token_str,true);

	$url = 'https://api.worktile.com/v1/entries?pid'.$args['pid'];
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$ret = curl_exec($ch);
	curl_close($ch);
	$ret = json_decode($ret,true);
	echo '获取项目的任务组列表';
	var_dump($ret);
	echo '<a href="/worktile/task">创建任务</a>';
});

//创建任务
$app->get('/worktile/task/{pid}/{entry_id}',function ($request, $response, $args)
{
	$access_token_str = file_get_contents(__DIR__.'/temp/worktile_access_token.json');
	$access_token = json_decode($access_token_str,true);
//curl -d 'name=还信用卡&entry_id=xxxx&desc=10月12号还信用卡' 'https://api.worktile.com/v1/task?pid=xxx&access_token=xxx'
	$url = 'https://api.worktile.com/v1/task?pid='.$args['pid'];
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	$post = ['name'=>'测试','entry_id'=>$args['entry_id'],'desc'=>'测试任务的描述'];
	$post_str = json_encode($post);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
	$ret = curl_exec($ch);
	curl_close($ch);
	$ret = json_decode($ret,true);


	echo '创建任务';
	var_dump($ret);
});
//-----------------------------------------------------------
$app->run();
