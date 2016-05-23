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
 			$item = json_decode($v);
 			echo "<li><a title='已解决' href='/kill/".$who."/".$item['id']."'>x</a><pre>{$v['msg']} </pre></li>";
 		}

 	 ?>
 </ul>
</body>
</html>