<?php
session_start();

require 'query/account_query.php';
require 'connect/config.php';
require 'php/functions.php';

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "createaccount.php";
}

if(isPostSet('screenname', 'email', 'password', 'confirm')) {
	$screenname = trim($_POST['screenname']);
	$email = trim($_POST['email']);
	$password = $_POST['password'];
	$confirmpassword = $_POST['confirm'];
	
	if($password === $confirmpassword) {
		$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
		$createAccount = createAccount($conn, $screenname, $email, $password);
		setResult($createAccount);
		
		if(isset($createAccount['Success'])) {
			$account = verifyAccount($conn, $email, $password);
			session_regenerate_id(true);
			$_SESSION = array();
			$_SESSION['Email'] = $account['Email'];
			$_SESSION['ScreenName'] = $account['ScreenName'];
			$_SESSION['LoginAttempts'] = $account['LoginAttempts'];
			$_SESSION['Type'] = $account['Type'];
			$_SESSION['Suspended'] = $account['Suspended'];
			$conn->close();
			redirect("account.php");
			exit;
		}
		else {
			$conn->close();
			redirect("createaccount.php");
			exit;
		}
	}
	else {
		setMessage("The two passwords you entered do not match.", true);
		redirect("createaccount.php");
		exit;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Create an Account</title>
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
<h2>Create an account.</h2>
<div class='content'>
<form action='createaccount.php' method='POST'>
<table>
	<tr>
		<td>Screen Name: </td>
		<td><input type='text' name='screenname'></td>
	</tr>
	<tr>
		<td>Email: </td>
		<td><input type='text' name='email'></td>
	</tr>
	<tr>
		<th colspan="2">Please do not use your password from another site.</th>
	</tr>
	<tr>
		<td>Password: </td>
		<td><input type='password' name='password'></td>
	</tr>
	<tr>
		<td>Confirm Password: </td>
		<td><input type='password' name='confirm'></td>
	</tr>
	<tr>
		<td><input type='submit' value='Submit'></td>
		<td>&nbsp;</td>
	</tr>
</table>
</form>
</div>
</section>
</body>
</html>