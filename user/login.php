<?php
//$conn is universal for sql query please
// $venues=json_decode(query($conn,"venues","country,location"));
// foreach ($venues as $venue) {
// 	echo $venue[0].": ".$venue[1]."<br/>";
// }
require_once "utils/sqldb.php";
require_once "utils/visitorlog.php"; //client_detail(identity,is_it_temp?)


$alert="This is a closed page permitted to only authorized user. Any suspected attempts to login will be captured and reported by the system.";
if (isset($_POST["email"]) && isset($_POST["password"])) {
	$valid = json_decode(get("users","id","password=MD5('".$_POST["password"]."') AND (username='".$_POST["email"]."' OR email='".$_POST["email"]."')"));
	//echo $valid;
	if (!empty($valid) && count($valid[0])>0) {
		$alert="Thank you. Please while we redirect you to your page.";
		update("users","session",session_id(),"id=".$valid[0][0]."");
		//Redirect("post",true);
		//echo "LOGIN SUCCESS";
		client_detail($_POST["email"],False);
		Redirect("home",True);
	}
	else {
		client_detail($_POST["email"]);
		$alert="OH NOOO!!!<br>Looks like you credentials are incorrect. Please try again.";
		Redirect("/",True);
		//include "app/landing.php";
	}
}
else if (isset($_POST["logout"])) {
	$_SESSION = array();
	session_regenerate_id();
	Redirect("/",True);
	//include "app/landing.php";

}
else {
	//Redirect("",true);
	Redirect("/",True);
	//include "app/landing.php";
}


?>
