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
        $data['time']=time();//记录时间
        $data['msg'] = array('id'=>$ex_postid,'msg'=>$data['msg']);
        $data['msg'] = json_encode($data['msg']);
        $this->redis->hMset('ex_post:postid:'.$prefix.':'.$ex_postid,$data);
        //时间集合
        $this->redis->zAdd('ex_post:'.$prefix,time(),$ex_postid);
    }
    public function kill($who,$key)
    {
        $this->redis->zRem('ex_post:'.$who,$key);
        $k = 'ex_post:postid:'.$who.':'.$key;
        $this->redis->del($k);
    }
    public function __destruct()
    {
        $this->redis->close();
    }
}