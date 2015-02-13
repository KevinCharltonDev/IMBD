<?php
session_start();

function homeRedirect() {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	header("Location: http://$host$uri/");
}

if(isset($_SESSION['Email'])) {
	homeRedirect();
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
			homeRedirect();
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
<?php require 'header.php'; ?>
<article>
	<h1>Indiana Music Business Directory</h1>
</article>
<form action="login.php" method="POST">
		Email: <input type="text" name="email"><br>
		Password: <input type="password" name="password"><br>
		<input type="submit" value="Sign In">
</form>
<footer>
This is the footer.
</footer>
</body>
</html>
