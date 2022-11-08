<?php
require_once "utils/sqldb.php";

function getVisIpAddr() {
      
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getBrowser() {

	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$browser = "N/A";

	$browsers = array(
	'/msie/i' => 'Internet explorer',
	'/firefox/i' => 'Firefox',
	'/safari/i' => 'Safari',
	'/chrome/i' => 'Chrome',
	'/edge/i' => 'Edge',
	'/opera/i' => 'Opera',
	'/mobile/i' => 'Mobile browser'
	);

	foreach ($browsers as $regex => $value) {
	if (preg_match($regex, $user_agent)) { $browser = $value; }
	}

	return $browser;
}

function client_detail($identity,$temp=True) {
	// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.114 Safari/537.36 Edg/103.0.1264.62
	//$_SERVER['HTTP_USER_AGENT']

	$remote_ip=getVisIPAddr();
	$ipdat = @json_decode(file_get_contents(
    "http://www.geoplugin.net/json.gp?ip=" . $remote_ip));
    $hostname=str_replace(",","-",gethostbyaddr($remote_ip));

    $continent = str_replace(",","-",$ipdat->geoplugin_continentName);
    $country = str_replace(",","-",$ipdat->geoplugin_countryName);
    $city = str_replace(",","-",$ipdat->geoplugin_city);
    $timezone = str_replace(",","-",$ipdat->geoplugin_timezone);
    
	$remote_port=str_replace(",","-",$_SERVER["REMOTE_PORT"]);
	$browser=str_replace(",","-",getBrowser());
	$os=str_replace(",","-",explode("(",explode(")",$_SERVER['HTTP_USER_AGENT'])[0])[1]);
	

	$vals = str_replace("`","-",str_replace("'","",str_replace('"','',$remote_ip.",".$continent.",".$country.",".$city.",".$timezone.",".$browser.",".$os.",".$remote_port.",".$identity)));


	if ($temp) {
		insert("tempvisitors","ip,continent,country,city,timezone,browser,os,remote_port,username",$vals);
	}
	else {
		insert("visitors","ip,continent,country,city,timezone,browser,os,remote_port,username",$vals);
	}
}

//IS IT FLOODING??
// if (count(json_decode(get("tempvisitors","ip","ip='".getvisIpAddr()."' AND (access_time>= NOW() - INTERVAL 5 minute)")))>20) {
// 	session_regenerate_id();
// 	// echo "BLOCKED!!!! TRY AGAIN AFTER 5 MINS";
// 	http_response_code(403);
// 	include('errorpage/error-403.php');
// 	die();
// 	exit();
// }

?>