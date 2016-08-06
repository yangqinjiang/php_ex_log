<html>
<head>
    <title>详细的异常错误显示</title>
    <script type="text/javascript" src="/js/zepto.min.js"></script>
</head>
<body>
<div id="detail">

</div>
    <script>
        var id = '<?php echo $id ?>';
        var who = '<?php echo $who ?>';
        console.log(id);
        console.log(who);
        var ii = '';
        function list_data(who,page,cb) {
            $.get('/detail_item/'+who+'/'+id,function (data) {
                cb && cb(data);
            },'json');
        }
        list_data(who,id,function (data) {
            console.log(data.msg);
            ii = data.msg;
            $('#detail').html(JSON.parse(data.msg));
        })
    </script>
</body>
</html>