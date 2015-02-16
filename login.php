<?php
session_start();

require 'functions.php';
require 'print_error.php';

$results = null;

if(isset($_SESSION['Email'])) {
	redirect();
	exit;
}

require 'query/verify_account.php';

if(isset($_POST['email']) and isset($_POST['password'])) {
	$results = verifyAccount($_POST['email'], $_POST['password']);
	if(!isset($results['Error'])) {
		if($results['Verified']) {
			session_regenerate_id(true);
			$_SESSION = array();
			$_SESSION['Email'] = $results['Email'];
			$_SESSION['LoginAttempts'] = $results['LoginAttempts'];
			$_SESSION['Type'] = $results['Type'];
			$_SESSION['Suspended'] = $results['Suspended'];
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
if(!is_null($results)) {
	if(isset($results["Error"]))
		printErrorFromCode($results["Code"]);
	else if(!$results["Verified"])
		printLoginError();
}
?>
</section>
</body>
</html>
