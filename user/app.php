<!DOCTYPE html>
<html>
<head>
	<title>Register</title>
	<!-- <link rel="stylesheet" type="text/css" href="slide/navbar/style.css"> -->
<link href="https://fonts.googleapis.com/css2?family=Jost:wght@500&display=swap" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/select2-bootstrap.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/tingle.min.css'?>" rel="stylesheet">
<link href="<?php echo $settings["app"]["homebase"].'/css/register.css'?>" rel="stylesheet">
<link rel="shortcut icon" href="<?php echo $settings["app"]["homebase"].'/'.$settings["app"]["logo"]?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<style>
    @import url(https://fonts.googleapis.com/css?family=Lato:400,100,300);
body {
  background: rgba(245, 132, 33, 0.87);
  min-height: 100vh;
  width: auto;
}

.title h1 {
  font-size: 3.5em;
  color: #fff;
}

.bar {
  height: 0.25em;
  width: 100%;
  background: #fff;
  margin: 1.5em auto 0;
}

.main {
  /*  background: $trans-orange;*/
  min-height: 50vh;
  position: relative;
  top: 20vh;
}

input[type=text], input[type=password] {
  font-size: 1.75em;
  padding: 0.55em;
  width: 100%;
  margin-bottom: 1em;
  border: none;
}
input[type=text]::-moz-placeholder, input[type=password]::-moz-placeholder {
  color: #aaaaaa;
  position: relative;
  padding: 0;
  -moz-transition: all 0.5s ease;
  transition: all 0.5s ease;
}
input[type=text]:-ms-input-placeholder, input[type=password]:-ms-input-placeholder {
  color: #aaaaaa;
  position: relative;
  padding: 0;
  -ms-transition: all 0.5s ease;
  transition: all 0.5s ease;
}
input[type=text]::placeholder, input[type=password]::placeholder {
  color: #aaaaaa;
  position: relative;
  padding: 0;
  transition: all 0.5s ease;
}
input[type=text]:hover::-moz-placeholder, input[type=text]:focus::-moz-placeholder, input[type=password]:hover::-moz-placeholder, input[type=password]:focus::-moz-placeholder {
  padding-top: 3em;
}
input[type=text]:hover:-ms-input-placeholder, input[type=text]:focus:-ms-input-placeholder, input[type=password]:hover:-ms-input-placeholder, input[type=password]:focus:-ms-input-placeholder {
  padding-top: 3em;
}
input[type=text]:hover::placeholder, input[type=text]:focus::placeholder, input[type=password]:hover::placeholder, input[type=password]:focus::placeholder {
  padding-top: 3em;
}

.form h2 {
  text-align: left;
  font-weight: 100;
  color: #fff;
  margin-bottom: 0.5em;
}
.form h4 {
  font-weight: 100;
  color: #fff;
  margin-top: 2em;
}

.login, .signup {
  color: white;
  width: 6em;
  font-family: Lato;
  font-weight: 100;
  font-size: 1.75em;
  border: 1px solid rgba(255, 255, 255, 0.75);
  background: transparent;
  transition: all 200ms ease-in;
  border-radius: 0.65em;
}
.login:hover, .signup:hover, .login:focus, .signup:focus {
  background: #fff;
  color: #f58421;
  border: none;
}

.signup {
  font-size: 1em;
  position: relative;
  left: 1.5em;
}

h1, h2 h3, h4, h5, h6, input {
  font: Lato;
  font-weight: 100;
}
</style>
<body> 	
      <div class="container main">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center title">
            <h1>NATIONAL DAY 115</h1>
            <h3>Security Check</h3>
            <div class="bar"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3 form">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="username"/><br/>
            <input type="password" name="password" placeholder="password"/>
            
            <a class="btn btn-default login" href="#0">login</a>
            </div>
        </div>
        </div>
<div class="tingle-demo tingle-demo-force-close " style="visibility:hidden">

</div>
</body>
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/jquery.easing.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/select2.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/tingle.min.js'?>"></script>
<script src="<?php echo $settings["app"]["homebase"].'/js/easy.qrcode.min.js'?>"></script>
</html>