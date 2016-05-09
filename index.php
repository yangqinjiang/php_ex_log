<?php

   $redis = new Redis();
   $redis->connect('127.0.0.1');
   if(empty($redis->ping())){
   		die('连不上Redis');
   }
   $redis->select(1);

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