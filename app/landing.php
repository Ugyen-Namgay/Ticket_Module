<?php
	
// 	require_once "utils/sqldb.php";
// 	if (!empty(isonline())) {
// 		Redirect("home",true);
// 	}
// 	else {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="stylesheet" type="text/css" href="slide navbar style.css">
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
<link rel="shortcut icon" href="images/rgob-logo.png" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<style>
body{
	margin: 0;
	padding: 0;
	display: flex;
	justify-content: center;
	align-items: center;
	min-height: 100vh;
	font-family: 'Jost', sans-serif;
	
}

.main{
	width: 80%;
    height: 600px;

	background: red;
	overflow: hidden;
	background: url("https://doc-08-2c-docs.googleusercontent.com/docs/securesc/68c90smiglihng9534mvqmq1946dmis5/fo0picsp1nhiucmc0l25s29respgpr4j/1631524275000/03522360960922298374/03522360960922298374/1Sx0jhdpEpnNIydS4rnN4kHSJtU1EyWka?e=view&authuser=0&nonce=gcrocepgbb17m&user=03522360960922298374&hash=tfhgbs86ka6divo3llbvp93mg4csvb38") no-repeat center/ cover;
	border-radius: 10px;
	box-shadow: 5px 20px 50px #000;
	background: linear-gradient(to bottom, #ffbe0b, #fca311, #fb5607);
	/*background: linear-gradient(to bottom, #0f0c29, #302b63, #24243e);*/
}
@media only screen and (min-width: 768px) {
  .main {
  		width: 350px;
		height: 500px;

  }
}


#chk{
	display: none;
}
.signup{
	position: relative;
	width:100%;
	height: 100%;

}
label{
	color: #fff;
	font-size: 2.3em;
	justify-content: center;
	display: flex;
	margin: 60px;
	font-weight: bold;
	cursor: pointer;
	transition: .5s ease-in-out;
}
input{
	width: 80%;
	height: 20px;
	background: #e0dede;
	justify-content: center;
	display: flex;
	margin: 20px auto;
	padding: 10px;
	border: none;
	outline: none;
	border-radius: 5px;
	font-weight: bolder;
	color: #ff6607;
}
button{
	width: 80%;
	height: 40px;
	margin: 10px auto;
	justify-content: center;
	display: block;
	color: #fff;
	/*background: #573b8a;*/
	background:  #fca311;
	font-size: 1em;
	font-weight: bold;
	margin-top: 50px;
	outline: none;
	border: none;
	border-radius: 5px;
	transition: .2s ease-in;
	cursor: pointer;
}
button:hover{
	/*background: #6d44b8;*/
	background:  #fb5607;

}
.login{
	height: 460px;
	background: #eee;
	/*border-radius: 10px;*/
	transform: translateY(-180px);
	transition: .8s ease-in-out;
}
.login label{
	/*color: #573b8a;*/
	color:  #fb5607;
	transform: scale(.6);
}

#chk:checked ~ .login{
	transform: translateY(calc(-100% - 100px));
}
#chk:checked ~ .login label{
	transform: scale(1.2);
	padding-top: 10px;	
}
#chk:checked ~ .signup label{
	transform: scale(.6);
}

.signup p{
	margin-left: 10%;
	margin-right: 10%;
	text-align: center;
	color: #fff;
	font-size: 1.2em;
}

</style>
<body>
	<div class="main">  	
		<input type="checkbox" id="chk" aria-hidden="true">

			<div class="signup">
				<label for="chk" area-hidden="true">Welcome</label>
					<p><?php 
					if (isset($alert)) {
						echo $alert;
					}
					else {
						echo "This is a closed page permitted to only authorized user. Any suspected attempts to login will be captured and reported by the system.";
					}
				?>
			</p>
<!-- 				<form>
					<label for="chk" aria-hidden="true">Sign up</label>
					<input type="text" name="txt" placeholder="User name" required="">
					<input type="email" name="email" placeholder="Email" required="">
					<input type="password" name="pswd" placeholder="Password" required="">
					<button>Sign up</button>
				</form> -->
			</div>

			<div class="login">
				<form method="POST" action="login">
					<label for="chk" aria-hidden="true">Login</label>
					<input type="text" id="email" name="email" placeholder="Username/Email" required="" value=<?php echo isset($_POST["email"])?"'".$_POST["email"]."'":""; ?> >
					<input type="password" id="password" name="password" placeholder="Password" required="">
					<button type="submit">Login</button>
				</form>
			</div>
	</div>
</body>
</html>
<?php
	//}
?>
