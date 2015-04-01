<?php
session_start();

require 'php/functions.php';
require 'php/data.php';
require 'query/account_query.php';
require 'connect/config.php';
require 'query/review_query.php';

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

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isset($_POST['oldpassword'], $_POST['newpassword'])) {
	$updatePassword = updatePassword($conn, $_SESSION['Email'], $_POST['oldpassword'], $_POST['newpassword']);
	setResult($updatePassword);
	redirect("account.php");
	exit;
}

$results = myBusinesses($conn, $_SESSION['Email'], $page, $resultsPerPage);
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Account</title>
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
if(isset($results['Error'])) {
	printError($results['Message']);
}
?>
<h2>My Account</h2>
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
	<tr>
		<td>Type: </td>
		<td><?php
			if($_SESSION['Type'] === 2) {
				echo '<a href="admin.php">' . accountTypeString($_SESSION['Type']) . '</a>';
			}
			else {
				echo accountTypeString($_SESSION['Type']);
			}
		?></td>
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
if(!isset($results['Error'])) {
	$count = count($results);
	if($count > 0) {
		echo "<h2>Businesses I've Added</h2>";
		echo "<div class='content'>\n";
		foreach($results as $result) {
			$id = htmlspecialchars($result['Id']);
			$name = htmlspecialchars($result['Name']);
			$type = htmlspecialchars(businessTypeString($result['Type']));
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