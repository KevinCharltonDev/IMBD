<?php
session_start();

require 'query/add_query.php';
require 'query/look_up_query.php';
require 'connect/config.php';
require 'php/functions.php';

if(!isset($_GET['id'])) {
	redirect();
	exit;
}

if(!isset($_SESSION['Email'])) {
	redirect("login.php");
	exit;
}

$id = (int) $_GET['id'];
$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isPostSet('id', 'comment')) {
	$post_id = (int) $_POST['id'];
	$result = requestPermission($conn, $post_id, $_SESSION['Email'], $_POST['comment']);
	setResult($result);
	redirect("listing.php?id={$post_id}");
	exit;
}

$business = businessData($conn, $id);
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Indiana Music Business Directory</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/validate.js"></script>
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
if(isset($business['Error'])) {
	printError("The business information could not be found.");
}
else {
?>

<h2>Request Update Permission</h2>
<div class="content">
<p><a href="listing.php?id=<?php echo $id; ?>">Back</a></p>
<h3><?php echo htmlspecialchars($business['Name']); ?></h3>
Please enter any information that will help an administrator determine if this business is yours.<br>
<form action="requestedit.php?id=<?php echo $id; ?>" method="POST">
<textarea name="comment" maxlength="255">
</textarea>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="submit" value="Submit">
</form>
</div>
<?php
}
?>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>