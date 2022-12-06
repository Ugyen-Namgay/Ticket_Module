<?php
if (isset($_POST["winners"])) {
    $urls = "https://api.bhutanapp.bt/v1.0.1/nationalday/lucky-draw/winners/";
}
else if (isset($_POST["select_winner"])) {
    $urls = "https://api.bhutanapp.bt/v1.0.1/nationalday/lucky-draw/select_winner/";
}

$opts = array('http' =>
array(
    'method'  => 'GET',
    'ignore_errors' => true,
    'header' => 'Content-Type: application/json'
    ), 
'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'cafile' => '/usr/lib/ssl/mycert.pem',
    )
);


//var_dump(openssl_get_cert_locations());
try {
    $context = stream_context_create($opts);
    $result = @file_get_contents($urls, false, $context);
    echo $result;

    if (!$result) {
        return "[]";
    }
    return $result;
    }
catch(Exception $e) {
    return "[]";
}