<?php
session_start();

require 'functions.php';
require 'print_error.php';
require 'connect/config.php';
require 'query/verify_account.php';

$account = null;

if(isset($_SESSION['Email'])) {
	redirect();
	exit;
}

if(isset($_POST['email']) and isset($_POST['password'])) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$account = verifyAccount($conn, $_POST['email'], $_POST['password']);
	$conn->close();
	
	if(!isset($account['Error'])) {
		if($account['Verified']) {
			session_regenerate_id(true);
			$_SESSION = array();
			$_SESSION['Email'] = $account['Email'];
			$_SESSION['LoginAttempts'] = $account['LoginAttempts'];
			$_SESSION['Type'] = $account['Type'];
			$_SESSION['Suspended'] = $account['Suspended'];
			redirect();
			exit;
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD - Sign In</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<h2>Sign In</h2>
<div class="content">
<form action="login.php" method="POST">
<table>
	<tr>
		<td>Email: </td>
		<td><input type="text" name="email"></td>
	</tr>
	<tr>
		<td>Password: </td>
		<td><input type="password" name="password"></td>
	</tr>
	<tr>
		<td><input type="submit" value="Sign In"></td>
		<td></td>
	</tr>
</table>
</form>
</div>
<?php
if(!is_null($account)) {
	if(isset($account["Error"]))
		printErrorFromCode($account["Code"]);
	else if(!$account["Verified"])
		printErrorFromCode(7);
}
?>
</section>
</body>
</html>
