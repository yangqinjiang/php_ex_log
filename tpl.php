<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	<meta http-equiv="refresh" content="60" />
	<script type="text/javascript" src="artTemplate.js"></script>
	
	<script type="text/javascript" src="http://libs.useso.com/js/zepto/1.1.3/zepto.min.js"></script>
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

<div id="prefix_bar">
	
</div>
<script type="text/html" id="prefix">
	{{each prefix_pool as value i}}
		<a href="/{{value}}">{{i}}</a>
	{{/each}}
</script>
<script type="text/html" id="list">
	{{each list as value}}
		<li><a title='删除它' href='/kill/<?php echo $who; ?>/{{value.id}}'>x</a>
		&nbsp;&nbsp;&nbsp;
		<a title='归档' href='/archive/<?php echo $who; ?>/{{value.id}}'>Archive</a>
		<pre>{{value.msg}}</pre></li>
		{{/each}}
</script>

 <ul id="list_ul">
 	
 </ul>
 <script type="text/javascript">
 	$.get('/list/<?php echo $who; ?>',function (data) {
 		console.log(data);
 		var html = template('prefix',data);
 		console.log(html);
 		document.getElementById('prefix_bar').innerHTML = html;

 		html = template('list',data);
 		console.log(html);
 		document.getElementById('list_ul').innerHTML = html;

 	},'json');
 </script>
</body>
</html>