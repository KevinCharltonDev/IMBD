<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'php/functions.php';
require 'php/data.php';
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

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$sp_id = (int) $_GET['id'];

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $sp_id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$sp_id}");
	exit;
}

$contacts = contacts($conn, $sp_id);

if(isPostSet('cid', 'first', 'last', 'email', 'job', 'phone', 'extension')) {
	$c_id = (int) $_POST['cid'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$email = $_POST['email'];
	$job = $_POST['job'];
	$phone = $_POST['phone'];
	$extension = $_POST['extension'];
	
	$hasContactPermission = hasContactUpdatePermission($conn, $c_id, $_SESSION['Email'], $_SESSION["Type"]);
	if($hasContactPermission === true) {
		$updateContact = updateContact($conn, $first, $last, $email, $job, $phone, $extension, $c_id);
		setResult($updateContact);
		
		if(isset($updateContact['Success'])) {
			redirect("listing.php?id={$sp_id}");
			exit;
		}
		else {
			redirect("updatecontact.php?id={$sp_id}");
			exit;
		}
	}
	else if($hasContactPermission === false) {
		setMessage("You do not have permission to update this contact.", true);
		redirect("listing.php?id={$sp_id}");
		exit;
	}
	else {
		setResult($hasContactPermission);
		redirect("updatecontact.php?id={$sp_id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Update Contacts</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
</head>
<body>
<?php require 'php/header.php';?>
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

echo "<h2>Update Contacts</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$sp_id}'>Back</a></p>";
$count = count($contacts);

foreach($contacts as $i => $contact) {
	$c_id = (int) $contact["C_Id"];
	$n = $i + 1;
	echo "<form action='updatecontact.php?id={$sp_id}' method='POST' id='contact{$n}'>\n";
	echo "<h3>Contact {$n}</h3>\n";
	contactForm(contactFromData($contact['First'], $contact['Last'], $contact['Email'], $contact['Job'], $contact['Phone'], $contact['Extension']));
	echo "<input type='hidden' name='cid' value='{$c_id}'/>\n";
	
	$prev = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Previous")->
		attribute("onclick", "showPrev('contact', {$n});");
	if($i == 0)
		$prev->attribute("disabled", "disabled");
	
	echo $prev->html();
		
	$next = HTMLTag::create("input", true, true)->
		attribute("type", "button")->
		attribute("value", "Next")->
		attribute("onclick", "showNext('contact', {$n});");
	if($i == $count - 1)
		$next->attribute("disabled", "disabled");
	
	echo $next->html();
	
	echo "<br/>\n";
	echo "<br/>\n";
	echo '<input type="submit" value="Submit"/>';
	echo "</form>\n";
}
?>
<script type="text/javascript">
toggleMultipleDisplay("contact", 2, <?php echo $count; ?>);
</script>
</div>
</section>
</body>
</html>