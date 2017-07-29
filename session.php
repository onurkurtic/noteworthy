<?php
	session_start();

	if($_SESSION["email"]){
		echo "You are successfully logged into your account";
	} else {
		header ("Location: index.php");
	}



?>