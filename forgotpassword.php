<?php
session_start();

require 'php/functions.php';
require 'connect/config.php';
require 'query/account_query.php';

if(isset($_SESSION['Email'])) {
	redirect();
	exit;
}

if(isPostSet('email')) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$iv = base64_encode(mcrypt_create_iv(48, MCRYPT_DEV_URANDOM));
	$iv = str_replace('+', '-', str_replace('/', '_', $iv));
	$link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?id=" . $iv;
	$account = resetCode($conn, $_POST['email'], $iv);
	$conn->close();
	
	setMessage("If the email address you entered is valid, an email will be sent allowing you to change your password.", FALSE);
	
	if(isset($account['Error'])) {
		redirect("login.php");
		exit;
	}
	
	$to      = $_POST['email'];
	$subject = 'Reset Password for IMBD';
	$message = "<html><body><h4>Indiana Music Business Directory - Reset Password</h4>" .
		"Click the link below to sign in and change your password.<br>" .
		"<a href='{$link}'>{$link}</a><br></body></html>";
	$headers = 'From: Indiana Music Business Directory <noreply@willshare.com>' . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" .
		'Content-type: text/html; charset=iso-8859-1';

	mail($to, $subject, $message, $headers);
	
	redirect("login.php");
	exit;
}

if(isset($_GET['id'])) {
	$code = htmlspecialchars($_GET['id']);
	redirect("resetpassword.php?id={$code}");
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>
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
<form action="forgotpassword.php" method="POST" class="login">
<h3>Reset Password</h3>
<label for="email">Email:</label>
<input type="text" name="email"><br>
<p>This must be the same email used for your account.  A link will be sent allowing you to change your password.</p>
<input type="submit" value="Send Link to Email">
</form>
</section>
<?php $position = "absolute"; include 'php/footer.php'; ?>
</body>
</html>