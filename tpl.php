<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	<meta http-equiv="refresh" content="10" />
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
 			echo "<li>{$v['msg']} <a title='已解决' href='/kill/".$who."/".$k."'>x</a></li>";
 		}

 	 ?>
 	<li></li>
 </ul>
</body>
</html>