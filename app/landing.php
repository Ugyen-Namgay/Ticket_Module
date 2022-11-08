<?php
	
	require_once "utils/sqldb.php";
  if (isset($_POST["logout"])) {
    $_SESSION = array();
    session_regenerate_id();
  }
	if (!empty(isonline())) {
		Redirect("home",true);
	}
	else {

//$conn is universal for sql query please
// $venues=json_decode(query($conn,"venues","country,location"));
// foreach ($venues as $venue) {
// 	echo $venue[0].": ".$venue[1]."<br/>";
// }
require_once "utils/sqldb.php";
#require_once "utils/visitorlog.php"; //client_detail(identity,is_it_temp?)


$alert="This is a closed page permitted to only authorized user. Any suspected attempts to login will be captured and reported by the system.";

if (isset($_POST["email"]) && isset($_POST["password"])) {
	$valid = json_decode(get("admin_user","admin_id","password=MD5('".$_POST["password"]."') AND  email='".$_POST["email"]."'"));
	//echo $valid;
	if (!empty($valid) && count($valid[0])>0) {
		$alert="Thank you. Please while we redirect you to your page.";
		update("admin_user","session_id",session_id(),"admin_id=".$valid[0][0]."");
		//Redirect("post",true);
		//echo "LOGIN SUCCESS";
		//client_detail($_POST["email"],False);
		Redirect("home",True);
	}
	else {
		client_detail($_POST["email"]);
		$alert="OH NOOO!!!<br>Looks like you credentials are incorrect. Please try again.";
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="slide navbar style.css">
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
<link rel="shortcut icon" href="images/bbs3-logo-mini.png" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<style>
*, *:before, *:after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  color: #999;
  padding: 20px;
  display: flex;
  min-height: 100vh;
  align-items: center;
  font-family: "Raleway";
  justify-content: center;
  background-color: #fbfbfb;
  flex-direction: column;
}

#mainButton {
  color: white;
  border: none;
  outline: none;
  font-size: 24px;
  font-weight: 200;
  overflow: hidden;
  position: relative;
  border-radius: 2px;
  letter-spacing: 2px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
  text-transform: uppercase;
  background-color: #e1b633;
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}
#mainButton .btn-text {
  z-index: 2;
  display: block;
  padding: 10px 20px;
  position: relative;
}
#mainButton .btn-text:hover {
  cursor: pointer;
}
#mainButton:after {
  top: -50%;
  z-index: 1;
  content: "";
  width: 150%;
  height: 200%;
  position: absolute;
  left: calc(-150% - 40px);
  background-color: rgba(255, 255, 255, 0.2);
  -webkit-transform: skewX(-40deg);
  -moz-transform: skewX(-40deg);
  -ms-transform: skewX(-40deg);
  -o-transform: skewX(-40deg);
  transform: skewX(-40deg);
  -webkit-transition: all 0.2s ease-out;
  -moz-transition: all 0.2s ease-out;
  -ms-transition: all 0.2s ease-out;
  -o-transition: all 0.2s ease-out;
  transition: all 0.2s ease-out;
}
#mainButton:hover {
  cursor: default;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
}
#mainButton:hover:after {
  -webkit-transform: translateX(100%) skewX(-30deg);
  -moz-transform: translateX(100%) skewX(-30deg);
  -ms-transform: translateX(100%) skewX(-30deg);
  -o-transform: translateX(100%) skewX(-30deg);
  transform: translateX(100%) skewX(-30deg);
}
#mainButton.active {
  box-shadow: 0 19px 38px rgba(0, 0, 0, 0.3), 0 15px 12px rgba(0, 0, 0, 0.22);
}
#mainButton.active .modal {
  -webkit-transform: scale(1, 1);
  -moz-transform: scale(1, 1);
  -ms-transform: scale(1, 1);
  -o-transform: scale(1, 1);
  transform: scale(1, 1);
}
#mainButton .modal {
  top: 0;
  left: 0;
  z-index: 3;
  width: 100%;
  height: 100%;
  padding: 20px;
  display: flex;
  position: fixed;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background-color: inherit;
  transform-origin: center center;
  background-image: linear-gradient(to top left, #e1b633 10%, #ca800c 65%, white 200%);
  -webkit-transform: scale(0.000001, 0.00001);
  -moz-transform: scale(0.000001, 0.00001);
  -ms-transform: scale(0.000001, 0.00001);
  -o-transform: scale(0.000001, 0.00001);
  transform: scale(0.000001, 0.00001);
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}

