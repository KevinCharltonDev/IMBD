<?php
session_start();

require 'query/account_query.php';

if(isset($_POST['screenname']))
	$screenname = trim($_POST['screenname']);
if(isset($_POST['email']))
	$email = trim($_POST['email']);
if(isset($_POST['password']))
	$password = $_POST['password'];
if(isset($_POST['squestion']))
	$squestion = $_POST['squestion'];
if(isset($_POST['sanswer']))
	$sanswer = $_POST['sanswer'];

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
createAccount($screenname, $email, $password, $squestion, $sanswer);
?>
</section>
</body>
</html>