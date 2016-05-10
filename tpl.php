<!DOCTYPE html>
<html>
<head>
	<title>你好</title>
</head>
<body>

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