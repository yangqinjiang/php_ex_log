<html>
<head>
    <title>详细的异常错误显示</title>
    <script type="text/javascript" src="/js/zepto.min.js"></script>
</head>
<body>
    <script>
        var id = '<?php echo $id ?>';
        var who = '<?php echo $who ?>';
        console.log(id);
        console.log(who);
        function list_data(who,page,size,cb) {
            $.get('/detail_item/'+who+'/'+id,function (data) {
                cb && cb(data);
            },'json');
        }
        list_data(who,id,function (data) {
            console.log(data);
        })
    </script>
</body>
</html>