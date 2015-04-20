<?php
session_start();

require 'query/account_query.php';
require 'connect/config.php';
require 'php/functions.php';

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "createaccount.php";
}

if(isset($_SESSION['Email'])) {
	redirect("account.php");
}

if(isPostSet('screenname', 'email', 'password', 'confirm')) {
	$screenname = trim($_POST['screenname']);
	$email = trim($_POST['email']);
	$password = $_POST['password'];
	$confirmpassword = $_POST['confirm'];
	$flagged = isPostSet('student');
	
	if($password === $confirmpassword) {
		$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
		$createAccount = createAccount($conn, $screenname, $email, $password, $flagged);
		
		if(isset($createAccount['Success'])) {
			$account = verifyAccount($conn, $email, $password);
			session_regenerate_id(true);
			$_SESSION = array();
			setResult($createAccount);
			$_SESSION['Email'] = $account['Email'];
			$_SESSION['ScreenName'] = $account['ScreenName'];
			$_SESSION['Type'] = $account['Type'];
			$_SESSION['Suspended'] = $account['Suspended'];
			$conn->close();
			redirect("account.php");
			exit;
		}
		else {
			$conn->close();
			setResult($createAccount);
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
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<?php require 'php/css_include.php'; ?>
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
<h2>Create an Account</h2>
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
</table>
<br>
<h3>Please do not use your password from another site.</h3>
<table>
	<tr>
		<td>Password: </td>
		<td><input type='password' name='password'></td>
	</tr>
	<tr>
		<td>Confirm Password: </td>
		<td><input type='password' name='confirm'></td>
	</tr>
</table>
<br>
<input type="checkbox" name="student">
Check this box if you are a student involved with this project at Ball State University.
<hr>
<input type="submit" value="Submit">
</form>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>