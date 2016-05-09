<?php
$c = array(
		'settings'=>array(
			'redis_db'=>array(
					'host'=>'localhost',
					'port' => 6379,
					'db_id' => 1
				)
		)
	);
   $redis_db = $c['settings']['redis_db'];
   $redis = new Redis();
   $redis->connect($redis_db['host'],$redis_db['port']);
   if(empty($redis->ping())){
   		die('连不上Redis');
   }
   $redis->select($redis_db['db_id']);

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