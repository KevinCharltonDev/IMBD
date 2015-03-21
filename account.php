<?php
session_start();

require 'functions.php';
require 'query/account_query.php';
require 'connect/config.php';
$page = 1;
$resultsPerPage = 10;

if(isset($_GET['page']))
	$page = (int) $_GET['page'];
if($page < 1)
	$page = 1;
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$updatePassword = null;
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isset($_POST['oldpassword'], $_POST['newpassword'])) {
	$email = $_SESSION['Email'];
	$oldpassword = $_POST['oldpassword'];
	$newpassword = $_POST['newpassword'];
	$updatePassword = updatePassword($conn, $email, $oldpassword, $newpassword);
}

$results = myBusinesses($conn, $_SESSION['Email'], $page, $resultsPerPage);
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<h2>My Account</h2>
<?php
if(!is_null($updatePassword)) {
	if(isset($updatePassword['Error'])) {
		printError($updatePassword['Message']);
	}
	else if(isset($updatePassword['Success'])) {
		printMessage($updatePassword['Message']);
	}
}
?>
<div class='content'>
<table>
	<tr>
		<td>Email: </td>
		<td><?php echo htmlspecialchars($_SESSION['Email']); ?></td>
	</tr>
	<tr>
		<td>Screen Name: </td>
		<td><?php echo htmlspecialchars($_SESSION['ScreenName']); ?></td>
	</tr>
</table>
<form action='account.php' method='POST'>
<h3>Change Password</h3>
<table>
	<tr>
		<td>Old password: </td>
		<td><input type='password' name='oldpassword'></td>
	</tr>
	<tr>
		<td>New password: </td>
		<td><input type='password' name='newpassword'></td>
	</tr>
	<tr>
		<td><input type='submit' value='Submit'></td>
		<td>&nbsp;</td>
	</tr>
</table>
</form>
</div>
</section>
<br/>
<section>
<?php
if(isset($results['Error'])) {
	printError($results["Message"]);
}
else {
	$count = count($results);
	if($count > 0) {
		echo "<h2>Businesses I've added</h2>";
		echo "<div class='content'>\n";
		foreach($results as $result) {
			$id = htmlspecialchars($result['Id']);
			$name = htmlspecialchars($result['Name']);
			$type = htmlspecialchars(spTypeToString($result['Type']));
			$description = htmlspecialchars($result['Description']);
			
			echo "<h3><a href='listing.php?id={$id}'>{$name} ({$type})</a></h3>\n";
			echo "<div class='ListingBounds'>";
			echo "<p>{$description}</p>\n";
			echo "</div>";
		}
		if($page > 1) {
			$prevPage = $page - 1;
			$prevLink = htmlspecialchars("account.php?page={$prevPage}");
			echo HTMLTag::create("a")->attribute("href", $prevLink)->innerHTML("Previous Page")->html();
			echo '&nbsp;&nbsp;';
		}
		if($count === $resultsPerPage) {
			$nextPage = $page + 1;
			$nextLink = htmlspecialchars("account.php?page={$nextPage}");
			echo HTMLTag::create("a")->attribute("href", $nextLink)->innerHTML("Next Page")->html();
		}
		
		echo "</div>\n";
	}
}
?>
</section>
</body>
</html>