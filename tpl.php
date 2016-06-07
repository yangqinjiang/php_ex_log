<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	<meta http-equiv="refresh" content="60" />
	<style type="text/css">
	ul li{
		padding: 10px;
	}
		ul li:hover{
			background-color: beige;
			
		}
	</style>
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
 			if(isset($item['archive']) && $item['archive'] == '1')continue;
 			// var_dump($item);
 			echo "<li><a title='删除它' href='/kill/".$who."/".$item['id']."'>x</a>&nbsp;&nbsp;&nbsp;<a title='归档' href='/archive/".$who."/".$item['id']."'>Archive</a><pre>{$item['msg']}--{$item['archive']}</pre></li>";
 		}

 	 ?>
 </ul>
</body>
</html>