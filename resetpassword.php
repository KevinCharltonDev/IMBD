<?php
session_start();

require 'php/functions.php';
require 'connect/config.php';
require 'query/account_query.php';

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "account.php";
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isset($_GET['email']) AND isset($_GET['id']) AND isPostSet('resetpw')) {
	if($_POST['resetpw'] !== $_POST['confirm']) {
		setMessage("The two passwords you entered do not match.", true);
	}
	else {
		$updatePassword = resetPassword($conn, $_GET['email'], $_GET['id'], $_POST['resetpw']);
		setResult($updatePassword);
	}
	redirect("resetpassword.php?email=" . $_GET['email'] . "&id=" . $_GET['id']);
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Indiana Music Business Directory</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
<script src="js/validate.js"></script>
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

echo "<form action='resetpassword.php?email={$_GET['email']}&id={$_GET['id']}' method='POST' class='login'>
<h3>Reset Password.</h3>
<label for='password' >Password:</label>
<input type='text' name='resetpw'><br>
<label for='password' >Confirm:</label>
<input type='text' name='confirm'><br>
<input type='submit' value='Reset'>
</form>";
?>
</section>
</body>
</html>