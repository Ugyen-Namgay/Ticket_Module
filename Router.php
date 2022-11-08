<?php

class Router{

	private $request;
	private $rootfolder;

	public function __construct($request){
		$this->request = $request;
		$this->rootfolder = parse_ini_file("settings/config.ini", true)["app"]["homebase"];
	}

	public function get($route, $file){
		//print_r($route."<br>");
		$uri = ltrim( $this->request, $this->rootfolder."/" );
		// print_r($this->request);
		// echo "<br>";
		$uri = explode("/", $uri);
		// print_r($uri);
		// echo "<br>";
		if($uri[0] == ltrim($route, $this->rootfolder."/")){

			array_shift($uri);
			$args = $uri;
			
			require $file . '.php';
			

		}
		
		

	}

}

function Redirect($url, $permanent = false) {
 header('Location: ' . $url, true, $permanent ? 301 : 302);
 exit();
 }