<?php 
session_start();

require 'connect/config.php';
require 'query/service_query.php';
require 'php/functions.php';

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(!isset($_SESSION['Email']) || $_SESSION['Type'] !== 2) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

if(isPostSet('service', 'column')) {
	$serviceName = $_POST['service'];
	$columnName = $_POST['column'];
	deleteColumn($conn, $serviceName, $columnName);
	redirect("_delete_service.php");
	exit;
}
else if(isPostSet('service')) {
	$serviceName = $_POST['service'];
	deleteService($conn, $serviceName);
	redirect("_delete_service.php");
	exit;
}

$serviceMetadata = getAllServiceMetadata($conn);
$allServices = getAllServices($conn);
$serviceColumnDropDowns = array();

foreach($serviceMetadata as $serviceName => $serviceData) {
	$dropDown = new HTMLDropDown("column");
	
	foreach($serviceData['Columns'] as $columnName => $columnData) {
		$dropDown->option($columnName, $columnName);
	}
	
	$serviceColumnDropDowns[$serviceName] = $dropDown;
}

$serviceDropDown = new HTMLDropDown("service");
foreach($allServices as $service) {
	$serviceDropDown->option($service['Name'], $service['Name']);
}

$conn->close();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<style type="text/css">
h3 {
	margin: 0;
	padding: 0;
	text-decoration: underline;
}
h4 {
	margin: 0;
	padding: 0;
	font-weight: normal;
	font-style: italic;
}
div {
	margin-left: 20px;
	margin-top: 0;
	maring-bottom: 0;
}

p {
	padding: 0;
	margin: 0;
}
</style>
</head>
<body>
<?php

echo "<h3>Delete a Service</h3>";
echo "<p>Warning: All columns for this service and any business data for this service will be deleted.</p>";
echo "<form action='_delete_service.php' method='POST'>";
echo $serviceDropDown->html();
echo "<br><br>";
echo "<input type='submit' value='Delete'>";
echo "</form>";
echo "<br><hr>";

echo "<h3>Delete a Service Field</h3>";
echo "<p>Warning: This field and its data will be permanently deleted.</p><br>";

foreach($serviceMetadata as $serviceName => $serviceData) {
	echo "<form action='_delete_service.php' method='POST'>";
	echo "<h4>{$serviceName}</h4>";
	echo $serviceColumnDropDowns[$serviceName]->html();
	echo "<input type='hidden' name='service' value='{$serviceName}'>";
	echo "<br><br>";
	echo "<input type='submit' value='Delete'>";
	echo "</form>";
	echo "<hr style='border:none;border-bottom:1px dotted black;'><br>";
}
?>
<script type="text/javascript">
var forms = document.getElementsByTagName("form");
for(var i = 0; i < forms.length; i++) {
	forms[i].onsubmit = function() { return confirm("Are you sure you want to delete this?"); };
}
</script>
</body>
</html>