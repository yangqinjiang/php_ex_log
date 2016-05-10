<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	<meta http-equiv="refresh" content="10" />
</head>
<body>
<a href="/A">trace.qbgoo.com</a>
<a href="/B">user.cengfan7.com</a>
<a href="/C">lc.cengfan7.com</a>
<a href="/D">cfq.cengfan7.com</a>
 <ul>
 	<?php 
 		foreach ($raw_msg as $v) {
 			echo "<li>{$v['msg']}</li>";
 		}

 	 ?>
 	<li></li>
 </ul>
</body>
</html>