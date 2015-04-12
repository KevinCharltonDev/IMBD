<?php
session_start();

require 'php/functions.php';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 60*60*24,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]);
}

session_destroy();
redirect();
exit;
?>
<!DOCTYPE html>
<html>
<head>
<title>Sign Out</title>
</head>
<body>
</body>
</html>