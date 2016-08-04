<?php

define('U', 'yes,is me.');
defined('U') or die('Access Denied');
session_start();
define('ROOT',__DIR__);
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
	
	header('location: /A');exit;
});
$app->get('/{who}', function ($request, $response, $args) {
	
	$who = $args['who'];
	if(empty($_SESSION['worktile_login']['__pid']) || empty($_SESSION['worktile_login']['__eid'])){
			$pid = $eid = 0;
	}else{
			$pid = $_SESSION['worktile_login']['__pid'];
			$eid = $_SESSION['worktile_login']['__eid'];
	}

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
			if(empty($item['id'])){
				continue;
			}
			$exist = $redis->sIsMember('ok_post:'.$who,$item['id']);
			if($exist){
				unset($raw_msg[$key]);
				continue;
			}
			$raw_msg[$key] = $item;
		}
		ob_clean();
		$response->withJson(['prefix_pool'=>$prefix_pool,'list'=>$raw_msg]);
});
$app->get('/list_limit/{who}/{page}/{perPage}',function($request,$response,$args){
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
//当需要查询某个主题某一页的评论时，就可主题的topicId通过指令
//zrevrange topicId (page-1)×10 (page-1)×10+perPage
//这样就能找出某个主题下某一页的按时间排好顺序的所有评论的commintId。page为查询第几页的页码，perPage为每页显示的条数。
	//只取前5条数据
	$page = empty($args['page']) ? 0 : $args['page'];
	$perPage = empty($args['perPage']) ? 0 : $args['perPage'];

	$min = ($page-1)*10;
	$size = $min+$perPage;
	$ids = $redis->zRevRange('ex_post:'.$who,$min,$size);
	$d = [];
	foreach ($ids as $id){
		$d[] = $redis->hMGet('ex_post:postid:'.$who.':'.$id,['msg']);
	}

	foreach ($d as $key => $value) {
		$item = (array)json_decode($value);
		if(empty($item['id'])){
			continue;
		}

		$exist = $redis->sIsMember('ok_post:'.$who,$item['id']);
		if($exist){
			unset($d[$key]);
			continue;
		}
	}
	ob_clean();
	$response->withJson(['prefix_pool'=>$prefix_pool,'list_id'=>$ids,'list'=>$d]);
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


	$this->tracer->record(array('msg'=>$msg),empty($prefix_pool[$prefix_key]) ? 'A' : $prefix_pool[$prefix_key]);
	$response->withJson(array('code'=>200,'msg'=>$msg));
	return $response;
});
$app->get('/detail/{who}/{key}',function ($request, $response, $args)
{
	var_dump('显示更多内容,未实现');
});
//-----------------------------------------------------------
$app->get('/worktile/setting',function(){
	if(empty($_SESSION['worktile_login'])){
		die('请先登录Worktile授权');
	}
	//
	include 'worktile_setting.php';exit;
});
$app->get('/worktile/setprojects',function(){
	$_SESSION['worktile_login']['__pid'] = $_GET['p'];
	$_SESSION['worktile_login']['__pname'] = $_GET['name'];
	echo '/worktile/entries/'.$_GET['p'];exit;
});
$app->get('/worktile/setentries',function(){
	$_SESSION['worktile_login']['__eid'] = $_GET['e'];
	$_SESSION['worktile_login']['__ename'] = $_GET['name'];
	echo '/worktile/entries/'.$_GET['e'];exit;
});
//1,请求授权 https://open.worktile.com/oauth2/authorize
$app->get('/worktile/authorize',function ($request, $response, $args)
{
	$worktile = $this->get('settings')['worktile'];
	$url = 'https://open.worktile.com/oauth2/authorize?client_id='.$worktile['client_id'].'&redirect_uri=http://trace.qbgoo.com/worktile/response';
    echo '<a href="'.$url.'">Worktile授权登录</a>';
});
//2,获取access_token  https://api.worktile.com/oauth2/access_token
$app->get('/worktile/response',function ($request, $response, $args)
{
	if(empty($_GET['code'])){
		exit('error call me.');
	}
	$code = $_GET['code'];
	$url = 'https://api.worktile.com/oauth2/access_token';
	$ch = curl_init();
    //post data
	$worktile = $this->get('settings')['worktile'];
     $post = ['client_id'=>$worktile['client_id'],'client_secret'=>$worktile['client_secret'],'code'=>$code];
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
	if($ret_arr['error_code'] == '100004' || $ret_arr['error_code'] == '1003' ){
		die($ret_arr['error_message']);
	}
	//保存正确的数据
	$saveAccessToken = $this->saveAccessToken;
	$saveAccessToken($ret);
	header('location: /worktile/user/profile');exit;

});
//人个资料
$app->get('/worktile/user/profile',function($request, $response, $args){
	$access_token = $this->worktile_access_token;
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
	if($ret['error_code'] == '100005' || $ret['error_code'] == '100006'){
		die($ret['error_message']);
	}
	$_SESSION['worktile_login'] = $ret;
	header('location: /');exit;
});
//获取用户所有项目
$app->get('/worktile/projects',function ($request, $response, $args)
{
	$access_token = $this->worktile_access_token;
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
	echo $ret;exit;
});

