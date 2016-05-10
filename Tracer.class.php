<?php
namespace qbgoo\tools;
/**
 * 异常日志记录器
 */
class Tracer
{
    protected  $redis;
    function  __construct($host,$port,$db_id){
        $this->redis = new \Redis();
        $this->redis->connect($host,$port);
        $this->redis->select($db_id);
    }

    public function ping()
    {
        $pong =  $this->redis->ping();
        return empty($pong);
    }

    public function record($data,$prefix)
    {
        if($this->ping()){
            return;
        }
        $ex_postid = $this->redis->incr('global:ex_postid:'.$prefix);
        $this->redis->hMset('ex_post:postid:'.$prefix.':'.$ex_postid,$data);
    }
    public function kill($who,$key)
    {
        $this->redis->del($key);
    }
    public function __destruct()
    {
        $this->redis->close();
    }
}