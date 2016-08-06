<html>
<head>
    <title></title>
</head>
<body>
    <pre>
        <?php print_r($d); ?>
    </pre>
    <script>
        var ddd = '<?php json_encode($d); ?>';
        console.log(ddd);
    </script>
</body>
</html>