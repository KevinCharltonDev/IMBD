<?php
session_start();

require 'php/functions.php';
require 'connect/config.php';
require 'query/account_query.php';

if(isset($_SESSION['Email'])) {
	redirect();
	exit;
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
if(isPostSet('rpemail')) {
	$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
	$iv = base64_encode(mcrypt_create_iv(48, MCRYPT_DEV_URANDOM));
	$iv = str_replace('+', '-', str_replace('/', '_', $iv));
	$link = "resetpw.php?email=" . $_POST['rpemail'] ."&id=" . $iv;
	$account = resetCode($conn, $_POST['rpemail'], $iv);
	$conn->close();
	
	if(isset($account['Error'])) {
		setResult($account);
		redirect("requestreset.php");
		exit;
	}
	
	$to      = $_POST['rpemail'];
	$subject = 'IMBD Reset Password Link';
	$message = $link;
	$headers = 'From: noreply@willshare.com' . "\r\n" .
    'Reply-To: noreply@willshare.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers, $params);
	
	setMessage("If the email you provided is valid, an email with a link will be sent to it shortly. Follow the link to change your password.", FALSE);
	redirect("requestreset.php");
	exit;
}
else{
	echo "<form action='requestreset.php' method='POST' class='login'>
<h3>Reset Password.</h3>
<label for='email'>Email:</label>
<input type='text' name='rpemail'><br>
<input type='submit' value='Send Reset Link'>
</form>";
}
?>
</section>
</body>
</html>