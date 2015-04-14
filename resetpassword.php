<?php
session_start();

require 'php/functions.php';
require 'connect/config.php';
require 'query/account_query.php';

if(isset($_SESSION['Email'])) {
	redirect("account.php");
	exit;
}

if(!isset($_GET['id'])) {
	redirect("forgotpassword.php");
	exit;
}

// htmlspecialchars will not change the code if it is valid
$code = htmlspecialchars($_GET['id']);
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$valid = validCode($conn, $code);

if(is_array($valid) || $valid === false) {
	redirect("forgotpassword.php");
	exit;
}

if(isPostSet('email', 'password', 'confirm')) {
	if($_POST['password'] !== $_POST['confirm']) {
		setMessage("The two passwords do not match.", true);
		redirect("resetpassword.php?id={$code}");
		exit;
	}
	else {
		$updatePassword = resetPassword($conn, $_POST['email'], $code, $_POST['password']);
		setResult($updatePassword);
		$account = verifyAccount($conn, $_POST['email'], $_POST['password']);
		if(isset($account['Verified']) && $account['Verified'] === true) {
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
			setMessage("The email you entered is invalid.", true);
			redirect("resetpassword.php?id={$code}");
			exit;
		}
	}
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
?>
<form action="resetpassword.php?id=<?php echo $code; ?>" method="POST" class="login">
<h3>Reset Password</h3>
<label for="email" >Email: </label>
<input type="text" name="email"><br>
<label for="password" >Password: </label>
<input type="password" name="password"><br>
<label for="confirm">Confirm: </label>
<input type="password" name="confirm"><br>
<p>
Enter your account email and a new password.
</p>
<input type="submit" value="Submit">
</form>
</section>
</body>
</html>