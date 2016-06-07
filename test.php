<?php 

	if(empty($_GET['code'])){
		exit('error call me.');
	}
	$code = $_GET['code'];
	$url = 'https://api.worktile.com/oauth2/access_token?client_id=2b4ddbd6f526434285f62b0006cebc0f&&client_secret=3d6b481a3dc04bf183651e062cbfc0e6&code='.$code;
	$ch = curl_init();
        $this_header = array(
            "Content-Type"=>'application/json'
        );

    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);
    curl_close($ch);
    var_dump($ret);