<?php
session_start();

require 'query/account_query.php';

if(isset($_POST['oldpassword']))
	$oldpassword = $_POST['oldpassword'];
if(isset($_POST['newpassword']))
	$newpassword = $_POST['newpassword'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
updatePassword($_SESSION['Email'], $oldpassword, $newpassword);
?>
</section>
</body>
</html>