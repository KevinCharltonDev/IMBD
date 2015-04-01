<?php
session_start();

require 'query/account_query.php';
require 'connect/config.php';
require 'php/functions.php';

if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isset($_GET['id'])) {
	$result = requestUpdatePermission($conn, $_GET['id'], $_SESSION['Email']);
	setResult($result);
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Indiana Music Business Directory</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/validate.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<div class="content">
<?php
if(isset($_SESSION['Error'])) {
	printError($_SESSION['Error']['Message']);
	unsetResult();
}
if(isset($_SESSION['Success'])) {
	printMessage($_SESSION['Success']['Message']);
	unsetResult();
}
if(isset($results['Error'])) {
	printError($results["Message"], "index.php");
}

echo "<h3>If you have information that would assist an administrator in determining this business is yours, ";
echo "feel free to contact us at (Email). Otherwise you can <a href='listing.php?id={$_GET['id']}'>Go back.</a></h3> ";
?>
</div>
</section>
</body>
</html>
