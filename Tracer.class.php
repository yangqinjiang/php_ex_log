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
        $msg = $data['msg'];
        $this->sendMsg($msg);//根据权重发送信息
        $data['msg'] = array('id'=>$ex_postid,'msg'=>$msg);
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
    //设置标志位
    public function archive($who,$key)
    {
        var_dump($who);
        var_dump($key);
        $r = $this->redis->sIsMember('ok_post:'.$who,$key);
        var_dump($r);
        var_dump($this->redis->sAdd('ok_post:'.$who,$key));
        var_dump($this->redis->sCard('ok_post:'.$who));
        $r = $this->redis->sIsMember('ok_post:'.$who,$key);
        var_dump($r);
        var_dump($this->redis->sMembers('ok_post:'.$who))
        
    }
    public function sendMsg($msg)
    {
        // echo $msg;
        $out = '';
        preg_match_all('/QBGOO-->((.*)[GTRNCOGL]):\s/x',$str2,$out);
        if(empty($out[1])){//匹配不成功
            return;
        }
        //判断
        $trace_type =  $out[1];
        if(in_array($trace_type,array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC'))){//EMERG  ERR
            // 发送信息
        }
    }
    public function __destruct()
    {
        $this->redis->close();
    }
}