<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	
	<script type="text/javascript" src="artTemplate.js"></script>
	
	<script type="text/javascript" src="http://libs.useso.com/js/zepto/1.1.3/zepto.min.js"></script>
	<style type="text/css">
	ul li{
		padding: 10px;
		list-style: none;
	}
		ul li:hover{
			background-color: beige;
		}
		ul li a{
			cursor: pointer;
			font-size: 20px;
		}
		ul li pre{
			font-size: 24px;
		}
		#prefix_bar{
			    text-align: center;
		}
		#prefix_bar a{

			display: inline-block;
			padding: 10px;
			border:1px solid red;
			text-decoration: none;
		}
		.current{
			    color: white;
    			background-color: red;
		}
	</style>
</head>
<body>

<div id="prefix_bar">
	
</div>
<script type="text/html" id="prefix">
	{{each prefix_pool as value i}}
		<a id="{{value}}" href="/{{value}}">{{i}}</a>
	{{/each}}
</script>
<script type="text/html" id="list">
	{{each list as value}}
		<li><a class="a"  title='删除它' _href='http://trace.qbgoo.com/kill/<?php echo $who; ?>/{{value.id}}'>x</a>
		&nbsp;&nbsp;&nbsp;
		<a  class="a"  title='归档' _href='http://trace.qbgoo.com/archive/<?php echo $who; ?>/{{value.id}}'>Archive</a>
		<pre>{{value.msg}}</pre></li>
		{{/each}}
</script>

 <ul id="list_ul">
 	
 </ul>
 <script type="text/javascript">


 	$.get('/list/<?php echo $who; ?>',function (data) {
 		// console.log(data);
 		var html = template('prefix',data);
 		// console.log(html);
 		document.getElementById('prefix_bar').innerHTML = html;

 		html = template('list',data);
 		// console.log(html);
 		document.getElementById('list_ul').innerHTML = html;
	 	$('#list_ul a').bind('click',function (d) {
	 		
	 		var href = $(this).attr('_href');
	 		$(this).parent().hide();
	 		console.log(href);
	 		var _this = $(this);
	 		$.get(href,function (d) {
	 		});
	 	});

	 	//渲染tab页面
	 	var who = '<?php echo $who; ?>';
	 	$('#'+who).addClass('current');

 	},'json');



 </script>
</body>
</html>