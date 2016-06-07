<?php 

$str = "2016-05-24 11:03:33-->QBGOO-->SQL: SHOW COLUMNS FROM `qbgoo_";
$str2 = "2016-05-24 11:02:55-->QBGOO-->NOTIC: [8] Undefined variable: list /alidata/www/qbgoo/qbgoo/qbgoo/Runtime/Cache/Home/e11fe9524c5f930a327566595";


$out = '';
preg_match_all('/QBGOO-->((.*)[LCR]):\s/x',$str,$out);
var_dump($out);
preg_match_all('/QBGOO-->((.*)[LCR]):\s/x',$str2,$out);
var_dump($out);