//获取项目成员
$app->get('/worktile/project_members/{pid}',function ($request, $response, $args)
{
	$access_token = $this->worktile_access_token;

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
	echo $ret;exit;
});
//获取项目的任务组列表
$app->get('/worktile/entries/{pid}',function ($request, $response, $args)
{
	$access_token = $this->worktile_access_token;

	$url = 'https://api.worktile.com/v1/entries?pid='.$args['pid'];
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
	echo $ret;exit;
});

//创建任务
$app->get('/worktile/task/{pid}/{entry_id}/{who}/{key}',function ($request, $response, $args)
{
	$who = $args['who'];
	$key = $args['key'];
	if(empty($who) || empty($key)){

		return;
	}

	if(empty($args['pid']) || empty($args['entry_id'])){
		$pid = $_SESSION['worktile_login']['__pid'];
		$eid = $_SESSION['worktile_login']['__eid'];
	}else{
		$pid = $args['pid'];
		$eid = $args['entry_id'];
	}

	if(empty($pid) || empty($eid)){
		return;
	}

	try {
		$redis = new Redis();
	   $redis->connect('127.0.0.1');
	   $redis->select(1);
	} catch (Exception $e) {
		echo $e->getMessage();
		die('连不上Redis');
	}
	$msg = $redis->hMGet('ex_post:postid:'.$who.':'.$key,['msg']);
	$msg = (array)json_decode($msg['msg']);
	$msg = 'TRACE::'.$msg['msg'].'::请找出此bug的原因,并解决它.http://trace.qbgoo.com/detail/'.$who.'/'.$key;
	$access_token = $this->worktile_access_token;
//curl -d 'name=还信用卡&entry_id=xxxx&desc=10月12号还信用卡' 'https://api.worktile.com/v1/task?pid=xxx&access_token=xxx'
	$url = 'https://api.worktile.com/v1/task?pid='.$pid;
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'access_token: ' . $access_token['access_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	$post = ['name'=>'来自[异常捕获系统]的任务'.date('Y-m-d H:i:s',time()),'entry_id'=>$eid,'desc'=>$msg];
	$post_str = json_encode($post);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
	$ret = curl_exec($ch);
	curl_close($ch);
	echo $ret;
	$ret = json_decode($ret,true);
	if(in_array($ret['error_code'],[100005,100006,600002,600003])){
		//错误

//		错误码(error_code)	错误信息(error_message)	http状态码(statusCode)
//		100005	没授权，请授权后再操作	401
//		100006	access_token不正确	400
//		600002	任务名称或任务组id为空	400
//		600003	创建任务失败	500
		echo json_encode($ret);
	}else{
		//存档
		$redis->sAdd('ok_post:'.$who,$key);
		$redis->sCard('ok_post:'.$who);
	}

	exit;
});

//刷新token
$app->get('/worktile/refresh_token',function ($request, $response, $args){
	$access_token = $this->worktile_access_token;
	$worktile = $this->get('settings')['worktile'];
	$url = 'https://api.worktile.com/oauth2/refresh_token?client_id='.$worktile['client_id'];
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
			'Content-Type: application/json',
			'refresh_token: ' . $access_token['refresh_token'])
	);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$ret = curl_exec($ch);
	curl_close($ch);

	$ret_arr = json_decode($ret,true);
	if($ret_arr['error_code'] == '100009' || $ret_arr['error_code'] == '100008' ){
		die($ret_arr['error_message']);
	}
	//保存正确的数据
	$saveAccessToken = $this->saveAccessToken;
	$saveAccessToken($ret);


});
//退出worktile账号
$app->get('/worktile/logout',function($request, $response, $args){
	unset($_SESSION['worktile_login']);
	session_destroy();
	header('location: /A');exit;
});

//-----------------------------------------------------------
$app->run();
