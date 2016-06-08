<!DOCTYPE html>
<html>
<head>
    <title>Worktile的项目设置</title>

    <script type="text/javascript" src="/artTemplate.js"></script>

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

<?php
if(!empty($_SESSION['worktile_login'])){
    echo 'worktile用户:'.$_SESSION['worktile_login']['name'].'-'.$_SESSION['worktile_login']['display_name'].'--><a href="/worktile/logout">退出</a>';
}else{
    echo '<span id="wa"></span>';
}

?>
<script type="text/html" id="projects">
    {{each list as i}}
    <a style="cursor: pointer;" _href="/worktile/setprojects?p={{i.pid}}&name={{i.name}}" title="选择默认项目名" name="{{i.name}}">{{i.name}}</a>
    {{/each}}
</script>
<script type="text/html" id="entries">
    {{each list as i}}
    <a _href="/worktile/setentries?e={{i.entry_id}}&name={{i.name}}" title="选择任务组名" name="{{i.name}}">{{i.name}}</a>
    {{/each}}
</script>

<span id="projects_bar">

</span>
<span id="entry_bar">

</span>
<div id="projects_list">

</div>
<div id="entries_list">

</div>
<script>
    //获取项目
    $.get('/worktile/projects',function (d) {
        console.log(d);
        var html = template('projects',{list:d});
         console.log(html);
        document.getElementById('projects_list').innerHTML = html;
        
        $('#projects_list a').bind('click',function (d) {
            var href = $(this).attr('_href');
            var name = $(this).attr('name');
            $('#projects_bar').html(name);
            console.error(href);
            $.get(href,function (data) {
                getEntries(data);
            });
        });
    },'json');
    function getEntries(href) {
        //获取项目的任务组列表
        $.get(href,function (d) {
            console.log(d);
            var html = template('entries',{list:d});
            console.log(html);
            document.getElementById('entries_list').innerHTML = html;
            $('#entries_list a').bind('click',function (d) {
                var href = $(this).attr('_href');
                var name = $(this).attr('name');
                $('#entry_bar').html(name);
                console.error(href);
                $.get(href,function (data) {
                });
            });
        },'json');
    }

//    //
//    $.get('/worktile/entries/ca3da2a9323c40a6b3ee8201070eeeb9',function (d) {
//        console.log(d);
//    },'json');
//
//    $.get('/worktile/task/ca3da2a9323c40a6b3ee8201070eeeb9/01a0f7bd99114761ba1b26b865527408',function (d) {
//        console.log(d);
//    },'json');
</script>
</body>
</html>