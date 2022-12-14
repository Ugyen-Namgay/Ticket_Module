<?php
include_once "utils/sqldb.php";
include_once "utils/visitorlog.php";  //client_detail(identity,is_it_temp?=true)



    
function api_get_token_for_phone($cid,$password) {
    //return "de6521af5df1f15d1e41c4bdb8e4f7eeb159d768"; //TEMP
    $settings = parse_ini_file("settings/config.ini", true);
    $url = $settings["bhutanapp"]["new_login_url"]; 
    $data = '{
        "cid":"'.$cid.'",
        "password":"'.$password.'"
    }';
    $opts = array('http' =>
        array(
            'method' => 'POST',
            'ignore_errors' => true,
            'header' => 'Content-type: application/json',
            'content' => $data
        ),
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,            
        ),
    );
    try {
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        return json_decode($result)->data->auth_token;
    }
    catch(Exception $e) {
        return false;
    }
    

    }

function api_get_phone_detail($cid) {
    $cacheddata = get_cache("APIDETAIL".$cid);
    if ($cacheddata && $cacheddata!='{"data":{},"error":true,"message":"User does not exist."}') {
        return $cacheddata;
    }
    $settings = parse_ini_file("settings/config.ini", true);
    $url = $settings["bhutanapp"]["new_user_detail_url"];
    //$token = api_get_token_for_phone($settings["bhutanapp"]["cid"],$settings["bhutanapp"]["password"]);

    $data = '{
        "cid":"'.$cid.'"
    }';


    $opts = array('http' =>
    array(
        'method'  => 'GET',
        'ignore_errors' => true,
        'header' => 'Content-Type: application/json',
        'content' => $data
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
        $result = @file_get_contents($url, false, $context);
        //echo $result;

        if (!$result) {
            set_cache("APIDETAIL".$cid,false,5);
            return false;
        }
        if ($result=='{"data":{},"error":true,"message":"User does not exist."}') { 
            set_cache("APIDETAIL".$cid,false,5); 
            return false;
        }
        set_cache("APIDETAIL".$cid,$result,0);
        return $result;
    }
    catch(Exception $e) {
        set_cache("APIDETAIL".$cid,false,5);
        return false;
    }

}

function send_sms($phone,$message) {
    $settings = parse_ini_file("settings/config.ini", true);
    $url = $settings["sms"]["url"];
    //$token = api_get_token_for_phone($settings["bhutanapp"]["cid"],$settings["bhutanapp"]["password"]);

    $data = '{
        "phone":"'.$phone.'",
        "message":"'.$message.'"
    }';

    $opts = array('http' =>
    array(
        'method' => $settings["sms"]["type"],
        //'header' => ['Authorization: token '.$token,'Content-Type: application/json'],
        'header' => ['Content-Type: application/json'],
        'content' => $data
    ),
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    )
    );

    try {
    $context = stream_context_create($opts);
    $result =file_get_contents($url, false, $context);
    // $result = @file_get_contents_curl($url);
    return $result;
    }
    catch(Exception $e) {
    return false;
    }

}

function generateOTP($n=6) {
    $valid = false;
    $generator = "1357902468";
    while (!$valid) {   
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand()%(strlen($generator))), 1);
        }
        if (get("otp","otp","otp='".$result."'")=="[]") {
            $valid = true;
        }
    }
    // Return result
    return $result;
}

function get_country() {
    $remote_ip=getVisIPAddr();
    if (get_cache($remote_ip)) {
        return get_cache($remote_ip);
    }
    $ipdat = @json_decode(file_get_contents(
      "http://www.geoplugin.net/json.gp?ip=" . $remote_ip));
    set_cache($remote_ip,str_replace(",","-",$ipdat->geoplugin_countryName));
    return str_replace(",","-",$ipdat->geoplugin_countryName);
  }

function getphoto($cid) {
    $cachedphoto = get_cache("PHOTO".$cid);
    if ($cachedphoto) {
        return $cachedphoto;
    }
    $settings = parse_ini_file("settings/config.ini", true);
    $url = $settings["censusimage"]["local_url"];
    $token = $settings["censusimage"]["token"];
    $composedurl = $url."cid=".$cid."&token=".$token."";
    $image=file_get_contents($composedurl);

    if ($image=="") {
        return "1";// Default image
    }
    else {
        $ratio=1;
        $data = base64_decode($image);
        if (!$data || $data=="") {
            return $defaultimage;
        }
        try {
            $im = @imagecreatefromstring($data);
        }
        catch (Exception $e){
            return "1";
        }
        if (!$im) {
            return "1";
        }
        $width = imagesx($im);
        $height = imagesy($im);
        if ($height>300) {
            $ratio = 300/$height;
        }

        $newwidth = $width * $ratio;
        $newheight = $height * $ratio;

        $newwidth =  (int)$newwidth;
        $newheight = (int)$newheight;
        
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        ob_start();
        imagepng($thumb);
        $temp = ob_get_contents();
        ob_end_clean();
        $theme_image_enc_little = base64_encode($temp);
        imagedestroy($im);
        $size=strlen($temp);
        //return $theme_image_enc_little;
        $previd = json_decode(get("images","id","bin='$theme_image_enc_little'"),true);
        if (sizeof($previd)==0) {
            insert("images","bin,format","$theme_image_enc_little,png");
            set_cache("PHOTO".$cid,json_decode(get("images","id","bin='$theme_image_enc_little'"),true)[0]["id"],0);
            return json_decode(get("images","id","bin='$theme_image_enc_little'"),true)[0]["id"];
        }
        else {
            set_cache("PHOTO".$cid,$previd[0]["id"],0);
            return $previd[0]["id"];
        }
    }

}

if (isset($_POST["number"]) && isset($_POST["message"])) {
    send_sms($_POST["number"],$_POST["message"]);
}