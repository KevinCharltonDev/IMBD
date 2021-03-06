<?php
session_start();

require 'php/functions.php';
require 'php/data.php';
require 'query/account_query.php';
require 'query/review_query.php';
require 'query/business_query.php';
require 'connect/config.php';

if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

if($_SESSION['Type'] !== 2) {
	redirect("account.php");
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isPostSet('reviewname', 'reviewid', 'delete') && $_SESSION['Type'] === 2) {
	$delete = deleteReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($delete);
	redirect("admin.php");
	exit;
}

if(isPostSet('reviewname', 'reviewid', 'suspend') && $_SESSION['Type'] === 2) {
	$suspend = suspendReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($suspend);
	redirect("admin.php");
	exit;
}

if(isPostSet('reviewname', 'reviewid', 'validate') && $_SESSION['Type'] === 2) {
	$validate = validateReview($conn, $_POST['reviewname'], $_POST['reviewid']);
	setResult($validate);
	redirect("admin.php");
	exit;
}

if(isPostSet('accountname', 'delete') && $_SESSION['Type'] === 2) {
	$delete = deleteAccount($conn, $_POST['accountname']);
	setResult($delete);
	redirect("admin.php");
	exit;
}

if(isPostSet('accountname', 'suspend') && $_SESSION['Type'] === 2) {
	$suspend = suspendAccount($conn, $_POST['accountname']);
	setResult($suspend);
	redirect("admin.php");
	exit;
}

if(isPostSet('accountname', 'validate') && $_SESSION['Type'] === 2) {
	$validate = validateAccount($conn, $_POST['accountname']);
	setResult($validate);
	redirect("admin.php");
	exit;
}

if(isPostSet('permissionemail', 'permissionid', 'grant') && $_SESSION['Type'] === 2){
	$grant = grantPermission($conn, $_POST['permissionemail'], $_POST['permissionid']);
	setResult($grant);
	redirect("admin.php");
	exit;
}

if(isPostSet('permissionemail', 'permissionid', 'deny') && $_SESSION['Type'] === 2){
	$deny = denyPermission($conn, $_POST['permissionemail'], $_POST['permissionid']);
	setResult($deny);
	redirect("admin.php");
	exit;
}

if(isPostSet('businessid', 'suspend') && $_SESSION['Type'] === 2){
	$suspend = suspendBusiness($conn, $_POST['businessid']);
	setResult($suspend);
	redirect("admin.php");
	exit;
}

if(isPostSet('businessid', 'validate') && $_SESSION['Type'] === 2){
	$validate = validateBusiness($conn, $_POST['businessid']);
	setResult($validate);
	redirect("admin.php");
	exit;
}

if(isPostSet('changeaccount', 'accountname')) {
	$type = (int) $_POST['changeaccount'];
	setResult(changeAccountType($conn, $_POST['accountname'], $type));
	redirect("admin.php");
	exit;
}


