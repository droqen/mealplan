<?php

require_once '.password.php';

// check user login
session_start();
if ($_POST["logout"]) {
	$_SESSION["user"]="";
	require_once 'echo_login_form.php';
	die();
} else if ($_SESSION["user"]=="droqen") {
	// yay!
} else if ($_POST["password"]==$PASSWORD) {
	$_SESSION["user"]="droqen"; // yay!
} else {
	require_once 'echo_login_form.php';
	die();
}

require_once '.credentials.php'; 
$dsn = "mysql:host=$dbhost;dbname=$dbname";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
	$conn = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
	die("<b>Problem!</b> PDO connection failed: " . $e->getMessage() . "<br/>");
}
