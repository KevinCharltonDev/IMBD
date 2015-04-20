<?php
session_start();

require 'php/functions.php';
require 'php/data.php';
require 'query/account_query.php';
require 'query/review_query.php';
require 'query/search_query.php';
require 'query/service_query.php';
require 'connect/config.php';

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "account.php";
	redirect("login.php");
	exit;
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isPostSet('oldpassword', 'newpassword', 'confirm')) {
	if($_POST['newpassword'] !== $_POST['confirm']) {
		setMessage("The two passwords you entered do not match.", true);
	}
	else {
		$updatePassword = updatePassword($conn, $_SESSION['Email'], $_POST['oldpassword'], $_POST['newpassword']);
		setResult($updatePassword);
	}
	redirect("account.php");
	exit;
}

if(isPostSet('delete')) {
	$deleteAccount = deleteAccount($conn, $_SESSION['ScreenName']);
	setResult($deleteAccount);
	redirect("logout.php");
	exit;
}

if(isPostSet('screenname')) {
	$updateScreenName = updateScreenName($conn, $_SESSION['Email'], trim($_POST['screenname']));
	setResult($updateScreenName);
	
	if(isset($updateScreenName['Success'])) {
		$_SESSION['ScreenName'] = substr(trim($_POST['screenname']), 0, 30);
	}
	
	redirect("account.php");
	exit;
}

$results = myBusinesses($conn, $_SESSION['Email']);
$count = count($results);
for($i = 0; $i < $count; $i++) {
	$id = (int) $results[$i]['Sp_Id'];
	$results[$i]['Services'] = getServices($conn, $id);
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Account</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
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
<hr>
<h3>Change Password</h3>
<form action='account.php' method='POST'>
Old Password<br>
<input type='password' name='oldpassword'>
<br><br>
New Password<br>
<input type='password' name='newpassword'>
<br>
Confirm Password<br>
<input type='password' name='confirm'><br>
<input type='submit' value='Submit'>
</form>
<hr>
<br>
<h3>Change Screen Name</h3>
<form action='account.php' method='POST'>
<input type="text" name="screenname"><br>
<input type="submit" value="Change">
</form>
<hr>
<br>
<h3>Delete Account</h3>
<form action='account.php' method='POST' onsubmit="return window.confirm('Are you sure you want to delete your account?\nYour reviews will be deleted, and you will lose \npermission to update businesses.');">
<input type="hidden" name="delete" value="delete">
<input type="submit" value="Delete">
</form>
<hr>
</div>
</section>
<section>
<?php if(!isset($results['Error']) && count($results) > 0): ?>
<h2>Businesses I've Added</h2>
<div class="content">
<?php foreach($results as $result):

$id = (int) $result['Sp_Id'];
$name = htmlspecialchars($result['Name']);
$type = businessTypeString($result['Type']);
$description = htmlspecialchars($result['Description']); ?>
<h3>
<a href="listing.php?id=<?php echo $id; ?>"><?php echo $name . " (" . $type . ")"; ?></a>
</h3>
<div class="ListingBounds">
<?php
echo $description;

if(count($result['Services']) > 0)
		echo '<div style="font-style: italic;margin-top: 10px;">';
	
	for($i = 0; $i < count($result['Services']); $i++) {
		$name = htmlspecialchars($result['Services'][$i]['Name']);
		if($i > 0)
			echo " | ";
		
		echo $name;
	}
	
	if(count($result['Services']) > 0)
		echo "</div>";
?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>