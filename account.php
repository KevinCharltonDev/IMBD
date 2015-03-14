<?php
session_start();

require 'functions.php';
require 'query/account_query.php';
require 'connect/config.php';

if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$updatePassword = null;

if(isset($_POST['oldpassword'], $_POST['newpassword'])) {
	$email = $_SESSION['Email'];
	$oldpassword = $_POST['oldpassword'];
	$newpassword = $_POST['newpassword'];
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$updatePassword = updatePassword($conn, $email, $oldpassword, $newpassword);
	$conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<h2>My Account</h2>
<?php
if(!is_null($updatePassword)) {
	if(isset($updatePassword['Error'])) {
		printError($updatePassword['Message']);
	}
	else if(isset($updatePassword['Success'])) {
		printMessage($updatePassword['Message']);
	}
}
?>
<div class='content'>
<table>
	<tr>
		<td>Email: </td>
		<td><?php echo htmlspecialchars($_SESSION['Email']); ?></td>
	</tr>
</table>
<table>
	<form action='account.php' method='POST'>
	<br>
	<tr>
		<td>Update Password</td>
	</tr>
	<tr>
		<td>Old password: </td>
		<td><input type='password' name='oldpassword'></td>
	</tr>
	<tr>
		<td>New password: </td>
		<td><input type='password' name='newpassword'></td>
	</tr>
	<tr>
		<td><input type='submit' value='Submit'></td>
	</tr>
	</form>
</table>
</form>
</div>
</section>
</body>
</html>