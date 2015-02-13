<?php
session_start();

function homeRedirect() {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/");
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 60*60*24,
	$params["path"], $params["domain"],
	$params["secure"], $params["httponly"]
);
}

session_destroy();
homeRedirect();
exit;
?>