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

if(isPostSet('reviewname', 'reviewid', 'delete') && $_SESSION['Type'] === 2) {
	$delete = deleteReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($delete);
	redirect("account.php");
	exit;
}

if(isPostSet('reviewname', 'reviewid', 'suspend') && $_SESSION['Type'] === 2) {
	$suspend = suspendReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($suspend);
	redirect("account.php");
	exit;
}

if(isPostSet('reviewname', 'reviewid', 'validate') && $_SESSION['Type'] === 2) {
	$validate = validateReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($validate);
	redirect("account.php");
	exit;
}

if(isPostSet('accountname', 'delete') && $_SESSION['Type'] === 2) {
	$delete = deleteAccount($conn, $_POST['accountname']);
	setResult($delete);
	redirect("account.php");
	exit;
}

if(isPostSet('accountname', 'suspend') && $_SESSION['Type'] === 2) {
	$suspend = suspendAccount($conn, $_POST['accountname']);
	setResult($suspend);
	redirect("account.php");
	exit;
}

if(isPostSet('accountname', 'validate') && $_SESSION['Type'] === 2) {
	$validate = validateAccount($conn, $_POST['accountname']);
	setResult($validate);
	redirect("account.php");
	exit;
}

$results = myBusinesses($conn, $_SESSION['Email'], $page, $resultsPerPage);
$flaggedReviews = null;
$suspendedReviews = null;
$flaggedAccounts = null;
$suspendedAccounts = null;
if($_SESSION['Type'] === 2) {
	$flaggedReviews = flaggedReviews($conn);
	$suspendedReviews = suspendedReviews($conn);
	$flaggedAccounts = flaggedAccounts($conn);
	$suspendedAccounts = suspendedAccounts($conn);
}
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
		<td><?php echo accountTypeString($_SESSION['Type']); ?></td>
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

if($_SESSION['Type'] === 2){
	echo "<h2>Administrative Functions</h2>";
	echo "<div class='content'>\n";
	
	//Flagged Reviews
	echo "<h4 class='clickable' onmousedown='toggleDisplay(\"flaggedHidden\")'>View Flagged Reviews</h4><hr>\n";
	echo "<div id='flaggedHidden'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"flaggedHidden\");</script>";
	
	$count = 0;
	foreach($flaggedReviews as $review) {
		$name = htmlspecialchars($review['Name']);
		$date = $review['Date'];
		$comment = htmlspecialchars($review['Comment']);
		$rating = $review['Rating'];
		$sp_id = $review['Sp_Id'];
		$count++;
		
echo <<<HTML

<div class="review">
<h4 onmousedown="toggleDisplay('flagreview{$count}')">
{$name} - {$date}
</h4>
<div id="flagreview{$count}">
<hr>
<noscript>{$rating} / 5</noscript>
<script type="text/javascript">
	var stars = new Stars("flagstar{$count}", 5, "{$rating}", false);
	stars.printStars();
</script>
<br>
<p>{$comment}</p>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
</div>
</div>
HTML;
	}
	
	echo "</div>\n";
	
	//Suspended Reviews
	echo "<h4 class='clickable' onmousedown='toggleDisplay(\"suspendedHidden\")'>View Suspended Reviews</h4><hr>\n";
	echo "<div id='suspendedHidden'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"suspendedHidden\");</script>";
	
	$count = 0;
	foreach($suspendedReviews as $review) {
		$name = htmlspecialchars($review['Name']);
		$date = $review['Date'];
		$comment = htmlspecialchars($review['Comment']);
		$rating = $review['Rating'];
		$sp_id = $review['Sp_Id'];
		$count++;
		
echo <<<HTML

<div class="review">
<h4 onmousedown="toggleDisplay('suspendreview{$count}')">
{$name} - {$date}
</h4>
<div id="suspendreview{$count}">
<hr>
<noscript>{$rating} / 5</noscript>
<script type="text/javascript">
	var stars = new Stars("suspendstar{$count}", 5, "{$rating}", false);
	stars.printStars();
</script>
<br>
<p>{$comment}</p>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
</div>
</div>
HTML;
	}
	
	echo "</div>\n";
	
	//Flagged Accounts
	echo "<h4 class='clickable' onmousedown='toggleDisplay(\"flaggedHidden2\")'>View Flagged Accounts</h4><hr>";
	echo "<div id='flaggedHidden2'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"flaggedHidden2\");</script>";
	
	$count = 0;
	foreach($flaggedAccounts as $account) {
		$email = htmlspecialchars($account['Email']);
		$screenname = htmlspecialchars($account['Name']);
		$type = accountTypeString($account['Type']);
		$count++;
		
		echo "<div class='review'>";
		echo "<h4 onmousedown='toggleDisplay(\"flagaccount{$count}\")'>{$screenname}: {$email} - ({$type})</h4><hr>\n";
		echo "<div id='flagaccount{$count}'>\n";
		echo "<ul>";
		
		foreach($flaggedReviews as $review) {
			if($review['Name'] === $account['Name']) {
				echo "<li>" . htmlspecialchars($review['Comment']) . "</li>";
			}
		}
		foreach($suspendedReviews as $review) {
			if($review['Name'] === $account['Name']) {
				echo "<li>" . htmlspecialchars($review['Comment']) . "</li>";
			}
		}
		
		echo "</ul>";
		echo "</div>\n";
		echo "<br>\n";
echo <<<HTML
<script type="text/javascript">
	toggleDisplay("flagaccount{$count}");
</script>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate Account">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend Account">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete Account">
</form>
HTML;
		echo "</div>\n";
	}
	
	echo "</div>\n";
	
	//Suspended Accounts
	echo "<h4 class='clickable' onmousedown='toggleDisplay(\"suspendedHidden2\")'>View Suspended Accounts</h4><hr>";
	echo "<div id='suspendedHidden2'>\n";
	echo "<script type='text/javascript'>toggleDisplay(\"suspendedHidden2\");</script>";
	
	$count = 0;
	foreach($suspendedAccounts as $account) {
		$email = htmlspecialchars($account['Email']);
		$screenname = htmlspecialchars($account['Name']);
		$type = accountTypeString($account['Type']);
		$count++;
		
		echo "<div class='review'>";
		echo "<h4 onmousedown='toggleDisplay(\"suspendaccount{$count}\")'>{$screenname}: {$email} - ({$type})</h4><hr>\n";
		echo "<div id='suspendaccount{$count}'>\n";
		echo "<ul>";
		
		foreach($flaggedReviews as $review) {
			if($review['Name'] === $account['Name']) {
				echo "<li>" . htmlspecialchars($review['Comment']) . "</li>";
			}
		}
		foreach($suspendedReviews as $review) {
			if($review['Name'] === $account['Name']) {
				echo "<li>" . htmlspecialchars($review['Comment']) . "</li>";
			}
		}
		
		echo "</ul>";
		echo "</div>\n";
		echo "<br>\n";
echo <<<HTML
<script type="text/javascript">
	toggleDisplay("suspendaccount{$count}");
</script>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate Account">
</form>
<form action="account.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete Account">
</form>
HTML;
		echo "</div>\n";
	}
	
	echo "</div>\n";
	echo "</div>\n";
}

?>
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