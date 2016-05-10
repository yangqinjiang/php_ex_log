<!DOCTYPE html>
<html>
<head>
	<title>你好</title>
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