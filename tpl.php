<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	<meta http-equiv="refresh" content="30" />
</head>
<body>
<?php 
 foreach ($prefix_pool as $key => $value) {
 	
 ?>
<a href="/<?php echo $value; ?>"><?php echo $key; ?></a>
<?php 
}
 ?>
 <ul>
 	<?php 
 		foreach ($raw_msg as $k => $v) {
 			$item = (array)json_decode($v);
 			// var_dump($item);
 			echo "<li><a title='删除它' href='/kill/".$who."/".$item['id']."'>x</a>&nbsp;&nbsp;&nbsp;<a title='归档' href='/archive/".$who."/".$item['id']."'>Archive</a><pre>{$item['msg']} </pre></li>";
 		}

 	 ?>
 </ul>
</body>
</html>