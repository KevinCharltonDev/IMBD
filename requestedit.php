<?php
session_start();

require 'query/add_query.php';
require 'connect/config.php';
require 'php/functions.php';

if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$result = requestPermission($conn, $id, $_SESSION['Email']);
setResult($result);
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
<div class="content">
<h3>If you have information that would assist an administrator in determining if this business is yours, feel free to contact us at (Email). Otherwise, <a href="listing.php?id=<?php echo $id; ?>">click here to go back</a>.</h3>
</div>
</section>
</body>
</html>
