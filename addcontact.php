<?php
session_start();

require 'query/add_query.php';
require 'query/update_listing.php';
require 'functions.php';
require 'connect/config.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

// An ID is needed to view this page so redirect to home page if not set
if(!isset($_GET['id'])) {
	redirect();
	exit;
}

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$contact = array(
	"First" => '',
	"Last" => '',
	"Email" => '',
	"Job" => '',
	"Phone" => '',
	"Extension" => '');
	
if(isset($_SESSION['Contact'])) {
	$contact = $_SESSION['Contact'];
}

if(isPostSet('first', 'last', 'email', 'job', 'phone', 'extension')) {
	$contact["First"] = $_POST['first'];
	$contact["Last"] = $_POST['last'];
	$contact["Email"] = $_POST['email'];
	$contact["Job"] = $_POST['job'];
	$contact["Phone"] = $_POST['phone'];
	$contact["Extension"] = $_POST['extension'];

	$addContact = addContact($conn,
		$contact['First'],
		$contact['Last'],
		$contact['Email'],
		$contact['Job'],
		$contact['Phone'],
		$contact['Extension'], $id);
		
	setResult($addContact);
	
	if(isset($addContact['Success'])) {
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		$_SESSION['Contact'] = $contact;
		redirect("addcontact.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add a Contact</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php';?>
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
<h2>Add a Contact</h2>
<div class="content">
<p><a href="listing.php?id=<?php echo $id; ?>">Back</a></p>
<form action="addcontact.php?id=<?php echo $id; ?>" method="POST">
<?php contactForm($contact["First"], $contact["Last"], $contact["Email"], $contact["Job"], $contact["Phone"], $contact["Extension"]); ?>
<input type="submit" value="Submit"/>
</form>
</div>
</section>
</body>
</html>