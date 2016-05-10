<?php 
defined('U') or die('Access Denied');
if(!isset($app)){
    die('调用此文件顺序有误!');
}
$container = $app->getContainer();

//跟踪域名
$container['trace_pool'] = function ($c)
{
    $prefix_pool = array('trace.qbgoo.com'=>'A','user.cengfan7.com'=>'B','lc.cengfan7.com'=>'C','cfq.cengfan7.com'=>'D');
    return $prefix_pool;  
};
//日志记录
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};
//数据库连接
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};
//Redis数据库连接
$container['tracer'] = function ($c)
{
    // $redis 
    $redis_db = $c['settings']['redis_db'];
    require_once 'Tracer.class.php';
    $tracer = new qbgoo\tools\Tracer($redis_db['host'],$redis_db['port'],$redis_db['db_id']);
    return $tracer;
};

//错误处理函数
$container['errorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c) {
        $msg = 'errorHandler went wrong!';
        $now = date('Y-m-d H:i:s');
        $now = $now . '-->User-server';

        if($exception instanceof \PDOException){
            $msg = $now.'-->'.implode(',',$exception->errorInfo).'-->line:'.$exception->getLine().'-->file:'.$exception->getFile();
        }
        if($exception instanceof \Exception){
            $msg = $now .'-->'.$exception->getMessage().'-->line:'.$exception->getLine().'-->file:'.$exception->getFile();
        }
        $c->tracer->record(array('msg'=>$msg));

        return $c['response']->withStatus(500)
                             ->withHeader('Content-Type', 'text/html')
                             ->write($msg);
    };
};

//phpErrorHandler 错误处理函数
$container['phpErrorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c) {
        $now = date('Y-m-d H:i:s');
        $now = $now . '-->User-server';
        if($exception instanceof \Exception){
            $msg = $now .'-->'.$exception->getMessage().'-->line:'.$exception->getLine().'-->file:'.$exception->getFile();
        }
        $c->tracer->record(array('msg'=>$msg));
        return $c['response']->withStatus(500)
                             ->withHeader('Content-Type', 'text/html')
                             ->write('phpErrorHandler went wrong!');
    };
};