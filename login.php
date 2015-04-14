<?php
session_start();

require 'php/functions.php';
require 'connect/config.php';
require 'query/account_query.php';

if(isset($_SESSION['Email'])) {
	redirect();
	exit;
}

if(isPostSet('email', 'password')) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$account = verifyAccount($conn, $_POST['email'], $_POST['password']);
	$conn->close();
	
	if(isset($account['Error'])) {
		setResult($account);
		redirect("login.php");
		exit;
	}
	
	if($account['Verified'] === true) {
		$redirect = isset($_SESSION['Redirect']) ? $_SESSION['Redirect'] : "";
		session_regenerate_id(true);
		$_SESSION = array();
		$_SESSION['Email'] = $account['Email'];
		$_SESSION['ScreenName'] = $account['ScreenName'];
		$_SESSION['Type'] = $account['Type'];
		$_SESSION['Suspended'] = $account['Suspended'];
		redirect($redirect);
		exit;
	}
	else {
		setMessage("Email or password is incorrect", true);
		redirect("login.php");
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sign In</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<?php
if(isset($_SESSION['Error'])) {
	printError($_SESSION['Error']['Message']);
	unsetResult();
}
if(isset($_SESSION['Success'])) {
	printMessage($_SESSION['Success']['Message']);
	unsetResult();
}
?>
<form action="login.php" method="POST" class="login">
<h3>Sign In</h3>
<label for="email">Email:</label>
<input type="text" name="email"><br>
<label for="password">Password:</label>
<input type="password" name="password"><br>
<div>
<a href="forgotpassword.php">Forgot Password?</a><br>
<a href="createaccount.php">Create an Account</a>
</div>
<input type="submit" value="Sign In">
</form>
</section>
</body>
</html>
