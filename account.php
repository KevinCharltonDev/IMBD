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
<title>My Account</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
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
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$account = accountInfo($conn, $_SESSION['Email']);

if(isset($_POST['suspendemail'], $_POST['suspendid'])){
	$suspend = suspendReview($conn, $_POST['suspendemail'], $_POST['suspendid']);
	if(isset($suspend['Error'])) {
		printError($suspend['Message']);
	}
	else if(isset($suspend['Success'])) {
		printMessage($suspend['Message']);
	}
}

if(isset($_POST['suspendaccount'])){
	$suspend = suspendAccount($conn, $_POST['suspendaccount']);
	if(isset($suspend['Error'])) {
		printError($suspend['Message']);
	}
	else if(isset($suspend['Success'])) {
		printMessage($suspend['Message']);
	}
}

if($account['Type']!=0){
	echo "<h2>Administrative functions</h2>";
	
	//Review administrative functions
	echo "<div class='content'>\n";
	echo "<h4 style='cursor:pointer' onmousedown='toggleDisplay(\"flaggedHidden\")'>View flagged reviews</h4>";
	echo "<div id='flaggedHidden'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"flaggedHidden\");</script>";
	
	$flaggedReviews = flaggedReviews($conn);
	
	foreach($flaggedReviews as $review) {
		$email = $review['Email'];
		$comment = $review['Comment'];
		$spid = $review['Sp_Id'];
		
		echo "<form action='account.php' method='POST'>\n";
		echo "<input type='text' name='suspendemail' value='{$email}' hidden>";
		echo "<input type='text' name='suspendid' value='{$spid}' hidden>";
		echo "<h3>By: {$email}</h3><hr>\n";
		echo "<div class = 'review'>{$comment}</div><input type='submit' value='Suspend comment'></form><br>\n";
	}
	
	echo "</div>\n";
	echo "</div><hr>\n";
	
	//Account administrative functions
	echo "<div class='content'>\n";
	echo "<h4 style='cursor:pointer' onmousedown='toggleDisplay(\"flaggedHidden2\")'>View flagged accounts</h4>";
	echo "<div id='flaggedHidden2'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"flaggedHidden2\");</script>";
	
	$flaggedAccounts = flaggedAccounts($conn);
	
	foreach($flaggedAccounts as $accounts) {
		$email = $accounts['Email'];
		$screenname = $accounts['Name'];
		$usersFlagged = usersFlaggedReviews($conn, $email);
		
		echo "<form action='account.php' method='POST'>\n";
		echo "<input type='text' name='suspendaccount' value='{$email}' hidden>";
		echo "<p><u>{$screenname}</u></p>";
		echo "<div><p>Comments under consideration</p>";
		echo "<div style='cursor:pointer' onmousedown='toggleDisplay(\"flaggedHidden{$screenname}\")'>►</div>";
		echo "<div class = 'review' id='flaggedHidden{$screenname}'>\n";
		foreach($usersFlagged as $review){
			$comment = $review['Comment'];
			echo "- " . $comment . "<hr>";
		}
		echo "<script type='text/javascript'>toggleDisplay(\"flaggedHidden{$screenname}\");</script>";
		echo "</div>\n";
		echo "</div>\n";
		echo "<br><input type='submit' value='Suspend user'></form><hr>\n";
	}
	
	echo "</div>\n";
	echo "</div><hr>\n";
}

$conn->close();
?>
</section>
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