.close-button {
  top: 20px;
  right: 20px;
  position: absolute;
  -webkit-transition: opacity 0.2s ease-in;
  -moz-transition: opacity 0.2s ease-in;
  -ms-transition: opacity 0.2s ease-in;
  -o-transition: opacity 0.2s ease-in;
  transition: opacity 0.2s ease-in;
}
.close-button:hover {
  opacity: 0.5;
  cursor: pointer;
}

.form-title {
  margin-bottom: 15px;
}

.form-button {
  width: 50vw;
  padding: 10px;
  color: #00a7ee;
  margin-top: 10px;
  max-width: 400px;
  text-align: center;
  border: solid 1px white;
  background-color: white;
  -webkit-transition: color 0.2s ease-in, background-color 0.2s ease-in;
  -moz-transition: color 0.2s ease-in, background-color 0.2s ease-in;
  -ms-transition: color 0.2s ease-in, background-color 0.2s ease-in;
  -o-transition: color 0.2s ease-in, background-color 0.2s ease-in;
  transition: color 0.2s ease-in, background-color 0.2s ease-in;
}
.form-button:hover {
  color: white;
  cursor: pointer;
  background-color: transparent;
}

.input-group {
  width: 100%;
  font-size: 16px;
  max-width: 400px;
  padding-top: 20px;
  position: relative;
  margin-bottom: 15px;
}
.input-group input {
  width: 100%;
  color: white;
  border: none;
  outline: none;
  padding: 5px 0;
  line-height: 1;
  font-size: 16px;
  font-family: "Raleway";
  border-bottom: solid 1px white;
  background-color: transparent;
  -webkit-transition: box-shadow 0.2s ease-in;
  -moz-transition: box-shadow 0.2s ease-in;
  -ms-transition: box-shadow 0.2s ease-in;
  -o-transition: box-shadow 0.2s ease-in;
  transition: box-shadow 0.2s ease-in;
}
.input-group input + label {
  left: 0;
  top: 20px;
  position: absolute;
  pointer-events: none;
  -webkit-transition: all 0.2s ease-in;
  -moz-transition: all 0.2s ease-in;
  -ms-transition: all 0.2s ease-in;
  -o-transition: all 0.2s ease-in;
  transition: all 0.2s ease-in;
}
.input-group input:focus {
  box-shadow: 0 1px 0 0 white;
}
.input-group input:focus + label, .input-group input.active + label {
  font-size: 12px;
  -webkit-transform: translateY(-20px);
  -moz-transform: translateY(-20px);
  -ms-transform: translateY(-20px);
  -o-transform: translateY(-20px);
  transform: translateY(-20px);
}

.channel {
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 10px;
  font-size: 16px;
  position: absolute;
  text-align: center;
  text-transform: none;
  letter-spacing: initial;
}
</style>
<body>
	<link href='https://fonts.googleapis.com/css?family=Raleway:400,500,300' rel='stylesheet' type='text/css'>
		<div id="mainButton">
			<div class="btn-text" onclick="openForm()">Bhutan App Ticket Module</div>
			<div class="modal">
			<form method="POST" action="/">
				<div class="close-button" onclick="closeForm()">x</div>
				<div class="form-title">Sign In</div>
				<div class="input-group">
					<input type="email" id="email" name="email" onblur="checkInput(this)" value="<?php echo isset($_POST["email"])?"".$_POST["email"]."":""; ?>"/>
					<label for="name">Username</label>
				</div>
				<div class="input-group">
					<input type="password" id="password" name="password" onblur="checkInput(this)" />
					<label for="password">Password</label>
				</div>
				<button class="form-button" type="submit" onclick="closeForm(); wait();">Go</button>
				<div class="channel">Ticketing System</div>
			</form>
			</div>
		</div>

		
		<br/>
		<br/>
		<div>

		<h5 id="alert"><?php 
					if (isset($alert)) {
						echo $alert;
					}
					else {
						echo "This is a closed page permitted to only authorized user. Any suspected attempts to login will be captured and reported by the system.";
					}
				?>
			</h5>
		</div>
</body>
<script>
	var button = document.getElementById('mainButton');

var openForm = function() {
	button.className = 'active';
};

var checkInput = function(input) {
	if (input.value.length > 0) {
		input.className = 'active';
	} else {
		input.className = '';
	}
};

var closeForm = function() {
	button.className = '';
};

var wait = function() {
	document.getElementById('alert').innerHTML="Please wait while the system validates your credentials";
}

document.addEventListener("keyup", function(e) {
	if (e.keyCode == 27 || e.keyCode == 13) {
		closeForm();
	}
});

checkInput(document.getElementById("email"));
</script>
</html>
<?php
	}
?>