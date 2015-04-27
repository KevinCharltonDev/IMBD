<?php
session_start();

require 'query/look_up_query.php';
require 'query/update_listing.php';
require 'query/service_query.php';
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
$id = (int) $_GET['id'];

// User must have update permission to view this page
$hasPermission = hasUpdatePermission($conn, $id, $_SESSION['Email'], $_SESSION["Type"]);
if($hasPermission !== true) {
	redirect("listing.php?id={$id}");
	exit;
}

$results = lookUp($conn, $id);
$services = getServices($conn, $id);
$allServices = getAllServices($conn);

if(isset($_POST['name'], $_POST['type'], $_POST['description'], $_POST['websites'])) {
	$business = businessFromPost();
	
	$update = update($conn, $id, $business);
	setResult($update);
	// If update was successful, redirect to business page
	if(isset($update["Success"])) {
		$selectedServices = isPostSet('services') ? servicesFromPost() : array();
		$hasServices = false;
		
		// Services to reject
		foreach($services as $service) {
			$found = false;
			foreach($selectedServices as $selectedService) {
				if($service['Name'] === $selectedService['Name']) {
					$found = true;
				}
			}
			
			if(!$found) {
				rejectService($conn, $service['Name'], $id);
			}
		}
		
		//Services to choose
		foreach($selectedServices as $selectedService) {
			$found = false;
			foreach($services as $service) {
				if($selectedService['Name'] === $service['Name']) {
					$found = true;
				}
			}
			
			if(!$found) {
				$hasServices = true;
				chooseService($conn, $selectedService['Name'], $id);
			}
		}
		
		if($hasServices) {
			redirect("updateservice.php?id={$id}");
			exit;
		}
		
		redirect("listing.php?id={$id}");
		exit;
	}
	else {
		redirect("update.php?id={$id}");
		exit;
	}
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>
<?php
if(!isset($results["Error"])) {
	echo htmlspecialchars($results["Data"]["Name"]);
}
else {
	echo "Indiana Music Business Directory";
}
?>
</title>
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

$name = htmlspecialchars($results["Data"]["Name"]);

echo "<h2>{$name}</h2>\n";
echo "<div class='content'>\n";
echo "<p><a href='listing.php?id={$id}'>Back</a></p>";
echo "<h3>Information</h3>\n";
echo "<form action='update.php?id={$id}' method='POST'>\n";
businessForm($results['Data'], $allServices, $services);
echo "<hr>\n";
echo '<input type="submit" value="Submit">';
echo "</form>\n";
echo "</div>\n";
?>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>