$flaggedReviews = null;
$suspendedReviews = null;
$flaggedAccounts = null;
$suspendedAccounts = null;
if($_SESSION['Type'] === 2) {
	$editRequests = editRequests($conn);
	$flaggedBusinesses = flaggedBusinesses($conn);
	$suspendedBusinesses = suspendedBusinesses($conn);
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
<title>Administrative Functions</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<?php require 'php/css_include.php'; ?>
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
<h2>Administrative Functions</h2>
<div class="content">

<!-- Edit Permission Requests -->
<h4 class="clickable" onmousedown="toggleDisplay('requestHidden')">View Edit Permission Requests</h4><hr>
<div id="requestHidden">
<script type="text/javascript">
	toggleDisplay("requestHidden");
</script>

<?php
$count = 0;
foreach($editRequests as $request){
	$sp_id = htmlspecialchars($request['Sp_Id']);
	$email = htmlspecialchars($request['Email']);
	$name = htmlspecialchars($request['Name']);
	$type = businessTypeString($request['Type']);
	$comment = htmlspecialchars($request['Comment']);
	$count++;
	
echo <<<HTML
	
<div class="review">
<h4>
{$email} - {$name} ({$type})
</h4>
<div id="editrequest{$count}">
<hr>
<p>{$comment}</p>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="permissionemail" value="{$email}">
<input type="hidden" name="permissionid" value="{$sp_id}">
<input type="hidden" name="grant" value="grant">
<input type="submit" value="Grant Permission">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="permissionemail" value="{$email}">
<input type="hidden" name="permissionid" value="{$sp_id}">
<input type="hidden" name="deny" value="deny">
<input type="submit" value="Deny Request">
</form>
</div>
</div>	
HTML;
}

if($count==0){
	echo <<<HTML
<div class="review">
<h4>No permission requests to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Flagged Businesses -->
<h4 class="clickable" onmousedown="toggleDisplay('businessFlaggedHidden')">View Flagged Businesses</h4><hr>
<div id="businessFlaggedHidden">
<script type="text/javascript">
	toggleDisplay("businessFlaggedHidden");
</script>
<?php
$count = 0;
foreach($flaggedBusinesses as $business){
	$sp_id = htmlspecialchars($business['Sp_Id']);
	$name = htmlspecialchars($business['Name']);
	$type = businessTypeString($business['Type']);
	$email = is_null($business['Email']) ? "" : "- Added by: " . htmlspecialchars($business['Email']);
	$description = htmlspecialchars($business['Description']);
	$count++;
	
echo <<<HTML
	
<div class="review">
<h4><a href='listing.php?id={$sp_id}'>{$name} ({$type}) {$email}</a></h4>
<div id="flaggedbusiness{$count}">
<hr>
<p>{$description}</p>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="businessid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="businessid" value="{$sp_id}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend">
</form>
</div>
</div>	
HTML;
}
if($count==0){
	echo <<<HTML
<div class="review">
<h4>No flagged businesses to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Suspended Businesses -->
<h4 class="clickable" onmousedown="toggleDisplay('businessSuspendedHidden')">View Suspended Businesses</h4><hr>
<div id="businessSuspendedHidden">
<script type="text/javascript">
	toggleDisplay("businessSuspendedHidden");
</script>
<?php
$count = 0;
foreach($suspendedBusinesses as $business){
	$sp_id = htmlspecialchars($business['Sp_Id']);
	$name = htmlspecialchars($business['Name']);
	$type = businessTypeString($business['Type']);
	$email = is_null($business['Email']) ? "" : "- Added by: " . htmlspecialchars($business['Email']);
	$description = htmlspecialchars($business['Description']);
	$count++;
	
echo <<<HTML
	
<div class="review">
<h4><a href='listing.php?id={$sp_id}'>{$name} ({$type}) {$email}</a></h4>
<div id="suspendedbusiness{$count}">
<hr>
<p>{$description}</p>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="businessid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
</div>
</div>	
HTML;
}
if($count==0){
	echo <<<HTML
<div class="review">
<h4>No suspended businesses to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Flagged Reviews -->
<h4 class="clickable" onmousedown="toggleDisplay('flaggedHidden')">View Flagged Reviews</h4><hr>
<div id="flaggedHidden">
<script type="text/javascript">
	toggleDisplay("flaggedHidden");
</script>
<?php 
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
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$name}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend Account">
</form>
</div>
</div>
HTML;
}

if($count==0){
	echo <<<HTML
<div class="review">
<h4>No flagged reviews to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Suspended Reviews -->
<h4 class="clickable" onmousedown="toggleDisplay('suspendedHidden')">View Suspended Reviews</h4>
<hr>
<div id="suspendedHidden">
<script type="text/javascript">
	toggleDisplay("suspendedHidden");
</script>
<?php
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
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="reviewname" value="{$name}">
<input type="hidden" name="reviewid" value="{$sp_id}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$name}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend Account">
</form>
</div>
</div>
HTML;
}

if($count==0){
	echo <<<HTML
<div class="review">
<h4>No suspended reviews to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Flagged Accounts -->
<h4 class="clickable" onmousedown="toggleDisplay('flaggedHidden2')">View Flagged Accounts</h4>
<hr>
<div id="flaggedHidden2">
<script type="text/javascript">
	toggleDisplay("flaggedHidden2");
</script>

<?php
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
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate Account">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="suspend" value="suspend">
<input type="submit" value="Suspend Account">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete Account">
</form>
<br>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="changeaccount" value="0">
<input type="submit" value="Normal Account">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="changeaccount" value="1">
<input type="submit" value="Student Account">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="changeaccount" value="2">
<input type="submit" value="Moderator Account">
</form>
HTML;
	echo "</div>\n";
}

if($count==0){
	echo <<<HTML
<div class="review">
<h4>No flagged accounts to display.</h4>
</div>
HTML;
}
?>
</div>

<!-- Suspended Accounts -->
<h4 class="clickable" onmousedown="toggleDisplay('suspendedHidden2')">View Suspended Accounts</h4>
<hr>
<div id="suspendedHidden2">
<script type="text/javascript">
	toggleDisplay("suspendedHidden2");
</script>

<?php
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
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="validate" value="validate">
<input type="submit" value="Validate Account">
</form>
<form action="admin.php" method="POST" style="display: inline;">
<input type="hidden" name="accountname" value="{$screenname}">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete Account">
</form>
HTML;
	echo "</div>\n";
}

if($count==0){
	echo <<<HTML
<div class="review">
<h4>No suspended accounts to display.</h4>
</div>
HTML;
}
?>
</div>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>