<!DOCTYPE html>
<html>
<head>
	<title>异常错误显示</title>
	
	<script type="text/javascript" src="artTemplate.js"></script>
	
	<script type="text/javascript" src="/js/zepto.min.js"></script>
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
			white-space: pre-wrap;
    		word-wrap: break-word;
    		    background-color: #FBFBFB;
		}
		#prefix_bar{
			margin-top: 10px;
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

<?php
	if(!empty($_SESSION['worktile_login'])){
		echo 'worktile用户:'.$_SESSION['worktile_login']['name'].'-'.$_SESSION['worktile_login']['display_name'].'--><a href="/worktile/logout">退出</a>';
		if(empty($_SESSION['worktile_login']['__pname']) || empty($_SESSION['worktile_login']['__ename'])){
			echo '&nbsp;&nbsp;&nbsp;请点击<a target="_blank" href="/worktile/setting">设置</a>,选择项目及其任务列表';
		}else{
			echo '&nbsp;&nbsp;&nbsp;'.$_SESSION['worktile_login']['__pname'].'-'.$_SESSION['worktile_login']['__ename'];
			echo '&nbsp;&nbsp;&nbsp;<a target="_blank" href="/worktile/setting">设置</a>';
		}
		
	}else{
		echo '<span id="wa"></span>';
	}

?>

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
			&nbsp;&nbsp;&nbsp;
			<a class="a"  title='创建worktile任务' _href='http://trace.qbgoo.com/worktile/task/<?php echo $pid; ?>/<?php echo $eid; ?>/<?php echo $who; ?>/{{value.id}}'>Task</a>
		<pre>{{value.msg}}</pre></li>
		{{/each}}
</script>

 <ul id="list_ul">
 	
 </ul>
<a href="javascript:get_more_list_data()">更多</a>
 <script type="text/javascript">


	 function list_data(who,page,size,cb) {
		 $.get('/list_limit/'+who+'/'+page+'/'+size,function (data) {
			 console.log(data);
			 for(i=0;i<data.list.length;i++){
				 data.list[i] = JSON.parse(data.list[i].msg);
			 }
			 cb && cb(data);


		 },'json');
	 }

	 var who = '<?php echo $who; ?>';
	 var page = 1;
	 var size = 250;
	 var page_size = window.localStorage.getItem('page_size');
	 if(page_size){
		 size = page_size;
	 }

	 list_data(who,page,size,function (data) {
		 // JSON.
		 var html = template('prefix',data);
		 // console.log(html);
		 document.getElementById('prefix_bar').innerHTML = html;

		 html = template('list',data);
		 // console.log(html);
		 document.getElementById('list_ul').innerHTML = html;
		 //
		 $('#list_ul a.a').bind('click',function (d) {

			 var href = $(this).attr('_href');
			 $(this).parent().hide();
//				 console.log(href);
			 var _this = $(this);
			 $.get(href,function (d) {
			 });
		 });

		 //渲染tab页面
		 var who = '<?php echo $who; ?>';
		 $('#'+who).addClass('current');
		 page = page +1;
	 });
	 var get_more_list_data = function () {
		 list_data(who,page,size,function (data) {
			 var html = template('list',data);
			 // console.log(html);
//			 document.getElementById('list_ul').innerHTML = html;
			 $('#list_ul').append(html);
			 page = page +1;
		 });
	 }




	 $.get('/worktile/authorize',function (d) {
		 console.log(d);
		 $('#wa').html(d);
	 });

 </script>
</body>
</html>